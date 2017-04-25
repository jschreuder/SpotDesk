<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Entity\TicketMailing;

class TicketMailingCollection implements \ArrayAccess, \Countable, \Iterator
{
    use CollectionTrait;

    public function __construct(TicketMailing ...$ticketMailings)
    {
        foreach ($ticketMailings as $ticketMailing) {
            $this->push($ticketMailing);
        }
    }

    public function push(TicketMailing $ticketMailing): void
    {
        $this->collection[$ticketMailing->getId()->toString()] = $ticketMailing;
    }

    public function current(): TicketMailing
    {
        return current($this->collection);
    }

    public function offsetGet($ticketMailingId): TicketMailing
    {
        if (!$this->offsetExists($ticketMailingId)) {
            throw new \OutOfBoundsException('No ticket with ID: ' . $ticketMailingId);
        }
        return $this->collection[$ticketMailingId];
    }
}
