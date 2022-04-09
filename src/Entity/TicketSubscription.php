<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\EmailAddressValue;
use Ramsey\Uuid\UuidInterface;

class TicketSubscription
{
    public function __construct(
        private UuidInterface $id, 
        private Ticket $ticket, 
        private EmailAddressValue $email, 
        private bool $internal, 
        private bool $sendNotifications
    )
    {
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
    
    public function setInternal(bool $internal) : void
    {
        $this->internal = $internal;
    }

    public function isInternal() : bool
    {
        return $this->internal;
    }
    
    public function setSendNotifications(bool $sendNotifications) : void
    {
        $this->sendNotifications = $sendNotifications;
    }

    public function sendNotifications() : bool
    {
        return $this->sendNotifications;
    }
}
