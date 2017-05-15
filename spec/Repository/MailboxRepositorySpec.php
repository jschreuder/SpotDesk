<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\MailboxCollection;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\Mailbox;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use jschreuder\SpotDesk\Value\MailTransportSecurityValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class MailboxRepositorySpec extends ObjectBehavior
{
    /** @var  \PDO */
    private $db;

    /** @var  DepartmentRepository */
    private $departmentRepository;

    public function let(\PDO $db, DepartmentRepository $departmentRepository) : void
    {
        $this->beConstructedWith(
            $this->db = $db,
            $this->departmentRepository = $departmentRepository
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(MailboxRepository::class);
    }

    public function it_can_create_a_mailbox(\PDOStatement $statement, Department $department) : void
    {
        $department->getId()->willReturn($departmentId = Uuid::uuid4());
        $this->db->prepare(new Argument\Token\StringContainsToken('INSERT'))->willReturn($statement);
        $statement->execute(new Argument\Token\TypeToken('array'));

        $mailbox = $this->createMailbox(
            $name = 'Mailbox',
            $department,
            $imapServer = 'mail.box',
            $imapPort = 587,
            $security = MailTransportSecurityValue::get(MailTransportSecurityValue::SECURITY_TLS),
            $imapUser = 'user',
            $imapPass = 'pass',
            $lastCheck = new \DateTimeImmutable()
        );
        $mailbox->getId()->shouldHaveType(UuidInterface::class);
        $mailbox->getName()->shouldBe($name);
        $mailbox->getDepartment()->shouldBe($department);
        $mailbox->getImapServer()->shouldBe($imapServer);
        $mailbox->getImapPort()->shouldBe($imapPort);
        $mailbox->getImapSecurity()->shouldBe($security);
        $mailbox->getImapUser()->shouldBe($imapUser);
        $mailbox->getImapPass()->shouldBe($imapPass);
        $mailbox->getLastCheck()->shouldBe($lastCheck);
    }

    public function it_can_get_a_mailbox(\PDOStatement $statement, Department $department) : void
    {
        $mailboxId = Uuid::uuid4();
        $departmentId = Uuid::uuid4();
        $this->departmentRepository->getDepartment(new Argument\Token\TypeToken(UuidInterface::class))
            ->willReturn($department);

        $name = 'Mailbox';
        $imapServer = 'mail.box';
        $imapPort = 587;
        $security = MailTransportSecurityValue::get(MailTransportSecurityValue::SECURITY_TLS);
        $imapUser = 'user';
        $imapPass = 'pass';
        $lastCheck = new \DateTimeImmutable();

        $this->db->prepare(new Argument\Token\StringContainsToken('SELECT'))->willReturn($statement);
        $statement->execute(['mailbox_id' => $mailboxId->getBytes()])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn(
            [
                'mailbox_id' => $mailboxId->getBytes(),
                'name' => $name,
                'department_id' => $departmentId->getBytes(),
                'imap_server' => $imapServer,
                'imap_port' => $imapPort,
                'imap_security' => $security->toString(),
                'imap_user' => $imapUser,
                'imap_pass' => $imapPass,
                'last_check' => $lastCheck->format('Y-m-d H:i:s'),
            ],
            null
        );

        $mailbox = $this->getMailbox($mailboxId);
        $mailbox->getId()->equals($mailboxId)->shouldBe(true);
        $mailbox->getName()->shouldBe($name);
        $mailbox->getDepartment()->shouldBe($department);
        $mailbox->getImapServer()->shouldBe($imapServer);
        $mailbox->getImapPort()->shouldBe($imapPort);
        $mailbox->getImapSecurity()->shouldBeLike($security);
        $mailbox->getImapUser()->shouldBe($imapUser);
        $mailbox->getImapPass()->shouldBe($imapPass);
        $mailbox->getLastCheck()->format('Y-m-d H:i:s')->shouldBe($lastCheck->format('Y-m-d H:i:s'));
    }

    public function it_will_error_when_getting_mailbox_failed(\PDOStatement $statement) : void
    {
        $mailboxId = Uuid::uuid4();

        $this->db->prepare(new Argument\Token\StringContainsToken('SELECT'))->willReturn($statement);
        $statement->execute(['mailbox_id' => $mailboxId->getBytes()])->shouldBeCalled();
        $statement->rowCount()->willReturn(0);

        $this->shouldThrow(\OutOfBoundsException::class)->duringGetMailbox($mailboxId);
    }

    public function it_can_get_all_mailboxes(\PDOStatement $statement, Department $department) : void
    {
        $mailboxId1 = Uuid::uuid4();
        $mailboxId2 = Uuid::uuid4();
        $departmentId = Uuid::uuid4();
        $this->departmentRepository->getDepartment(new Argument\Token\TypeToken(UuidInterface::class))
            ->willReturn($department);

        $this->db->query(new Argument\Token\StringContainsToken('SELECT'))->willReturn($statement);
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn(
            [
                'mailbox_id' => $mailboxId1->getBytes(),
                'name' => 'One',
                'department_id' => $departmentId->getBytes(),
                'imap_server' => 'one.dev',
                'imap_port' => 1,
                'imap_security' => MailTransportSecurityValue::SECURITY_SSL,
                'imap_user' => 'uone',
                'imap_pass' => 'pone',
                'last_check' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
            [
                'mailbox_id' => $mailboxId2->getBytes(),
                'name' => 'Two',
                'department_id' => null,
                'imap_server' => 'two.dev',
                'imap_port' => 2,
                'imap_security' => MailTransportSecurityValue::SECURITY_NONE,
                'imap_user' => 'utwo',
                'imap_pass' => 'ptwo',
                'last_check' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
            null
        );

        $mailboxes = $this->getMailboxes();
        $mailboxes->shouldHaveType(MailboxCollection::class);
        $mailboxes->count()->shouldBe(2);
    }

    public function it_can_get_all_mailboxes_for_department(\PDOStatement $statement, Department $department) : void
    {
        $mailboxId1 = Uuid::uuid4();
        $mailboxId2 = Uuid::uuid4();
        $departmentId = Uuid::uuid4();
        $department->getId()->willReturn($departmentId);
        $this->departmentRepository->getDepartment(new Argument\Token\TypeToken(UuidInterface::class))
            ->willReturn($department);

        $this->db->prepare(new Argument\Token\StringContainsToken('SELECT'))->willReturn($statement);
        $statement->execute(['department_id' => $departmentId->getBytes()])->shouldBeCalled();
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn(
            [
                'mailbox_id' => $mailboxId1->getBytes(),
                'name' => 'One',
                'department_id' => $departmentId->getBytes(),
                'imap_server' => 'one.dev',
                'imap_port' => 1,
                'imap_security' => MailTransportSecurityValue::SECURITY_SSL,
                'imap_user' => 'uone',
                'imap_pass' => 'pone',
                'last_check' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
            [
                'mailbox_id' => $mailboxId2->getBytes(),
                'name' => 'Two',
                'department_id' => $departmentId->getBytes(),
                'imap_server' => 'two.dev',
                'imap_port' => 2,
                'imap_security' => MailTransportSecurityValue::SECURITY_NONE,
                'imap_user' => 'utwo',
                'imap_pass' => 'ptwo',
                'last_check' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
            null
        );

        $mailboxes = $this->getMailboxesForDepartment($department);
        $mailboxes->shouldHaveType(MailboxCollection::class);
        $mailboxes->count()->shouldBe(2);
    }

    public function it_can_update_a_mailbox(\PDOStatement $statement, Mailbox $mailbox) : void
    {
        $mailbox->getId()->willReturn($mailboxId = Uuid::uuid4());
        $mailbox->getName()->willReturn($name = 'Mailbox');
        $mailbox->getDepartment()->willReturn($department = null);
        $mailbox->getImapServer()->willReturn($imapServer = 'mail.box');
        $mailbox->getImapPort()->willReturn($imapPort = 25);
        $mailbox->getImapSecurity()
            ->willReturn($security = MailTransportSecurityValue::get(MailTransportSecurityValue::SECURITY_TLS));
        $mailbox->getImapUser()->willReturn($imapUser = 'user');
        $mailbox->getImapPass()->willReturn($imapPass = 'pass');

        $this->db->prepare(new Argument\Token\StringContainsToken('UPDATE'))->willReturn($statement);
        $statement->execute([
            'mailbox_id' => $mailboxId->getBytes(),
            'name' => $name,
            'department_id' => $department,
            'imap_server' => $imapServer,
            'imap_port' => $imapPort,
            'imap_security' => $security->toString(),
            'imap_user' => $imapUser,
            'imap_pass' => $imapPass,
        ])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);

        $this->updateMailbox($mailbox);
    }

    public function it_will_error_when_updating_mailbox_failed(
        \PDOStatement $statement,
        Mailbox $mailbox,
        Department $department
    ) : void
    {
        $mailbox->getId()->willReturn($mailboxId = Uuid::uuid4());
        $mailbox->getName()->willReturn($name = 'Mailbox');
        $mailbox->getDepartment()->willReturn($department);
        $mailbox->getImapServer()->willReturn($imapServer = 'mail.box');
        $mailbox->getImapPort()->willReturn($imapPort = 25);
        $mailbox->getImapSecurity()
            ->willReturn($security = MailTransportSecurityValue::get(MailTransportSecurityValue::SECURITY_TLS));
        $mailbox->getImapUser()->willReturn($imapUser = 'user');
        $mailbox->getImapPass()->willReturn($imapPass = 'pass');

        $department->getId()->willReturn($departmentId = Uuid::uuid4());

        $this->db->prepare(new Argument\Token\StringContainsToken('UPDATE'))->willReturn($statement);
        $statement->execute([
            'mailbox_id' => $mailboxId->getBytes(),
            'name' => $name,
            'department_id' => $departmentId->getBytes(),
            'imap_server' => $imapServer,
            'imap_port' => $imapPort,
            'imap_security' => $security->toString(),
            'imap_user' => $imapUser,
            'imap_pass' => $imapPass,
        ])->shouldBeCalled();
        $statement->rowCount()->willReturn(0);

        $this->shouldThrow(\RuntimeException::class)->duringUpdateMailbox($mailbox);
    }

    public function it_can_update_last_check_datetime_of_mailbox(\PDOStatement $statement, Mailbox $mailbox) : void
    {
        $mailbox->getId()->willReturn($mailboxId = Uuid::uuid4());
        $checkTime = new \DateTimeImmutable();

        $this->db->prepare(new Argument\Token\StringContainsToken('UPDATE'))->willReturn($statement);
        $statement->execute([
            'last_check' => $checkTime->format('Y-m-d H:i:s'),
            'mailbox_id' => $mailboxId->getBytes(),
        ])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);

        $mailbox->setLastCheck($checkTime)->shouldBeCalled();

        $this->updateLastCheck($mailbox, $checkTime);
    }

    public function it_will_error_when_updating_last_check_datetime_of_mailbox_failed(
        \PDOStatement $statement,
        Mailbox $mailbox
    ) : void
    {
        $mailbox->getId()->willReturn($mailboxId = Uuid::uuid4());

        $this->db->prepare(new Argument\Token\StringContainsToken('UPDATE'))->willReturn($statement);
        $statement->execute(new Argument\Token\TypeToken('array'))->shouldBeCalled();
        $statement->rowCount()->willReturn(0);

        $this->shouldThrow(\RuntimeException::class)->duringUpdateLastCheck($mailbox);
    }

    public function it_can_delete_a_mailbox(\PDOStatement $statement, Mailbox $mailbox) : void
    {
        $mailbox->getId()->willReturn($mailboxId = Uuid::uuid4());

        $this->db->prepare(new Argument\Token\StringContainsToken('DELETE'))->willReturn($statement);
        $statement->execute(['mailbox_id' => $mailboxId->getBytes()])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);

        $this->deleteMailbox($mailbox);
    }

    public function it_will_error_when_deleteing_mailbox_failed(\PDOStatement $statement, Mailbox $mailbox) : void
    {
        $mailbox->getId()->willReturn($mailboxId = Uuid::uuid4());

        $this->db->prepare(new Argument\Token\StringContainsToken('DELETE'))->willReturn($statement);
        $statement->execute(['mailbox_id' => $mailboxId->getBytes()])->shouldBeCalled();
        $statement->rowCount()->willReturn(0);

        $this->shouldThrow(\RuntimeException::class)->duringDeleteMailbox($mailbox);
    }
}
