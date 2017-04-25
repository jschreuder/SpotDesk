<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\EmailAddressValue;
use Ramsey\Uuid\UuidInterface;

class Ticket
{
    /** @var  UuidInterface */
    private $id;

    /** @var  string */
    private $secretKey;

    /** @var  EmailAddressValue */
    private $email;

    /** @var  string */
    private $subject;

    /** @var  string */
    private $message;

    /** @var  \DateTimeInterface */
    private $createdAt;

    /** @var  int */
    private $updates;

    /** @var  \DateTimeInterface */
    private $lastUpdate;

    /** @var  Status */
    private $status;

    /** @var  ?Department */
    private $department;

    public function __construct(
        UuidInterface $id,
        string $secretKey,
        EmailAddressValue $email,
        string $subject,
        string $message,
        \DateTimeInterface $createdAt,
        int $updates,
        \DateTimeInterface $lastUpdate,
        Status $status,
        ?Department $department
    ) {
        $this->id = $id;
        $this->secretKey = $secretKey;
        $this->email = $email;
        $this->subject = $subject;
        $this->message = $message;
        $this->createdAt = $createdAt;
        $this->updates = $updates;
        $this->lastUpdate = $lastUpdate;
        $this->status = $status;
        $this->department = $department;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getEmail(): EmailAddressValue
    {
        return $this->email;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdates(): int
    {
        return $this->updates;
    }

    public function setUpdates(int $updates): void
    {
        $this->updates = $updates;
    }

    public function getLastUpdate(): \DateTimeInterface
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(\DateTimeInterface $lastUpdate): void
    {
        $this->lastUpdate = $lastUpdate;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): void
    {
        $this->department = $department;
    }
}
