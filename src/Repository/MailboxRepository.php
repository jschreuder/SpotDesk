<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\MailboxCollection;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\Mailbox;
use jschreuder\SpotDesk\Exception\SpotDeskException;
use jschreuder\SpotDesk\Value\MailTransportSecurityValue;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class MailboxRepository
{
    /** @var  \PDO */
    private $db;

    /** @var  DepartmentRepository */
    private $departmentRepository;

    public function __construct(\PDO $db, DepartmentRepository $departmentRepository)
    {
        $this->db = $db;
        $this->departmentRepository = $departmentRepository;
    }

    public function createMailbox(
        string $name,
        ?Department $department,
        string $imapServer,
        int $imapPort,
        MailTransportSecurityValue $imapSecurity,
        string $imapUser,
        string $imapPass,
        ?\DateTimeInterface $lastCheck = null
    ) : Mailbox {
        $mailbox = new Mailbox(
            Uuid::uuid4(),
            $name,
            $department,
            $imapServer,
            $imapPort,
            $imapSecurity,
            $imapUser,
            $imapPass,
            $lastCheck ?? new \DateTimeImmutable()
        );

        $query = $this->db->prepare("
            INSERT INTO `mailboxes` (
                `mailbox_id`, `name`, `department_id`, `imap_server`, `imap_port`, `imap_security`, `imap_user`, 
                `imap_pass`, `last_check`
            ) VALUES (
                :mailbox_id, :name, :department_id, :imap_server, :imap_port, :imap_security, :imap_user, 
                :imap_pass, :last_check
            )
        ");
        $query->execute([
            'mailbox_id' => $mailbox->getId()->getBytes(),
            'name' => $mailbox->getName(),
            'department_id' => is_null($mailbox->getDepartment())
                ? null
                : $mailbox->getDepartment()->getId()->getBytes(),
            'imap_server' => $mailbox->getImapServer(),
            'imap_port' => $mailbox->getImapPort(),
            'imap_security' => $mailbox->getImapSecurity()->toString(),
            'imap_user' => $mailbox->getImapUser(),
            'imap_pass' => $mailbox->getImapPass(),
            'last_check' => $mailbox->getLastCheck()->format('Y-m-d H:i:s'),
        ]);

        return $mailbox;
    }

    private function arrayToMailbox(array $row) : Mailbox
    {
        $department = is_null($row['department_id'])
            ? null
            : $this->departmentRepository->getDepartment(Uuid::fromBytes($row['department_id']));

        return new Mailbox(
            Uuid::fromBytes($row['mailbox_id']),
            $row['name'],
            $department,
            $row['imap_server'],
            intval($row['imap_port']),
            MailTransportSecurityValue::get($row['imap_security']),
            $row['imap_user'],
            $row['imap_pass'],
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['last_check'])
        );
    }

    public function getMailbox(UuidInterface $mailboxId) : Mailbox
    {
        $query = $this->db->prepare("SELECT * FROM `mailboxes` WHERE `mailbox_id` = :mailbox_id");
        $query->execute(['mailbox_id' => $mailboxId->getBytes()]);

        if ($query->rowCount() !== 1) {
            throw new \OutOfBoundsException('No mailbox found for ID: ' . $mailboxId->toString());
        }

        return $this->arrayToMailbox($query->fetch(\PDO::FETCH_ASSOC));
    }

    public function getMailboxes() : MailboxCollection
    {
        $query = $this->db->query("SELECT * FROM `mailboxes`");
        $mailboxCollection = new MailboxCollection();
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $mailboxCollection->push($this->arrayToMailbox($row));
        }
        return $mailboxCollection;
    }

    public function getMailboxesForDepartment(Department $department) : MailboxCollection
    {
        $query = $this->db->prepare("
            SELECT * 
            FROM `mailboxes`
            WHERE `department_id` = :department_id
        ");
        $query->execute(['department_id' => $department->getId()->getBytes()]);

        $mailboxCollection = new MailboxCollection();
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $mailboxCollection->push($this->arrayToMailbox($row));
        }
        return $mailboxCollection;
    }

    public function updateMailbox(Mailbox $mailbox) : void
    {
        $query = $this->db->prepare("
            UPDATE `mailboxes`
            SET `name` = :name, `department_id` = :department_id, `imap_server` = :imap_server, 
                `imap_port` = :imap_port, `imap_security` = :imap_security, `imap_user` = :imap_user, 
                `imap_pass` = :imap_pass
            WHERE `mailbox_id` = :mailbox_id
        ");
        $query->execute([
            'mailbox_id' => $mailbox->getId()->getBytes(),
            'name' => $mailbox->getName(),
            'department_id' => is_null($mailbox->getDepartment())
                ? null
                : $mailbox->getDepartment()->getId()->getBytes(),
            'imap_server' => $mailbox->getImapServer(),
            'imap_port' => $mailbox->getImapPort(),
            'imap_security' => $mailbox->getImapSecurity()->toString(),
            'imap_user' => $mailbox->getImapUser(),
            'imap_pass' => $mailbox->getImapPass(),
        ]);

        if ($query->rowCount() !== 1) {
            throw new SpotDeskException('Failed to update mailbox: ' . $mailbox->getId()->toString());
        }
    }

    public function updateLastCheck(Mailbox $mailbox, ?\DateTimeInterface $checkTime = null) : void
    {
        $checkTime = $checkTime ?? new \DateTimeImmutable('now');
        $query = $this->db->prepare("
            UPDATE `mailboxes`
            SET `last_check` = :last_check
            WHERE `mailbox_id` = :mailbox_id
        ");
        $query->execute([
            'last_check' => $checkTime->format('Y-m-d H:i:s'),
            'mailbox_id' => $mailbox->getId()->getBytes(),
        ]);

        if ($query->rowCount() !== 1) {
            throw new SpotDeskException('Failed to update last check for mailbox: ' . $mailbox->getId()->toString());
        }
        $mailbox->setLastCheck($checkTime);
    }

    public function deleteMailbox(Mailbox $mailbox) : void
    {
        $query = $this->db->prepare("
            DELETE FROM `mailboxes`
            WHERE `mailbox_id` = :mailbox_id
        ");
        $query->execute(['mailbox_id' => $mailbox->getId()->getBytes()]);

        if ($query->rowCount() !== 1) {
            throw new SpotDeskException('Failed to delete mailbox: ' . $mailbox->getId()->toString());
        }
    }
}
