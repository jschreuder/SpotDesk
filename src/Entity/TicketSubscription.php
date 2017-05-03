<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\EmailAddressValue;
use Ramsey\Uuid\UuidInterface;

class TicketSubscription
{
    /** @var  UuidInterface */
    private $id;

    /** @var  Ticket */
    private $ticket;

    /** @var  EmailAddressValue */
    private $email;

    /** @var  bool */
    private $internal;

    public function __construct(UuidInterface $id, Ticket $ticket, EmailAddressValue $email, bool $internal)
    {
        $this->id = $id;
        $this->ticket = $ticket;
        $this->email = $email;
        $this->internal = $internal;
    }

    public function getId() : UuidInterface
    {
        return $this->id;
    }

    public function getTicket() : Ticket
    {
        return $this->ticket;
    }

    public function getEmail() : EmailAddressValue
    {
        return $this->email;
    }

    public function getInternal() : bool
    {
        return $this->internal;
    }
}
