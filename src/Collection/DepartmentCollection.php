<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Collection;

use ArrayAccess;
use Countable;
use Iterator;
use jschreuder\SpotDesk\Entity\Department;
use OutOfBoundsException;

class DepartmentCollection implements ArrayAccess, Countable, Iterator
{
    use CollectionTrait;

    public function __construct(Department ...$departments)
    {
        foreach ($departments as $department) {
            $this->push($department);
        }
    }

    public function push(Department $department) : void
    {
        $this->collection[$department->getId()->toString()] = $department;
    }

    public function current() : Department
    {
        return current($this->collection);
    }

    public function offsetGet($departmentId) : Department
    {
        if (!isset($this[strval($departmentId)])) {
            throw new OutOfBoundsException('No such department: ' . $departmentId);
        }
        return $this->collection[strval($departmentId)];
    }
}
