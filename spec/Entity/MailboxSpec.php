<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\Mailbox;
use jschreuder\SpotDesk\Value\MailTransportSecurityValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\UuidInterface;

class MailboxSpec extends ObjectBehavior
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

    public function let(UuidInterface $id, Department $department)
    {
        $this->beConstructedWith(
            $this->id = $id,
            $this->name = 'name',
            $this->department = $department,
            $this->imapServer = 'server.io',
            $this->imapPort = 587,
            $this->imapSecurity = MailTransportSecurityValue::get(MailTransportSecurityValue::SECURITY_SSL),
            $this->imapUser = 'user',
            $this->imapPass = 'pass',
            $this->lastCheck = new \DateTimeImmutable('-1 month')
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Mailbox::class);
    }

    public function it_can_access_its_properties()
    {
        $this->getId()->shouldReturn($this->id);
        $this->getName()->shouldReturn($this->name);
        $this->getDepartment()->shouldReturn($this->department);
        $this->getImapServer()->shouldReturn($this->imapServer);
        $this->getImapPort()->shouldReturn($this->imapPort);
        $this->getImapSecurity()->shouldReturn($this->imapSecurity);
        $this->getImapUser()->shouldReturn($this->imapUser);
        $this->getImapPass()->shouldReturn($this->imapPass);
        $this->getLastCheck()->shouldReturn($this->lastCheck);
    }

    public function it_can_instantiate_without_department()
    {
        $this->beConstructedWith(
            $this->id,
            $this->name,
            null,
            $this->imapServer,
            $this->imapPort,
            $this->imapSecurity,
            $this->imapUser,
            $this->imapPass,
            $this->lastCheck
        );
        $this->getDepartment()->shouldReturn(null);
    }

    public function it_can_change_some_properties(Department $department)
    {
        $name = 'new';
        $this->setName($name);
        $this->getName()->shouldReturn($name);

        $this->setDepartment($department);
        $this->getDepartment()->shouldReturn($department);

        $server = 'server.com';
        $this->setImapServer($server);
        $this->getImapServer()->shouldReturn($server);

        $port = 25;
        $this->setImapPort($port);
        $this->getImapPort()->shouldReturn($port);

        $security = MailTransportSecurityValue::get(MailTransportSecurityValue::SECURITY_TLS);
        $this->setImapSecurity($security);
        $this->getImapSecurity()->shouldReturn($security);

        $user = 'newuser';
        $this->setImapUser($user);
        $this->getImapUser()->shouldReturn($user);

        $pass = 'newpass';
        $this->setImapPass($pass);
        $this->getImapPass()->shouldReturn($pass);

        $lastCheck = new \DateTimeImmutable();
        $this->setLastCheck($lastCheck);
        $this->getLastCheck()->shouldReturn($lastCheck);
    }
}
