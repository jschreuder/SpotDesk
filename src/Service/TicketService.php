<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service;

use jschreuder\SpotDesk\Collection\TicketSubscriptionCollection;
use jschreuder\SpotDesk\Collection\TicketUpdateCollection;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketSubscription;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class TicketService
{
    /** @var  \PDO */
    private $db;

    /** @var  StatusService */
    private $statusService;

    /** @var  DepartmentService */
    private $departmentService;

    public function __construct(
        \PDO $db,
        StatusService $statusService,
        DepartmentService $departmentService
    )
    {
        $this->db = $db;
        $this->statusService = $statusService;
        $this->departmentService = $departmentService;
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
            $this->statusService->getStatus($row['status']),
            $this->departmentService->getDepartment(Uuid::fromBytes($row['department'])->toString())
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
}
