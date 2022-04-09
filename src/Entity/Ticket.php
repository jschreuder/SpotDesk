<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\EmailAddressValue;
use Ramsey\Uuid\UuidInterface;
use DateTimeInterface;

class Ticket
{
    public function __construct(
        private UuidInterface $id,
        private string $secretKey,
        private EmailAddressValue $email,
        private string $subject,
        private string $message,
        private DateTimeInterface $createdAt,
        private int $updates,
        private DateTimeInterface $lastUpdate,
        private Status $status,
        private ?Department $department
    )
    {
    }

    public function getId() : UuidInterface
    {
        return $this->id;
    }

    public function getSecretKey() : string
    {
        return $this->secretKey;
    }

    public function getEmail() : EmailAddressValue
    {
        return $this->email;
    }

    public function getSubject() : string
    {
        return $this->subject;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    public function getCreatedAt() : DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdates() : int
    {
        return $this->updates;
    }

    public function setUpdates(int $updates) : void
    {
        $this->updates = $updates;
    }

    public function getLastUpdate() : DateTimeInterface
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(DateTimeInterface $lastUpdate) : void
    {
        $this->lastUpdate = $lastUpdate;
    }

    public function getStatus() : Status
    {
        return $this->status;
    }

    public function setStatus(Status $status) : void
    {
        $this->status = $status;
    }

    public function getDepartment() : ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department) : void
    {
        $this->department = $department;
    }
}
