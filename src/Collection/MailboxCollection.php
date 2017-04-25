<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Entity\Mailbox;

class MailboxCollection implements \ArrayAccess, \Countable, \Iterator
{
    use CollectionTrait;

    public function __construct(Mailbox ...$mailboxes)
    {
        foreach ($mailboxes as $mailbox) {
            $this->push($mailbox);
        }
    }

    public function push(Mailbox $mailbox) : void
    {
        $this->collection[$mailbox->getId()->toString()] = $mailbox;
    }

    public function current() : Mailbox
    {
        return current($this->collection);
    }

    public function offsetGet($mailboxId) : Mailbox
    {
        if (!$this->offsetExists($mailboxId)) {
            throw new \OutOfBoundsException('No such status: ' . $mailboxId);
        }
        return $this->collection[$mailboxId];
    }
}
