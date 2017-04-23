<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\MailTransportSecurityValue;
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
    private $imapServer;

    /** @var  int */
    private $imapPort;

    /** @var  MailTransportSecurityValue */
    private $imapSecurity;

    /** @var  string */
    private $imapUser;

    /** @var  string */
    private $imapPass;

    /** @var  \DateTimeInterface */
    private $lastCheck;

    public function __construct(
        UuidInterface $id,
        string $name,
        ?Department $department,
        string $imapServer,
        int $imapPort,
        MailTransportSecurityValue $imapSecurity,
        string $imapUser,
        string $imapPass,
        \DateTimeInterface $lastCheck
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->department = $department;
        $this->imapServer = $imapServer;
        $this->imapPort = $imapPort;
        $this->imapSecurity = $imapSecurity;
        $this->imapUser = $imapUser;
        $this->imapPass = $imapPass;
        $this->lastCheck = $lastCheck;
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

    public function getImapServer(): string
    {
        return $this->imapServer;
    }

    public function setImapServer(string $imapServer): void
    {
        $this->imapServer = $imapServer;
    }

    public function getImapPort(): int
    {
        return $this->imapPort;
    }

    public function setImapPort(int $imapPort): void
    {
        $this->imapPort = $imapPort;
    }

    public function getImapSecurity(): MailTransportSecurityValue
    {
        return $this->imapSecurity;
    }

    public function setImapSecurity(MailTransportSecurityValue $imapSecurity): void
    {
        $this->imapSecurity = $imapSecurity;
    }

    public function getImapUser(): string
    {
        return $this->imapUser;
    }

    public function setImapUser(string $imapUser): void
    {
        $this->imapUser = $imapUser;
    }

    public function getImapPass(): string
    {
        return $this->imapPass;
    }

    public function setImapPass(string $imapPass): void
    {
        $this->imapPass = $imapPass;
    }

    public function getLastCheck(): \DateTimeInterface
    {
        return $this->lastCheck;
    }

    public function setLastCheck(\DateTimeInterface $lastCheck): void
    {
        $this->lastCheck = $lastCheck;
    }
}
