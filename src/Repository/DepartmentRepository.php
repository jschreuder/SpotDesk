<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\DepartmentCollection;
use jschreuder\SpotDesk\Entity\Department;
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

    private function arrayToDepartment(array $row, ?Department $parent): Department
    {
        return new Department(
            Uuid::fromBytes($row['department_id']),
            $row['name'],
            $parent
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
        }
        return $this->_departments;
    }
}
