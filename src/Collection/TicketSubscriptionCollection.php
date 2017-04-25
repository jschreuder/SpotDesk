<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Entity\TicketSubscription;

class TicketSubscriptionCollection implements \ArrayAccess, \Countable, \Iterator
{
    use CollectionTrait;

    public function __construct(TicketSubscription ...$ticketSubscriptions)
    {
        foreach ($ticketSubscriptions as $ticketSubscription) {
            $this->push($ticketSubscription);
        }
    }

    public function push(TicketSubscription $ticketSubscription): void
    {
        $this->collection[$ticketSubscription->getId()->toString()] = $ticketSubscription;
    }

    public function current(): TicketSubscription
    {
        return current($this->collection);
    }

    public function offsetGet($ticketSubscriptionId): TicketSubscription
    {
        if (!$this->offsetExists($ticketSubscriptionId)) {
            throw new \OutOfBoundsException('No such status: ' . $ticketSubscriptionId);
        }
        return $this->collection[$ticketSubscriptionId];
    }
}
