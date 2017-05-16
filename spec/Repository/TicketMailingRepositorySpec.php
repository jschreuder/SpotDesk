<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\TicketMailingCollection;
use jschreuder\SpotDesk\Collection\TicketUpdateCollection;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketMailing;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use jschreuder\SpotDesk\Repository\TicketMailingRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class TicketMailingRepositorySpec extends ObjectBehavior
{
    /** @var  \PDO */
    private $db;

    /** @var  TicketRepository */
    private $ticketRepository;

    public function let(\PDO $db, TicketRepository $ticketRepository) : void
    {
        $this->beConstructedWith(
            $this->db = $db,
            $this->ticketRepository = $ticketRepository
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(TicketMailingRepository::class);
    }

    public function it_can_create_a_ticket_mailing(\PDOStatement $statement, Ticket $ticket) : void
    {
        $ticket->getId()->willReturn($ticketId = Uuid::uuid4());
        $type = 'mailing';

        $this->db->prepare(new Argument\Token\StringContainsToken('INSERT'))->willReturn($statement);
        $statement->execute(new Argument\Token\TypeToken('array'))->shouldBeCalled();

        $ticketMailing = $this->createTicketMailing($ticket, $type, null);
        $ticketMailing->getId()->shouldHaveType(UuidInterface::class);
        $ticketMailing->getTicket()->shouldBe($ticket);
        $ticketMailing->getTicketUpdate()->shouldBe(null);
        $ticketMailing->getType()->shouldBe($type);
    }

    public function it_can_create_a_ticket_mailing_with_ticket_update(
        \PDOStatement $statement,
        Ticket $ticket,
        TicketUpdate $ticketUpdate
    ) : void
    {
        $ticket->getId()->willReturn($ticketId = Uuid::uuid4());
        $ticketUpdate->getId()->willReturn($ticketUpdateId = Uuid::uuid4());
        $type = 'mailing';

        $this->db->prepare(new Argument\Token\StringContainsToken('INSERT'))->willReturn($statement);
        $statement->execute(new Argument\Token\TypeToken('array'))->shouldBeCalled();

        $ticketMailing = $this->createTicketMailing($ticket, $type, $ticketUpdate);
        $ticketMailing->getId()->shouldHaveType(UuidInterface::class);
        $ticketMailing->getTicket()->shouldBe($ticket);
        $ticketMailing->getTicketUpdate()->shouldBe($ticketUpdate);
        $ticketMailing->getType()->shouldBe($type);
    }

    public function it_can_get_unsent_ticket_mailings(
        \PDOStatement $statement,
        Ticket $ticket1,
        Ticket $ticket2,
        TicketUpdateCollection $ticketUpdateCollection1,
        TicketUpdateCollection $ticketUpdateCollection2,
        TicketUpdate $ticketUpdate1
    ) : void
    {
        $this->db->query(new Argument\Token\StringContainsToken('SELECT'))->willReturn($statement);
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn(
            [
                'ticket_mailing_id' => ($ticketMailingId1 = Uuid::uuid4())->getBytes(),
                'ticket_id' => ($ticketId1 = Uuid::uuid4())->getBytes(),
                'ticket_update_id' => ($ticketUpdateId1 = Uuid::uuid4())->getBytes(),
                'type' => 'mailing',
                'sent_at' => null,
            ],
            [
                'ticket_mailing_id' => ($ticketMailingId2 = Uuid::uuid4())->getBytes(),
                'ticket_id' => ($ticketId2 = Uuid::uuid4())->getBytes(),
                'ticket_update_id' => null,
                'type' => 'mailing',
                'sent_at' => null,
            ],
            null
        );

        $this->ticketRepository->getTicket(new Argument\Token\TypeToken(UuidInterface::class))
            ->willReturn($ticket1, $ticket2, null);
        $this->ticketRepository->getTicketUpdates($ticket1)->willReturn($ticketUpdateCollection1);
        $this->ticketRepository->getTicketUpdates($ticket2)->willReturn($ticketUpdateCollection2);

        $ticketUpdateCollection1->offsetGet($ticketUpdateId1->toString())->willReturn($ticketUpdate1);

        $ticketMailingCollection = $this->getUnsent();
        $ticketMailingCollection->shouldHaveType(TicketMailingCollection::class);
        $ticketMailingCollection[$ticketMailingId1->toString()]->getTicket()->shouldBe($ticket1);
        $ticketMailingCollection[$ticketMailingId1->toString()]->getTicketUpdate()->shouldBe($ticketUpdate1);
        $ticketMailingCollection[$ticketMailingId2->toString()]->getTicket()->shouldBe($ticket2);
    }

    public function it_can_mark_ticket_mailing_as_sent(\PDOStatement $statement, TicketMailing $ticketMailing) : void
    {
        $sentTime = new \DateTimeImmutable();
        $ticketMailing->getId()->willReturn($ticketMailingId = Uuid::uuid4());

        $this->db->prepare(new Argument\Token\StringContainsToken('UPDATE'))->willReturn($statement);
        $statement->execute([
            'sent_at' => $sentTime->format('Y-m-d H:i:s'),
            'ticket_mailing_id' => $ticketMailingId->getBytes(),
        ])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);

        $ticketMailing->setSentAt($sentTime)->shouldBeCalled();

        $this->setSent($ticketMailing, $sentTime);
    }

    public function it_will_error_when_marking_ticket_mailing_as_sent_failed(
        \PDOStatement $statement,
        TicketMailing $ticketMailing
    ) : void
    {
        $ticketMailing->getId()->willReturn($ticketMailingId = Uuid::uuid4());

        $this->db->prepare(new Argument\Token\StringContainsToken('UPDATE'))->willReturn($statement);
        $statement->execute(new Argument\Token\TypeToken('array'))->shouldBeCalled();
        $statement->rowCount()->willReturn(0);

        $this->shouldThrow(\RuntimeException::class)->duringSetSent($ticketMailing);
    }
}
