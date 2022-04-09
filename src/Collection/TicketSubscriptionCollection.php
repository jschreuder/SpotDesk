<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Collection;

use ArrayAccess;
use Countable;
use Iterator;
use jschreuder\SpotDesk\Entity\TicketSubscription;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use OutOfBoundsException;

class TicketSubscriptionCollection implements ArrayAccess, Countable, Iterator
{
    use CollectionTrait;

    public function __construct(TicketSubscription ...$ticketSubscriptions)
    {
        foreach ($ticketSubscriptions as $ticketSubscription) {
            $this->push($ticketSubscription);
        }
    }

    public function push(TicketSubscription $ticketSubscription) : void
    {
        $this->collection[$ticketSubscription->getId()->toString()] = $ticketSubscription;
    }

    public function current() : TicketSubscription
    {
        return current($this->collection);
    }

    public function offsetGet($ticketSubscriptionId) : TicketSubscription
    {
        if (!$this->offsetExists($ticketSubscriptionId)) {
            throw new OutOfBoundsException('No such status: ' . $ticketSubscriptionId);
        }
        return $this->collection[$ticketSubscriptionId];
    }

    public function getByEmailAddress(EmailAddressValue $email) : TicketSubscription
    {
        /** @var  TicketSubscription[] $subscriptions */
        $subscriptions = $this->toArray();
        foreach ($subscriptions as $subscription) {
            if ($subscription->getEmail()->isEqual($email)) {
                return $subscription;
            }
        }
        throw new OutOfBoundsException('E-mail address not in collection: ' . $email->toString());
    }
}
