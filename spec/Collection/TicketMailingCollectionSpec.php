<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Collection\TicketMailingCollection;
use jschreuder\SpotDesk\Entity\TicketMailing;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class TicketMailingCollectionSpec extends ObjectBehavior
{
    public function it_is_initializable(TicketMailing $ticketMailing1, TicketMailing $ticketMailing2) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $ticketMailing1->getId()->willReturn($uuid1);
        $ticketMailing2->getId()->willReturn($uuid2);

        $this->beConstructedWith($ticketMailing1, $ticketMailing2);
        $this->shouldHaveType(TicketMailingCollection::class);

        $this->offsetExists($uuid1->toString())->shouldBe(true);
        $this->offsetGet($uuid1->toString())->shouldReturn($ticketMailing1);
        $this->offsetExists($uuid2->toString())->shouldBe(true);
        $this->offsetGet($uuid2->toString())->shouldReturn($ticketMailing2);
    }

    public function it_can_push_items(
        TicketMailing $ticketMailing1,
        TicketMailing $ticketMailing2,
        TicketMailing $ticketMailing3
    ) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticketMailing1->getId()->willReturn($uuid1);
        $ticketMailing2->getId()->willReturn($uuid2);
        $ticketMailing3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticketMailing1, $ticketMailing2);

        $this->offsetExists($uuid3->toString())->shouldBe(false);
        $this->shouldThrow(\OutOfBoundsException::class)->duringOffsetGet($uuid3->toString());

        $this->push($ticketMailing3);
        $this->offsetExists($uuid3->toString())->shouldBe(true);
        $this->offsetGet($uuid3->toString())->shouldReturn($ticketMailing3);
    }

    public function it_is_iterable(
        TicketMailing $ticketMailing1,
        TicketMailing $ticketMailing2,
        TicketMailing $ticketMailing3
    ) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticketMailing1->getId()->willReturn($uuid1);
        $ticketMailing2->getId()->willReturn($uuid2);
        $ticketMailing3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticketMailing1, $ticketMailing2, $ticketMailing3);

        $ticketMailings = [[$uuid1, $ticketMailing1], [$uuid2, $ticketMailing2], [$uuid3, $ticketMailing3]];
        foreach ($ticketMailings as $pair) {
            list($uuid, $ticketMailing) = $pair;
            $this->current()->shouldReturn($ticketMailing);
            $this->key()->shouldReturn($uuid->toString());
            $this->valid()->shouldBe(true);
            $this->next();
        }
        $this->valid()->shouldBe(false);

        $this->rewind();
        $this->current()->shouldReturn($ticketMailing1);
    }

    public function it_is_countable(
        TicketMailing $ticketMailing1,
        TicketMailing $ticketMailing2,
        TicketMailing $ticketMailing3
    ) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticketMailing1->getId()->willReturn($uuid1);
        $ticketMailing2->getId()->willReturn($uuid2);
        $ticketMailing3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticketMailing1, $ticketMailing2);

        $this->count()->shouldBe(2);
        $this->push($ticketMailing3);
        $this->count()->shouldBe(3);
    }

    public function it_can_return_array(
        TicketMailing $ticketMailing1,
        TicketMailing $ticketMailing2,
        TicketMailing $ticketMailing3
    ) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $ticketMailing1->getId()->willReturn($uuid1);
        $ticketMailing2->getId()->willReturn($uuid2);
        $ticketMailing3->getId()->willReturn($uuid3);
        $this->beConstructedWith($ticketMailing1, $ticketMailing2, $ticketMailing3);

        $this->toArray()->shouldReturn([
            $uuid1->toString() => $ticketMailing1,
            $uuid2->toString() => $ticketMailing2,
            $uuid3->toString() => $ticketMailing3,
        ]);
    }

    public function it_cant_set_or_unset_entries_from_collection(TicketMailing $ticketMailing) : void
    {
        $this->shouldThrow(\RuntimeException::class)->duringOffsetSet('key', $ticketMailing);
        $this->shouldThrow(\RuntimeException::class)->duringOffsetUnset('key');
    }
}
