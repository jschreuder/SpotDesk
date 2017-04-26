<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketMailing;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\UuidInterface;

class TicketMailingSpec extends ObjectBehavior
{
    /** @var  UuidInterface */
    private $id;

    /** @var  Ticket */
    private $ticket;

    /** @var  TicketUpdate */
    private $ticketUpdate;

    /** @var  string */
    private $type;

    /** @var  ?\DateTimeInterface */
    private $sentAt;

    public function let(UuidInterface $id, Ticket $ticket, TicketUpdate $ticketUpdate)
    {
        $this->beConstructedWith(
            $this->id = $id,
            $this->ticket = $ticket,
            $this->ticketUpdate = $ticketUpdate,
            $this->type = 'new.update',
            $this->sentAt = new \DateTimeImmutable('-5 minutes')
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(TicketMailing::class);
    }

    public function it_can_access_its_properties()
    {
        $this->getId()->shouldReturn($this->id);
        $this->getTicket()->shouldReturn($this->ticket);
        $this->getTicketUpdate()->shouldReturn($this->ticketUpdate);
        $this->getType()->shouldReturn($this->type);
        $this->getSentAt()->shouldReturn($this->sentAt);
    }

    public function it_can_instantiate_without_sent_at()
    {
        $this->beConstructedWith($this->id, $this->ticket, $this->ticketUpdate, $this->type, null);
        $this->getSentAt()->shouldReturn(null);
    }

    public function it_can_change_some_properties()
    {
        $sentAt = new \DateTimeImmutable();
        $this->setSentAt($sentAt);
        $this->getSentAt()->shouldReturn($sentAt);
    }
}
