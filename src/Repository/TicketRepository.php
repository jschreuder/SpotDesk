<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\TicketCollection;
use jschreuder\SpotDesk\Collection\TicketSubscriptionCollection;
use jschreuder\SpotDesk\Collection\TicketUpdateCollection;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketSubscription;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class TicketRepository
{
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
    )
    {
        $this->db = $db;
        $this->statusRepository = $statusRepository;
        $this->departmentRepository = $departmentRepository;
    }

    public function createTicket(
        EmailAddressValue $email,
        string $subject,
        string $message,
        \DateTimeInterface $createdAt,
        ?Department $department
    ): Ticket
    {
        $ticket = new Ticket(
            Uuid::uuid4(),
            random_bytes(127),
            $email,
            $subject,
            $message,
            $createdAt,
            0,
            $createdAt,
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
            'ticket_id' => $ticket->getId()->toString(),
            'secret_key' => $ticket->getSecretKey(),
            'email' => $ticket->getEmail()->toString(),
            'subject' => $ticket->getSubject(),
            'message' => $ticket->getMessage(),
            'created_at' => $ticket->getCreatedAt()->format('Y-m-d H:i:s'),
            'updates' => $ticket->getUpdates(),
            'last_update' => $ticket->getLastUpdate()->format('Y-m-d H:i:s'),
            'status' => $ticket->getStatus()->getStatus(),
            'department_id' => is_null($ticket->getDepartment()) ? null : $ticket->getDepartment()->getId()->toString(),
        ]);

        return $ticket;
    }

    private function arrayToTicket(array $row): Ticket
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
            $this->departmentRepository->getDepartment(Uuid::fromBytes($row['department']))
        );
    }

    public function getTicket(UuidInterface $id): Ticket
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

    public function getOpenTicketsForUser(EmailAddressValue $email): TicketCollection
    {
        // Fetch all tickets that are either assigned to a department the given user is a part of
        // or those that haven't been assigned to a specific department
        $query = $this->db->prepare("
            SELECT * 
            FROM `tickets` t
            LEFT JOIN `departments` d ON t.`department_id` = d.`department_id`
            LEFT JOIN `users_departments` ud ON (ud.`department_id` = d.`department_id` AND ud.`email` = :email) 
            WHERE ud.`email` IS NOT NULL OR t.`department_id` IS NULL
            ORDER BY t.`created_at`
        ");
        $query->execute(['email' => $email->toString()]);

        $ticketCollection = new TicketCollection();
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $ticketCollection->push($this->arrayToTicket($row));
        }
        return $ticketCollection;
    }

    private function arrayToTicketUpdate(array $row, Ticket $ticket): TicketUpdate
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

    public function getTicketUpdates(Ticket $ticket): TicketUpdateCollection
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

    private function arrayToTicketSubscription(array $row, Ticket $ticket): TicketSubscription
    {
        return new TicketSubscription(
            Uuid::fromBytes($row['ticket_subscription_id']),
            $ticket,
            EmailAddressValue::get($row['email'])
        );
    }

    public function getTicketSubscriptions(Ticket $ticket): TicketSubscriptionCollection
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
    ): bool
    {
        $query = $this->db->prepare("
            SELECT COUNT(*)
            FROM `tickets`
            WHERE `email` = :email 
                AND `subject` = :subject 
                AND `message` = :message 
                AND `department_id` " . (is_null($department) ? "IS NULL" : "= :department_id") . "
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