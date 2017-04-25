<?php declare(strict_types=1);

namespace spec\jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Collection\TicketUpdateCollection;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class TicketUpdateCollectionSpec extends ObjectBehavior
{
    public function it_is_initializable(TicketUpdate $ticketUpdate1, TicketUpdate $ticketUpdate2)
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $ticketUpdate1->getId()->willReturn($uuid1);
        $ticketUpdate2->getId()->willReturn($uuid2);

        $this->beConstructedWith($ticketUpdate1, $ticketUpdate2);
        $this->shouldHaveType(TicketUpdateCollection::class);

        $this->offsetExists($uuid1->toString())->shouldBe(true);
        $this->offsetGet($uuid1->toString())->shouldReturn($ticketUpdate1);
        $this->offsetExists($uuid2->toString())->shouldBe(true);
        $this->offsetGet($uuid2->toString())->shouldReturn($ticketUpdate2);
    }

    public function it_can_push_items(TicketUpdate $ticketUpdate1, TicketUpdate $ticketUpdate2, TicketUpdate $ticketUpdate3)
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticketUpdate1->getId()->willReturn($uuid1);
        $ticketUpdate2->getId()->willReturn($uuid2);
        $ticketUpdate3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticketUpdate1, $ticketUpdate2);

        $this->offsetExists($uuid3->toString())->shouldBe(false);
        $this->shouldThrow(\OutOfBoundsException::class)->duringOffsetGet($uuid3->toString());

        $this->push($ticketUpdate3);
        $this->offsetExists($uuid3->toString())->shouldBe(true);
        $this->offsetGet($uuid3->toString())->shouldReturn($ticketUpdate3);
    }

    public function it_is_iterable(TicketUpdate $ticketUpdate1, TicketUpdate $ticketUpdate2, TicketUpdate $ticketUpdate3)
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticketUpdate1->getId()->willReturn($uuid1);
        $ticketUpdate2->getId()->willReturn($uuid2);
        $ticketUpdate3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticketUpdate1, $ticketUpdate2, $ticketUpdate3);

        $ticketUpdates = [[$uuid1, $ticketUpdate1], [$uuid2, $ticketUpdate2], [$uuid3, $ticketUpdate3]];
        foreach ($ticketUpdates as $pair) {
            list($uuid, $ticketUpdate) = $pair;
            $this->current()->shouldReturn($ticketUpdate);
            $this->key()->shouldReturn($uuid->toString());
            $this->valid()->shouldBe(true);
            $this->next();
        }
        $this->valid()->shouldBe(false);

        $this->rewind();
        $this->current()->shouldReturn($ticketUpdate1);
    }

    public function it_is_countable(TicketUpdate $ticketUpdate1, TicketUpdate $ticketUpdate2, TicketUpdate $ticketUpdate3)
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticketUpdate1->getId()->willReturn($uuid1);
        $ticketUpdate2->getId()->willReturn($uuid2);
        $ticketUpdate3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticketUpdate1, $ticketUpdate2);

        $this->count()->shouldBe(2);
        $this->push($ticketUpdate3);
        $this->count()->shouldBe(3);
    }

    public function it_can_return_array(TicketUpdate $ticketUpdate1, TicketUpdate $ticketUpdate2, TicketUpdate $ticketUpdate3)
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticketUpdate1->getId()->willReturn($uuid1);
        $ticketUpdate2->getId()->willReturn($uuid2);
        $ticketUpdate3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticketUpdate1, $ticketUpdate2, $ticketUpdate3);

        $this->toArray()->shouldReturn([
            $uuid1->toString() => $ticketUpdate1,
            $uuid2->toString() => $ticketUpdate2,
            $uuid3->toString() => $ticketUpdate3,
        ]);
    }
}
