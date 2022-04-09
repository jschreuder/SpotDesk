<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use Ramsey\Uuid\UuidInterface;
use DateTimeInterface;

class TicketMailing
{
    public function __construct(
        private UuidInterface $id,
        private Ticket $ticket,
        private ?TicketUpdate $ticketUpdate,
        private string $type,
        private ?DateTimeInterface $sentAt = null
    ) {
        $this->id = $id;
        $this->ticket = $ticket;
        $this->ticketUpdate = $ticketUpdate;
        $this->type = $type;
        $this->sentAt = $sentAt;
    }

    public function getId() : UuidInterface
    {
        return $this->id;
    }

    public function getTicket() : Ticket
    {
        return $this->ticket;
    }

    public function getTicketUpdate() : ?TicketUpdate
    {
        return $this->ticketUpdate;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getSentAt() : ?DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(DateTimeInterface $sentAt) : void
    {
        $this->sentAt = $sentAt;
    }
}
