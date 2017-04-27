<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Collection\TicketCollection;
use jschreuder\SpotDesk\Entity\Ticket;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class TicketCollectionSpec extends ObjectBehavior
{
    public function it_is_initializable(Ticket $ticket1, Ticket $ticket2)
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $ticket1->getId()->willReturn($uuid1);
        $ticket2->getId()->willReturn($uuid2);

        $this->beConstructedWith($ticket1, $ticket2);
        $this->shouldHaveType(TicketCollection::class);

        $this->offsetExists($uuid1->toString())->shouldBe(true);
        $this->offsetGet($uuid1->toString())->shouldReturn($ticket1);
        $this->offsetExists($uuid2->toString())->shouldBe(true);
        $this->offsetGet($uuid2->toString())->shouldReturn($ticket2);
    }

    public function it_can_push_items(Ticket $ticket1, Ticket $ticket2, Ticket $ticket3)
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticket1->getId()->willReturn($uuid1);
        $ticket2->getId()->willReturn($uuid2);
        $ticket3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticket1, $ticket2);

        $this->offsetExists($uuid3->toString())->shouldBe(false);
        $this->shouldThrow(\OutOfBoundsException::class)->duringOffsetGet($uuid3->toString());

        $this->push($ticket3);
        $this->offsetExists($uuid3->toString())->shouldBe(true);
        $this->offsetGet($uuid3->toString())->shouldReturn($ticket3);
    }

    public function it_is_iterable(Ticket $ticket1, Ticket $ticket2, Ticket $ticket3)
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticket1->getId()->willReturn($uuid1);
        $ticket2->getId()->willReturn($uuid2);
        $ticket3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticket1, $ticket2, $ticket3);

        $tickets = [[$uuid1, $ticket1], [$uuid2, $ticket2], [$uuid3, $ticket3]];
        foreach ($tickets as $pair) {
            list($uuid, $ticket) = $pair;
            $this->current()->shouldReturn($ticket);
            $this->key()->shouldReturn($uuid->toString());
            $this->valid()->shouldBe(true);
            $this->next();
        }
        $this->valid()->shouldBe(false);

        $this->rewind();
        $this->current()->shouldReturn($ticket1);
    }

    public function it_is_countable(Ticket $ticket1, Ticket $ticket2, Ticket $ticket3)
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticket1->getId()->willReturn($uuid1);
        $ticket2->getId()->willReturn($uuid2);
        $ticket3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticket1, $ticket2);

        $this->count()->shouldBe(2);
        $this->push($ticket3);
        $this->count()->shouldBe(3);
    }

    public function it_can_return_array(Ticket $ticket1, Ticket $ticket2, Ticket $ticket3)
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticket1->getId()->willReturn($uuid1);
        $ticket2->getId()->willReturn($uuid2);
        $ticket3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticket1, $ticket2, $ticket3);

        $this->toArray()->shouldReturn([
            $uuid1->toString() => $ticket1,
            $uuid2->toString() => $ticket2,
            $uuid3->toString() => $ticket3,
        ]);
    }

    public function it_can_contain_a_total_count()
    {
        $this->shouldThrow(\RuntimeException::class)->duringGetTotalCount();

        $this->setTotalCount(42);
        $this->getTotalCount()->shouldBe(42);
    }

    public function it_cant_set_total_count_to_negative_value()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->duringSetTotalCount(-1);
    }

    public function it_cant_set_or_unset_entries_from_collection(Ticket $ticket)
    {
        $this->shouldThrow(\RuntimeException::class)->duringOffsetSet('key', $ticket);
        $this->shouldThrow(\RuntimeException::class)->duringOffsetUnset('key');
    }
}
