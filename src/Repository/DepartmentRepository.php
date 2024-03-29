<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\DepartmentCollection;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Exception\SpotDeskException;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use OutOfBoundsException;
use PDO;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DepartmentRepository
{
    private DepartmentCollection $_departments;

    public function __construct(private PDO $db)
    {
    }

    public function createDepartment(string $name, ?Department $parent, EmailAddressValue $email) : Department
    {
        $department = new Department(Uuid::uuid4(), $name, $parent, $email);

        $query = $this->db->prepare("
            INSERT INTO `departments` (`department_id`, `name`, `parent_id`, `email`)
            VALUES (:department_id, :name, :parent_id, :email)
        ");
        $query->execute([
            'department_id' => $department->getId()->getBytes(),
            'name' => $department->getName(),
            'parent_id' => is_null($department->getParent()) ? null : $department->getParent()->getId()->getBytes(),
            'email' => $department->getEmail()->toString(),
        ]);

        return $department;
    }

    private function arrayToDepartment(array $row, ?Department $parent) : Department
    {
        return new Department(
            Uuid::fromBytes($row['department_id']),
            $row['name'],
            $parent,
            EmailAddressValue::get($row['email'])
        );
    }

    public function getDepartment(UuidInterface $departmentId) : Department
    {
        $departments = $this->getDepartments();
        if (!isset($departments[$departmentId->toString()])) {
            throw new OutOfBoundsException('No department found for ID: ' . $departmentId->toString());
        }

        return $departments[$departmentId->toString()];
    }

    public function getDepartments() : DepartmentCollection
    {
        if (!isset($this->_departments)) {
            $query = $this->db->query("SELECT * FROM `departments`");

            $departmentRows = [];
            $departmentCollection = new DepartmentCollection();
            while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
                $department = $this->arrayToDepartment($row, null);
                $departmentRows[$department->getId()->toString()] = $row;
                $departmentCollection->push($department);
            }

            foreach ($departmentCollection as $id => $department) {
                if (!is_null($departmentRows[$id]['parent_id'])) {
                    $parentId = Uuid::fromBytes($departmentRows[$id]['parent_id'])->toString();
                    $department->setParent($departmentCollection[$parentId]);
                }
            }
            $this->_departments = $departmentCollection;
        }
        return $this->_departments;
    }

    public function getDepartmentsForUser(User $user) : DepartmentCollection
    {
        $query = $this->db->prepare("
            SELECT d.*
            FROM `departments` d
            INNER JOIN `users_departments` ud ON d.`department_id` = ud.`department_id` AND ud.email = :email
        ");
        $query->execute(['email' => $user->getEmail()->toString()]);

        $departmentCollection = new DepartmentCollection();
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $departmentCollection->push($this->arrayToDepartment(
                $row,
                $row['parent_id'] ? $this->getDepartment(Uuid::fromBytes($row['parent_id'])) : null
            ));
        }
        return $departmentCollection;
    }

    public function updateDepartment(Department $department) : void
    {
        $query = $this->db->prepare("
            UPDATE `departments`
            SET `name` = :name, `email` = :email, `parent_id` = :parent_id
            WHERE `department_id` = :department_id
        ");
        $query->execute([
            'name' => $department->getName(),
            'email' => $department->getEmail()->toString(),
            'parent_id' => $department->getParent() ? $department->getParent()->getId()->getBytes() : null,
            'department_id' => $department->getId()->getBytes(),
        ]);

        if ($query->rowCount() !== 1) {
            throw new SpotDeskException('Failed to update department: ' . $department->getId()->toString());
        }
    }

    public function deleteDepartment(Department $department)
    {
        $query = $this->db->prepare("
            DELETE FROM `departments`
            WHERE `department_id` = :department_id
        ");
        $query->execute(['department_id' => $department->getId()->getBytes()]);

        if ($query->rowCount() !== 1) {
            throw new SpotDeskException('Failed to delete department: ' . $department->getId()->toString());
        }
    }
}
