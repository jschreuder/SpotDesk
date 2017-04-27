<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\UuidInterface;

class TicketUpdateSpec extends ObjectBehavior
{
    /** @var  UuidInterface */
    private $id;

    /** @var  Ticket */
    private $ticket;

    /** @var  EmailAddressValue */
    private $email;

    /** @var  string */
    private $message;

    /** @var  \DateTimeInterface */
    private $createdAt;

    /** @var  bool */
    private $internal;

    public function let(UuidInterface $id, Ticket $ticket) : void
    {
        $this->beConstructedWith(
            $this->id = $id,
            $this->ticket = $ticket,
            $this->email = EmailAddressValue::get('some@mail.address'),
            $this->message = 'Some response to the original inquiry',
            $this->createdAt = new \DateTimeImmutable('-1 week'),
            $this->internal = true
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(TicketUpdate::class);
    }

    public function it_can_access_its_properties() : void
    {
        $this->getId()->shouldReturn($this->id);
        $this->getTicket()->shouldReturn($this->ticket);
        $this->getEmail()->shouldReturn($this->email);
        $this->getMessage()->shouldReturn($this->message);
        $this->getCreatedAt()->shouldReturn($this->createdAt);
        $this->isInternal()->shouldBe($this->internal);
    }
}
