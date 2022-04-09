<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Collection;

use ArrayAccess;
use Countable;
use Iterator;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use OutOfBoundsException;

class TicketUpdateCollection implements ArrayAccess, Countable, Iterator
{
    use CollectionTrait;

    public function __construct(TicketUpdate ...$ticketUpdates)
    {
        foreach ($ticketUpdates as $ticketUpdate) {
            $this->push($ticketUpdate);
        }
    }

    public function push(TicketUpdate $ticketUpdate) : void
    {
        $this->collection[$ticketUpdate->getId()->toString()] = $ticketUpdate;
    }

    public function current() : TicketUpdate
    {
        return current($this->collection);
    }

    public function offsetGet($ticketUpdateId) : TicketUpdate
    {
        if (!$this->offsetExists($ticketUpdateId)) {
            throw new OutOfBoundsException('No such status: ' . $ticketUpdateId);
        }
        return $this->collection[$ticketUpdateId];
    }
}
