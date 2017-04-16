<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Entity\Ticket;

class TicketCollection implements \ArrayAccess, \Countable, \Iterator
{
    use CollectionTrait;

    /** @var  Ticket[] */
    private $collection = [];

    public function __construct(Ticket ...$tickets)
    {
        foreach ($tickets as $ticket) {
            $this->push($ticket);
        }
    }

    public function push(Ticket $ticket): void
    {
        $this->collection[$ticket->getId()->toString()] = $ticket;
    }

    public function current(): Ticket
    {
        return current($this->collection);
    }

    public function offsetGet($ticketId): Ticket
    {
        if (!$this->offsetExists($ticketId)) {
            throw new \OutOfBoundsException('No ticket with ID: ' . $ticketId);
        }
        return $this->collection[$ticketId];
    }
}
