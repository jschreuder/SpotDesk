<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Collection\MailboxCollection;
use jschreuder\SpotDesk\Entity\Mailbox;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class MailboxCollectionSpec extends ObjectBehavior
{
    public function it_is_initializable(Mailbox $mailbox1, Mailbox $mailbox2) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $mailbox1->getId()->willReturn($uuid1);
        $mailbox2->getId()->willReturn($uuid2);

        $this->beConstructedWith($mailbox1, $mailbox2);
        $this->shouldHaveType(MailboxCollection::class);

        $this->shouldHaveKey($uuid1->toString());
        $this->offsetGet($uuid1->toString())->shouldReturn($mailbox1);
        $this->shouldHaveKey($uuid2->toString());
        $this->offsetGet($uuid2->toString())->shouldReturn($mailbox2);
    }

    public function it_can_push_items(Mailbox $mailbox1, Mailbox $mailbox2, Mailbox $mailbox3) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $mailbox1->getId()->willReturn($uuid1);
        $mailbox2->getId()->willReturn($uuid2);
        $mailbox3->getId()->willReturn($uuid3);
        $this->beConstructedWith($mailbox1, $mailbox2);

        $this->shouldNotHaveKey($uuid3->toString());
        $this->shouldThrow(\OutOfBoundsException::class)->duringOffsetGet($uuid3->toString());

        $this->push($mailbox3);
        $this->shouldHaveKey($uuid3->toString());
        $this->offsetGet($uuid3->toString())->shouldReturn($mailbox3);
    }

    public function it_is_iterable(Mailbox $mailbox1, Mailbox $mailbox2, Mailbox $mailbox3) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $mailbox1->getId()->willReturn($uuid1);
        $mailbox2->getId()->willReturn($uuid2);
        $mailbox3->getId()->willReturn($uuid3);
        $this->beConstructedWith($mailbox1, $mailbox2, $mailbox3);

        $mailboxs = [[$uuid1, $mailbox1], [$uuid2, $mailbox2], [$uuid3, $mailbox3]];
        foreach ($mailboxs as $pair) {
            list($uuid, $mailbox) = $pair;
            $this->current()->shouldReturn($mailbox);
            $this->key()->shouldReturn($uuid->toString());
            $this->valid()->shouldBe(true);
            $this->next();
        }
        $this->valid()->shouldBe(false);

        $this->rewind();
        $this->current()->shouldReturn($mailbox1);
    }

    public function it_is_countable(Mailbox $mailbox1, Mailbox $mailbox2, Mailbox $mailbox3) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $mailbox1->getId()->willReturn($uuid1);
        $mailbox2->getId()->willReturn($uuid2);
        $mailbox3->getId()->willReturn($uuid3);
        $this->beConstructedWith($mailbox1, $mailbox2);

        $this->count()->shouldBe(2);
        $this->push($mailbox3);
        $this->count()->shouldBe(3);
    }

    public function it_can_return_array(Mailbox $mailbox1, Mailbox $mailbox2, Mailbox $mailbox3) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $mailbox1->getId()->willReturn($uuid1);
        $mailbox2->getId()->willReturn($uuid2);
        $mailbox3->getId()->willReturn($uuid3);
        $this->beConstructedWith($mailbox1, $mailbox2, $mailbox3);

        $this->toArray()->shouldReturn([
            $uuid1->toString() => $mailbox1,
            $uuid2->toString() => $mailbox2,
            $uuid3->toString() => $mailbox3,
        ]);
    }

    public function it_cant_set_or_unset_entries_from_collection(Mailbox $mailbox) : void
    {
        $this->shouldThrow(\RuntimeException::class)->duringOffsetSet('key', $mailbox);
        $this->shouldThrow(\RuntimeException::class)->duringOffsetUnset('key');
    }
}
