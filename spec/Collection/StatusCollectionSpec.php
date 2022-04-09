<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Collection\StatusCollection;
use jschreuder\SpotDesk\Entity\Status;
use PhpSpec\ObjectBehavior;

class StatusCollectionSpec extends ObjectBehavior
{
    public function it_is_initializable(Status $status1, Status $status2) : void
    {
        $statusName1 = 's1';
        $statusName2 = 's2';
        $status1->getName()->willReturn($statusName1);
        $status2->getName()->willReturn($statusName2);

        $this->beConstructedWith($status1, $status2);
        $this->shouldHaveType(StatusCollection::class);

        $this->shouldHaveKey($statusName1);
        $this->offsetGet($statusName1)->shouldReturn($status1);
        $this->shouldHaveKey($statusName2);
        $this->offsetGet($statusName2)->shouldReturn($status2);
    }

    public function it_can_push_items(Status $status1, Status $status2, Status $status3) : void
    {
        $statusName1 = 's1';
        $statusName2 = 's2';
        $statusName3 = 's3';
        $status1->getName()->willReturn($statusName1);
        $status2->getName()->willReturn($statusName2);
        $status3->getName()->willReturn($statusName3);
        $this->beConstructedWith($status1, $status2);

        $this->shouldNotHaveKey($statusName3);
        $this->shouldThrow(\OutOfBoundsException::class)->duringOffsetGet($statusName3);

        $this->push($status3);
        $this->shouldHaveKey($statusName3);
        $this->offsetGet($statusName3)->shouldReturn($status3);
    }

    public function it_is_iterable(Status $status1, Status $status2, Status $status3) : void
    {
        $statusName1 = 's1';
        $statusName2 = 's2';
        $statusName3 = 's3';
        $status1->getName()->willReturn($statusName1);
        $status2->getName()->willReturn($statusName2);
        $status3->getName()->willReturn($statusName3);
        $this->beConstructedWith($status1, $status2, $status3);

        $statuss = [[$statusName1, $status1], [$statusName2, $status2], [$statusName3, $status3]];
        foreach ($statuss as $pair) {
            list($statusName, $status) = $pair;
            $this->current()->shouldReturn($status);
            $this->key()->shouldReturn($statusName);
            $this->valid()->shouldBe(true);
            $this->next();
        }
        $this->valid()->shouldBe(false);

        $this->rewind();
        $this->current()->shouldReturn($status1);
    }

    public function it_is_countable(Status $status1, Status $status2, Status $status3) : void
    {
        $statusName1 = 's1';
        $statusName2 = 's2';
        $statusName3 = 's3';
        $status1->getName()->willReturn($statusName1);
        $status2->getName()->willReturn($statusName2);
        $status3->getName()->willReturn($statusName3);
        $this->beConstructedWith($status1, $status2);

        $this->count()->shouldBe(2);
        $this->push($status3);
        $this->count()->shouldBe(3);
    }

    public function it_can_return_array(Status $status1, Status $status2, Status $status3) : void
    {
        $statusName1 = 's1';
        $statusName2 = 's2';
        $statusName3 = 's3';
        $status1->getName()->willReturn($statusName1);
        $status2->getName()->willReturn($statusName2);
        $status3->getName()->willReturn($statusName3);
        $this->beConstructedWith($status1, $status2, $status3);

        $this->toArray()->shouldReturn([
            $statusName1 => $status1,
            $statusName2 => $status2,
            $statusName3 => $status3,
        ]);
    }

    public function it_cant_set_or_unset_entries_from_collection(Status $status) : void
    {
        $this->shouldThrow(\RuntimeException::class)->duringOffsetSet('key', $status);
        $this->shouldThrow(\RuntimeException::class)->duringOffsetUnset('key');
    }
}
