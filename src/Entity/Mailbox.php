<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\MailTransportSecurityValue;
use Ramsey\Uuid\UuidInterface;
use DateTimeInterface;

class Mailbox
{
    public function __construct(
        private UuidInterface $id,
        private string $name,
        private ?Department $department,
        private string $imapServer,
        private int $imapPort,
        private MailTransportSecurityValue $imapSecurity,
        private string $imapUser,
        private string $imapPass,
        private DateTimeInterface $lastCheck
    ) {
    }

    public function getId() : UuidInterface
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getDepartment() : ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department) : void
    {
        $this->department = $department;
    }

    public function getImapServer() : string
    {
        return $this->imapServer;
    }

    public function setImapServer(string $imapServer) : void
    {
        $this->imapServer = $imapServer;
    }

    public function getImapPort() : int
    {
        return $this->imapPort;
    }

    public function setImapPort(int $imapPort) : void
    {
        $this->imapPort = $imapPort;
    }

    public function getImapSecurity() : MailTransportSecurityValue
    {
        return $this->imapSecurity;
    }

    public function setImapSecurity(MailTransportSecurityValue $imapSecurity) : void
    {
        $this->imapSecurity = $imapSecurity;
    }

    public function getImapUser() : string
    {
        return $this->imapUser;
    }

    public function setImapUser(string $imapUser) : void
    {
        $this->imapUser = $imapUser;
    }

    public function getImapPass() : string
    {
        return $this->imapPass;
    }

    public function setImapPass(string $imapPass) : void
    {
        $this->imapPass = $imapPass;
    }

    public function getLastCheck() : DateTimeInterface
    {
        return $this->lastCheck;
    }

    public function setLastCheck(DateTimeInterface $lastCheck) : void
    {
        $this->lastCheck = $lastCheck;
    }
}
