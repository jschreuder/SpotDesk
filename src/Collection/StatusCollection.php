<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Entity\Status;

class StatusCollection implements \ArrayAccess, \Countable, \Iterator
{
    use CollectionTrait;

    public function __construct(Status ...$statuses)
    {
        foreach ($statuses as $status) {
            $this->push($status);
        }
    }

    public function push(Status $status): void
    {
        $this->collection[$status->getName()] = $status;
    }

    public function current(): Status
    {
        return current($this->collection);
    }

    public function offsetGet($status): Status
    {
        if (!$this->offsetExists($status)) {
            throw new \OutOfBoundsException('No such status: ' . $status);
        }
        return $this->collection[$status];
    }
}
