<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketSubscription;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\UuidInterface;

class TicketSubscriptionSpec extends ObjectBehavior
{
    /** @var  UuidInterface */
    private $id;

    /** @var  Ticket */
    private $ticket;

    /** @var  EmailAddressValue */
    private $email;

    /** @var  bool */
    private $internal;
    
    /** @var  bool */
    private $sendNotifications;

    public function let(UuidInterface $id, Ticket $ticket) : void
    {
        $this->beConstructedWith(
            $this->id = $id,
            $this->ticket = $ticket,
            $this->email = EmailAddressValue::get('some@address.to'),
            $this->internal = false,
            $this->sendNotifications = true
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(TicketSubscription::class);
    }

    public function it_can_access_its_properties() : void
    {
        $this->getId()->shouldReturn($this->id);
        $this->getTicket()->shouldReturn($this->ticket);
        $this->getEmail()->shouldReturn($this->email);
        $this->isInternal()->shouldReturn($this->internal);
        $this->sendNotifications()->shouldReturn($this->sendNotifications);
    }
    
    public function it_can_change_some_properties() : void
    {
        $this->setInternal(true);
        $this->isInternal()->shouldBe(true);
        
        $this->setSendNotifications(false);
        $this->sendNotifications()->shouldBe(false);
    }
}
