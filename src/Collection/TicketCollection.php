<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Collection;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Exception\SpotDeskException;
use OutOfBoundsException;

class TicketCollection implements ArrayAccess, Countable, Iterator
{
    use CollectionTrait;

    private int $totalCount;

    public function __construct(Ticket ...$tickets)
    {
        foreach ($tickets as $ticket) {
            $this->push($ticket);
        }
    }

    public function push(Ticket $ticket) : void
    {
        $this->collection[$ticket->getId()->toString()] = $ticket;
    }

    public function current() : Ticket
    {
        return current($this->collection);
    }

    public function offsetGet($ticketId) : Ticket
    {
        if (!$this->offsetExists($ticketId)) {
            throw new OutOfBoundsException('No ticket with ID: ' . $ticketId);
        }
        return $this->collection[$ticketId];
    }

    public function getTotalCount() : int
    {
        if (!isset($this->totalCount)) {
            throw new SpotDeskException('Total count must be set before it is retrieved.');
        }
        return $this->totalCount;
    }

    public function setTotalCount(int $totalCount)
    {
        if ($totalCount < 0) {
            throw new InvalidArgumentException('Total count of tickets cannot be smaller then 0.');
        }
        $this->totalCount = $totalCount;
    }
}
