<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Collection\TicketSubscriptionCollection;
use jschreuder\SpotDesk\Entity\TicketSubscription;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class TicketSubscriptionCollectionSpec extends ObjectBehavior
{
    public function it_is_initializable(
        TicketSubscription $ticketSubscription1,
        TicketSubscription $ticketSubscription2
    ) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $ticketSubscription1->getId()->willReturn($uuid1);
        $ticketSubscription2->getId()->willReturn($uuid2);

        $this->beConstructedWith($ticketSubscription1, $ticketSubscription2);
        $this->shouldHaveType(TicketSubscriptionCollection::class);

        $this->offsetExists($uuid1->toString())->shouldBe(true);
        $this->offsetGet($uuid1->toString())->shouldReturn($ticketSubscription1);
        $this->offsetExists($uuid2->toString())->shouldBe(true);
        $this->offsetGet($uuid2->toString())->shouldReturn($ticketSubscription2);
    }

    public function it_can_push_items(
        TicketSubscription $ticketSubscription1,
        TicketSubscription $ticketSubscription2,
        TicketSubscription $ticketSubscription3
    ) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticketSubscription1->getId()->willReturn($uuid1);
        $ticketSubscription2->getId()->willReturn($uuid2);
        $ticketSubscription3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticketSubscription1, $ticketSubscription2);

        $this->offsetExists($uuid3->toString())->shouldBe(false);
        $this->shouldThrow(\OutOfBoundsException::class)->duringOffsetGet($uuid3->toString());

        $this->push($ticketSubscription3);
        $this->offsetExists($uuid3->toString())->shouldBe(true);
        $this->offsetGet($uuid3->toString())->shouldReturn($ticketSubscription3);
    }

    public function it_is_iterable(
        TicketSubscription $ticketSubscription1,
        TicketSubscription $ticketSubscription2,
        TicketSubscription $ticketSubscription3
    ) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticketSubscription1->getId()->willReturn($uuid1);
        $ticketSubscription2->getId()->willReturn($uuid2);
        $ticketSubscription3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticketSubscription1, $ticketSubscription2, $ticketSubscription3);

        $ticketSubscriptions = [
            [$uuid1, $ticketSubscription1],
            [$uuid2, $ticketSubscription2],
            [$uuid3, $ticketSubscription3]
        ];
        foreach ($ticketSubscriptions as $pair) {
            list($uuid, $ticketSubscription) = $pair;
            $this->current()->shouldReturn($ticketSubscription);
            $this->key()->shouldReturn($uuid->toString());
            $this->valid()->shouldBe(true);
            $this->next();
        }
        $this->valid()->shouldBe(false);

        $this->rewind();
        $this->current()->shouldReturn($ticketSubscription1);
    }

    public function it_is_countable(
        TicketSubscription $ticketSubscription1,
        TicketSubscription $ticketSubscription2,
        TicketSubscription $ticketSubscription3
    ) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticketSubscription1->getId()->willReturn($uuid1);
        $ticketSubscription2->getId()->willReturn($uuid2);
        $ticketSubscription3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticketSubscription1, $ticketSubscription2);

        $this->count()->shouldBe(2);
        $this->push($ticketSubscription3);
        $this->count()->shouldBe(3);
    }

    public function it_can_return_array(
        TicketSubscription $ticketSubscription1,
        TicketSubscription $ticketSubscription2,
        TicketSubscription $ticketSubscription3
    ) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticketSubscription1->getId()->willReturn($uuid1);
        $ticketSubscription2->getId()->willReturn($uuid2);
        $ticketSubscription3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticketSubscription1, $ticketSubscription2, $ticketSubscription3);

        $this->toArray()->shouldReturn([
            $uuid1->toString() => $ticketSubscription1,
            $uuid2->toString() => $ticketSubscription2,
            $uuid3->toString() => $ticketSubscription3,
        ]);
    }

    public function it_cant_set_or_unset_entries_from_collection(TicketSubscription $ticketSubscription) : void
    {
        $this->shouldThrow(\RuntimeException::class)->duringOffsetSet('key', $ticketSubscription);
        $this->shouldThrow(\RuntimeException::class)->duringOffsetUnset('key');
    }
}
