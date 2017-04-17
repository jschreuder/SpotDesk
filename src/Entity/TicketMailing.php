<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Entity;

use Ramsey\Uuid\UuidInterface;

class TicketMailing
{
    /** @var  UuidInterface */
    private $id;

    /** @var  Ticket */
    private $ticket;

    /** @var  string */
    private $type;

    /** @var  ?\DateTimeInterface */
    private $sentAt;

    public function __construct(UuidInterface $id, Ticket $ticket, string $type, ?\DateTimeInterface $sentAt = null)
    {
        $this->id = $id;
        $this->ticket = $ticket;
        $this->type = $type;
        $this->sentAt = $sentAt;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeInterface $sentAt): void
    {
        $this->sentAt = $sentAt;
    }
}
