<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use Ramsey\Uuid\UuidInterface;

class Mailbox
{
    /** @var  UuidInterface */
    private $id;

    /** @var  string */
    private $name;

    /** @var  ?Department */
    private $department;

    /** @var  string */
    private $smtpServer;

    /** @var  int */
    private $smtpPort;

    /** @var  string */
    private $smtpSecurity;

    /** @var  string */
    private $smtpUser;

    /** @var  string */
    private $smtpPass;

    public function __construct(
        UuidInterface $id,
        string $name,
        ?Department $department,
        string $smtpServer,
        int $smtpPort,
        string $smtpSecurity,
        string $smtpUser,
        string $smtpPass
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->department = $department;
        $this->smtpServer = $smtpServer;
        $this->smtpPort = $smtpPort;
        $this->smtpSecurity = $smtpSecurity;
        $this->smtpUser = $smtpUser;
        $this->smtpPass = $smtpPass;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): void
    {
        $this->department = $department;
    }

    public function getSmtpServer(): string
    {
        return $this->smtpServer;
    }

    public function setSmtpServer(string $smtpServer): void
    {
        $this->smtpServer = $smtpServer;
    }

    public function getSmtpPort(): int
    {
        return $this->smtpPort;
    }

    public function setSmtpPort(int $smtpPort): void
    {
        $this->smtpPort = $smtpPort;
    }

    public function getSmtpSecurity(): string
    {
        return $this->smtpSecurity;
    }

    public function setSmtpSecurity(string $smtpSecurity): void
    {
        $this->smtpSecurity = $smtpSecurity;
    }

    public function getSmtpUser(): string
    {
        return $this->smtpUser;
    }

    public function setSmtpUser(string $smtpUser): void
    {
        $this->smtpUser = $smtpUser;
    }

    public function getSmtpPass(): string
    {
        return $this->smtpPass;
    }

    public function setSmtpPass(string $smtpPass): void
    {
        $this->smtpPass = $smtpPass;
    }
}
