<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\MailboxCollection;
use jschreuder\SpotDesk\Entity\Mailbox;
use Ramsey\Uuid\Uuid;

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

    private function arrayToMailbox(array $row): Mailbox
    {
        $department = is_null($row['department_id'])
            ? null
            : $this->departmentRepository->getDepartment(Uuid::fromBytes($row['department_id'])->toString());

        return new Mailbox(
            Uuid::fromBytes($row['mailbox_id']),
            $row['name'],
            $department,
            $row['smtp_server'],
            $row['smtp_port'],
            $row['smtp_security'],
            $row['smtp_user'],
            $row['smtp_pass']
        );
    }

    public function getMailboxes(): MailboxCollection
    {
        $query = $this->db->prepare("SELECT * FROM `mailboxes`");
        $query->execute();

        $mailboxCollection = new MailboxCollection();
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $mailboxCollection->push($this->arrayToMailbox($row));
        }
        return $mailboxCollection;
    }
}
