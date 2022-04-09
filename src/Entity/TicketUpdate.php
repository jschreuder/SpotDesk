<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\EmailAddressValue;
use Ramsey\Uuid\UuidInterface;
use DateTimeInterface;

class TicketUpdate
{
    public function __construct(
        private UuidInterface $id,
        private Ticket $ticket,
        private EmailAddressValue $email,
        private string $message,
        private DateTimeInterface $createdAt,
        private bool $internal
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

    public function getMessage() : string
    {
        return $this->message;
    }

    public function getCreatedAt() : DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isInternal() : bool
    {
        return $this->internal;
    }
}
