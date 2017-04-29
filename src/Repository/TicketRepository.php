<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\TicketCollection;
use jschreuder\SpotDesk\Collection\TicketSubscriptionCollection;
use jschreuder\SpotDesk\Collection\TicketUpdateCollection;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\Status;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketSubscription;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use jschreuder\SpotDesk\Value\StatusTypeValue;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class TicketRepository
{
    const ALLOWED_SORT_COLUMNS = ['subject', 'updates', 'last_update', 'status'];

    /** @var  \PDO */
    private $db;

    /** @var  StatusRepository */
    private $statusRepository;

    /** @var  DepartmentRepository */
    private $departmentRepository;

    public function __construct(
        \PDO $db,
        StatusRepository $statusRepository,
        DepartmentRepository $departmentRepository
    ) {
        $this->db = $db;
        $this->statusRepository = $statusRepository;
        $this->departmentRepository = $departmentRepository;
    }

    public function createTicket(
        EmailAddressValue $email,
        string $subject,
        string $message,
        ?Department $department,
        ?\DateTimeInterface $createdAt = null
    ) : Ticket {
        $ticket = new Ticket(
            Uuid::uuid4(),
            random_bytes(127),
            $email,
            $subject,
            $message,
            $createdAt,
            0,
            $createdAt ?? new \DateTimeImmutable(),
            $this->statusRepository->getStatus('new'),
            $department
        );

        $query = $this->db->prepare("
            INSERT INTO `tickets` (
                `ticket_id`, `secret_key`, `email`, `subject`, `message`, `created_at`, `updates`, `last_update`, 
                `status`, `department_id`
            ) VALUES (
                :ticket_id, :secret_key, :email, :subject, :message, :created_at, :updates, :last_update, 
                :status, :department_id
            )
        ");
        $query->execute([
            'ticket_id' => $ticket->getId()->getBytes(),
            'secret_key' => $ticket->getSecretKey(),
            'email' => $ticket->getEmail()->toString(),
            'subject' => $ticket->getSubject(),
            'message' => $ticket->getMessage(),
            'created_at' => $ticket->getCreatedAt()->format('Y-m-d H:i:s'),
            'updates' => $ticket->getUpdates(),
            'last_update' => $ticket->getLastUpdate()->format('Y-m-d H:i:s'),
            'status' => $ticket->getStatus()->getName(),
            'department_id' => is_null($ticket->getDepartment()) ? null : $ticket->getDepartment()->getId()->getBytes(),
        ]);

        return $ticket;
    }

    private function arrayToTicket(array $row) : Ticket
    {
        return new Ticket(
            Uuid::fromBytes($row['ticket_id']),
            $row['secret_key'],
            EmailAddressValue::get($row['email']),
            $row['subject'],
            $row['message'],
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['created_at']),
            intval($row['updates']),
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['last_update']),
            $this->statusRepository->getStatus($row['status']),
            $row['department_id']
                ? $this->departmentRepository->getDepartment(Uuid::fromBytes($row['department_id']))
                : null
        );
    }

    public function getTicket(UuidInterface $id) : Ticket
    {
        $query = $this->db->prepare("
            SELECT * FROM `tickets` WHERE `ticket_id` = :ticket_id
        ");
        $query->execute(['ticket_id' => $id->getBytes()]);

        if ($query->rowCount() !== 1) {
            throw new \OutOfBoundsException('No ticket found for ID: ' . $id->toString());
        }

        return $this->arrayToTicket($query->fetch(\PDO::FETCH_ASSOC));
    }

    public function getTicketsForUser(
        EmailAddressValue $email,
        ?StatusTypeValue $statusType = null,
        int $limit,
        int $page,
        string $sortBy,
        string $sortDirection = 'ASC'
    ) : TicketCollection {
        if (!in_array($sortBy, self::ALLOWED_SORT_COLUMNS)) {
            throw new \InvalidArgumentException('Invalid column for sorting: ' . $sortBy);
        }
        if (!in_array(strtoupper($sortDirection), ['ASC', 'DESC'])) {
            throw new \InvalidArgumentException('Invalid direction for sorting: ' . $sortDirection);
        }

        // Fetch all tickets that are either assigned to a department the given user is a part of
        // or those that haven't been assigned to a specific department
        $offset = $limit * ($page - 1);
        $query = $this->db->prepare("
            SELECT t.*
            FROM `tickets` t
            LEFT JOIN `departments` d ON t.`department_id` = d.`department_id`
            LEFT JOIN `users_departments` ud ON (ud.`department_id` = d.`department_id` AND ud.`email` = :email)
            INNER JOIN `statuses` s ON t.`status` = s.`status` AND s.`type` = :status_type
            WHERE ud.`email` IS NOT NULL OR t.`department_id` IS NULL
            ORDER BY t.`{$sortBy}` {$sortDirection}
            LIMIT {$limit} OFFSET {$offset}
        ");
        $query->execute([
            'email' => $email->toString(),
            'status_type' => $statusType ? $statusType->toString() : StatusTypeValue::TYPE_OPEN,
        ]);

        $ticketCollection = new TicketCollection();
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $ticketCollection->push($this->arrayToTicket($row));
        }

        $countQuery = $this->db->prepare("
            SELECT COUNT(t.`ticket_id`) AS ticket_count
            FROM `tickets` t
            LEFT JOIN `departments` d ON t.`department_id` = d.`department_id`
            LEFT JOIN `users_departments` ud ON (ud.`department_id` = d.`department_id` AND ud.`email` = :email)
            INNER JOIN `statuses` s ON t.`status` = s.`status` AND s.`type` = :status_type
            WHERE ud.`email` IS NOT NULL OR t.`department_id` IS NULL
        ");
        $countQuery->execute([
            'email' => $email->toString(),
            'status_type' => $statusType ? $statusType->toString() : StatusTypeValue::TYPE_OPEN,
        ]);
        $ticketCollection->setTotalCount(intval($countQuery->fetchColumn()));

        return $ticketCollection;
    }

    public function updateTicketStatus(Ticket $ticket, Status $status) : void
    {
        $query = $this->db->prepare("
            UPDATE `tickets`
            SET `status` = :status
            WHERE `ticket_id` = :ticket_id
        ");
        $query->execute([
            'status' => $status->getName(),
            'ticket_id' => $ticket->getId()->getBytes(),
        ]);

        if ($query->rowCount() !== 1) {
            throw new \RuntimeException('Failed to update status for ticket: ' . $ticket->getId()->toString());
        }
        $ticket->setStatus($status);
    }

    public function updateTicketDepartment(Ticket $ticket, ?Department $department) : void
    {
        $query = $this->db->prepare("
            UPDATE `tickets`
            SET `department_id` = :department_id
            WHERE `ticket_id` = :ticket_id
        ");
        $query->execute([
            'department_id' => $department ? $department->getId()->getBytes() : null,
            'ticket_id' => $ticket->getId()->getBytes(),
        ]);

        if ($query->rowCount() !== 1) {
            throw new \RuntimeException('Failed to update department for ticket: ' . $ticket->getId()->toString());
        }
        $ticket->setDepartment($department);
    }

    public function moveTicketsFromDepartmentToDepartment(Department $oldDepartment, Department $newDepartment)
    {
        $query = $this->db->prepare("
            UPDATE `tickets`
            SET `department_id` = :new_department_id
            WHERE `department_id` = :old_department_id
        ");
        $query->execute([
            'new_department_id' => $newDepartment->getId()->getBytes(),
            'old_department_id' => $oldDepartment->getId()->getBytes(),
        ]);
    }

    public function deleteTicket(Ticket $ticket) : void
    {
        $query = $this->db->prepare("
            DELETE FROM `tickets`
            WHERE `ticket_id` = :ticket_id
        ");
        $query->execute([
            'ticket_id' => $ticket->getId()->getBytes(),
        ]);
    }

    public function deleteTicketsFromDepartment(Department $department)
    {
        $query = $this->db->prepare("
            DELETE FROM `tickets`
            WHERE `department_id` = :department_id
        ");
        $query->execute([
            'department_id' => $department->getId()->getBytes(),
        ]);
    }

    public function updateTicketUpdateStats(Ticket $ticket) : void
    {
        $query = $this->db->prepare("
            UPDATE `tickets` t
            INNER JOIN (
                    SELECT `ticket_id`, COUNT(`ticket_update_id`) updates, MAX(`created_at`) last_update 
                    FROM `ticket_updates` 
                    GROUP BY `ticket_id`
                ) tu ON (t.`ticket_id` = tu.`ticket_id`)
            SET t.`updates` = tu.updates, t.`last_update` = tu.last_update
            WHERE t.`ticket_id` = :ticket_id
        ");
        $query->execute(['ticket_id' => $ticket->getId()->getBytes()]);
    }

    public function createTicketUpdate(
        Ticket $ticket,
        EmailAddressValue $email,
        string $message,
        bool $internal,
        ?\DateTimeInterface $createdAt = null
    ) : TicketUpdate {
        $ticketUpdate = new TicketUpdate(
            Uuid::uuid4(),
            $ticket,
            $email,
            $message,
            $createdAt ?? new \DateTimeImmutable(),
            $internal
        );

        $query = $this->db->prepare("
            INSERT INTO `ticket_updates` (`ticket_update_id`, `ticket_id`, `email`, `message`, `created_at`, `internal`)
            VALUES (:ticket_update_id, :ticket_id, :email, :message, :created_at, :internal)
        ");
        $query->execute([
            'ticket_update_id' => $ticketUpdate->getId()->getBytes(),
            'ticket_id' => $ticketUpdate->getTicket()->getId()->getBytes(),
            'email' => $ticketUpdate->getEmail()->toString(),
            'message' => $ticketUpdate->getMessage(),
            'created_at' => $ticketUpdate->getCreatedAt()->format('Y-m-d H:i:s'),
            'internal' => $ticketUpdate->isInternal(),
        ]);

        $this->updateTicketUpdateStats($ticket);

        return $ticketUpdate;
    }

    private function arrayToTicketUpdate(array $row, Ticket $ticket) : TicketUpdate
    {
        return new TicketUpdate(
            Uuid::fromBytes($row['ticket_update_id']),
            $ticket,
            EmailAddressValue::get($row['email']),
            $row['message'],
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['created_at']),
            boolval($row['internal'])
        );
    }

    public function getTicketUpdates(Ticket $ticket) : TicketUpdateCollection
    {
        $query = $this->db->prepare("
            SELECT * FROM `ticket_updates` WHERE `ticket_id` = :ticket_id ORDER BY `created_at` ASC
        ");
        $query->execute(['ticket_id' => $ticket->getId()->getBytes()]);

        $updateCollection = new TicketUpdateCollection();
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $updateCollection->push($this->arrayToTicketUpdate($row, $ticket));
        }
        return $updateCollection;
    }

    private function arrayToTicketSubscription(array $row, Ticket $ticket) : TicketSubscription
    {
        return new TicketSubscription(
            Uuid::fromBytes($row['ticket_subscription_id']),
            $ticket,
            EmailAddressValue::get($row['email'])
        );
    }

    public function getTicketSubscriptions(Ticket $ticket) : TicketSubscriptionCollection
    {
        $query = $this->db->prepare("
            SELECT * FROM `ticket_subscriptions` WHERE `ticket_id` = :ticket_id
        ");
        $query->execute(['ticket_id' => $ticket->getId()->getBytes()]);

        $subscriptionCollection = new TicketSubscriptionCollection();
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $subscriptionCollection->push($this->arrayToTicketSubscription($row, $ticket));
        }
        return $subscriptionCollection;
    }

    public function isDuplicate(
        EmailAddressValue $email,
        string $subject,
        string $message,
        \DateTimeInterface $createdAt,
        ?Department $department
    ) : bool {
        $departmentWhere = (is_null($department) ? "IS NULL" : "= :department_id");
        $query = $this->db->prepare("
            SELECT COUNT(*)
            FROM `tickets`
            WHERE `email` = :email 
                AND `subject` = :subject 
                AND `message` = :message 
                AND `department_id` {$departmentWhere}
                AND `created_at` > :duplicate_before
                AND `created_at` < :duplicate_after
        ");
        $args = [
            'email' => $email->toString(),
            'subject' => $subject,
            'message' => $message,
            'duplicate_before' => date('Y-m-d H:i:s', strtotime('-1 day', $createdAt->getTimestamp())),
            'duplicate_after' => date('Y-m-d H:i:s', strtotime('+1 day', $createdAt->getTimestamp())),
        ];
        if (!is_null($department)) {
            $args['department_id'] = $department->getId()->toString();
        }
        $query->execute($args);
        return $query->fetchColumn() > 0;
    }
}
