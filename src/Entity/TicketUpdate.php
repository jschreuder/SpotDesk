<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\EmailAddressValue;
use Ramsey\Uuid\UuidInterface;

class TicketUpdate
{
    /** @var  UuidInterface */
    private $id;

    /** @var  Ticket */
    private $ticket;

    /** @var  EmailAddressValue */
    private $email;

    /** @var  string */
    private $message;

    /** @var  \DateTimeInterface */
    private $createdAt;

    /** @var  bool */
    private $internal;

    public function __construct(
        UuidInterface $id,
        Ticket $ticket,
        EmailAddressValue $email,
        string $message,
        \DateTimeInterface $createdAt,
        bool $internal
    )
    {
        $this->id = $id;
        $this->ticket = $ticket;
        $this->email = $email;
        $this->message = $message;
        $this->createdAt = $createdAt;
        $this->internal = $internal;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    public function getEmail(): EmailAddressValue
    {
        return $this->email;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isInternal(): bool
    {
        return $this->internal;
    }
}
