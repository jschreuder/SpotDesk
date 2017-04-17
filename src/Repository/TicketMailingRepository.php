<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\TicketMailingCollection;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketMailing;
use Ramsey\Uuid\Uuid;

class TicketMailingRepository
{
    /** @var  \PDO */
    private $db;

    /** @var  TicketRepository */
    private $ticketRepository;

    public function __construct(\PDO $db, TicketRepository $ticketRepository)
    {
        $this->db = $db;
        $this->ticketRepository = $ticketRepository;
    }

    public function createTicketMailing(Ticket $ticket, string $type): TicketMailing
    {
        $ticketMailing = new TicketMailing(
            Uuid::uuid4(),
            $ticket,
            $type
        );

        $query = $this->db->prepare("
            INSERT INTO `ticket_mailings` (`ticket_mailing_id`, `ticket_id`, `type`)
            VALUES (:ticket_mailing_id, :ticket_id, :type)
        ");
        $query->execute([
            'ticket_mailing_id' => $ticketMailing->getId()->getBytes(),
            'ticket_id' => $ticketMailing->getTicket()->getId()->getBytes(),
            'type' => $ticketMailing->getType(),
        ]);

        return $ticketMailing;
    }

    private function arrayToTicketMailing(array $row): TicketMailing
    {
        return new TicketMailing(
            Uuid::fromBytes($row['ticket_mailing_id']),
            $this->ticketRepository->getTicket(Uuid::fromBytes($row['ticket_id'])),
            $row['type'],
            is_null($row['sent_at'])
                ? null
                : \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['sent_at'])
        );
    }

    public function getUnsent(): TicketMailingCollection
    {
        $query = $this->db->prepare("
            SELECT * FROM `ticket_mailings` WHERE `sent_at` IS NULL
        ");
        $query->execute();

        $ticketMailingCollection = new TicketMailingCollection();
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $ticketMailingCollection->push($this->arrayToTicketMailing($row));
        }
        return $ticketMailingCollection;
    }

    public function setSent(TicketMailing $ticketMailing, ?\DateTimeInterface $sentAt = null)
    {
        $sentAt = $sentAt ?? new \DateTimeImmutable();
        $query = $this->db->prepare("
            UPDATE `ticket_mailings`
            SET `sent_at` = :sent_at
            WHERE `ticket_mailing_id` = :ticket_mailing_id
        ");
        $query->execute([
            'sent_at' => $sentAt->format('Y-m-d H:i:s'),
            'ticket_mailing_id' => $ticketMailing->getId()->getHex(),
        ]);

        if ($query->rowCount() !== 1) {
            throw new \RuntimeException(
                'Failed to update sent status for ticket-mailing: ' . $ticketMailing->getId()->toString()
            );
        }
        $ticketMailing->setSentAt($sentAt);
    }
}