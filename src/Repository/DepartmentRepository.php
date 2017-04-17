<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\DepartmentCollection;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DepartmentRepository
{
    /** @var  \PDO */
    private $db;

    /** @var  DepartmentCollection */
    private $_departments;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function createDepartment(string $name, ?Department $parent, EmailAddressValue $email): Department
    {
        $department = new Department(Uuid::uuid4(), $name, $parent, $email);

        $query = $this->db->prepare("
            INSERT INTO `departments` (`department_id`, `name`, `parent_id`)
            VALUES (:department_id, :name, :parent_id)
        ");
        $query->execute([
            'department_id' => $department->getId()->getBytes(),
            'name' => $department->getName(),
            'parent_id' => is_null($department->getParent()) ? null : $department->getParent()->getId()->getBytes(),
            'email' => $department->getEmail()->toString(),
        ]);

        return $department;
    }

    private function arrayToDepartment(array $row, ?Department $parent): Department
    {
        return new Department(
            Uuid::fromBytes($row['department_id']),
            $row['name'],
            $parent,
            EmailAddressValue::get($row['email'])
        );
    }

    public function getDepartment(UuidInterface $departmentId): Department
    {
        $departments = $this->getDepartments();
        if (!isset($departments[$departmentId->toString()])) {
            throw new \OutOfBoundsException('No department found for ID: ' . $departmentId->toString());
        }

        return $departments[$departmentId->toString()];
    }

    public function getDepartments(): DepartmentCollection
    {
        if (is_null($this->_departments)) {
            $query = $this->db->prepare("SELECT * FROM `departments`");
            $query->execute();

            $departmentRows = [];
            $departmentCollection = new DepartmentCollection();
            while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
                $department = $this->arrayToDepartment($row, null);
                $departmentRows[$department->getId()->toString()] = $row;
                $departmentCollection->push($department);
            }

            foreach ($departmentCollection as $id => $department) {
                if (!is_null($departmentRows[$id]['parent_id'])) {
                    $department->setParent($departmentCollection[$id]);
                }
            }
            $this->_departments = $departmentCollection;
        }
        return $this->_departments;
    }

    public function updateDepartment(Department $department): void
    {
        $query = $this->db->prepare("
            UPDATE `departments`
            SET `name` = :name, `email` = :email
            WHERE `department_id` = :department_id
        ");
        $query->execute([
            'name' => $department->getName(),
            'email' => $department->getEmail()->toString(),
            'department_id' => $department->getId()->getBytes(),
        ]);

        if ($query->rowCount() !== 1) {
            throw new \RuntimeException('Failed to update department: ' . $department->getId()->toString());
        }
    }
}
