<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Entity\Department;

class DepartmentCollection implements \ArrayAccess, \Countable, \Iterator
{
    use CollectionTrait;

    public function __construct(Department ...$departments)
    {
        foreach ($departments as $department) {
            $this->push($department);
        }
    }

    public function push(Department $department): void
    {
        $this->collection[$department->getId()->toString()] = $department;
    }

    public function current(): Department
    {
        return current($this->collection);
    }

    public function offsetGet($departmentId): Department
    {
        if (!$this->offsetExists($departmentId)) {
            throw new \OutOfBoundsException('No such department: ' . $departmentId);
        }
        return $this->collection[$departmentId];
    }

    public function getChildrenOf(?Department $department)
    {
        return array_filter($this->collection, function (Department $current) use ($department) {
            return $current->getParent() === $department;
        });
    }
}
