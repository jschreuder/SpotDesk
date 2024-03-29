<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Collection\DepartmentCollection;
use jschreuder\SpotDesk\Entity\Department;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class DepartmentCollectionSpec extends ObjectBehavior
{
    public function it_is_initializable(Department $department1, Department $department2) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $department1->getId()->willReturn($uuid1);
        $department2->getId()->willReturn($uuid2);

        $this->beConstructedWith($department1, $department2);
        $this->shouldHaveType(DepartmentCollection::class);

        $this->shouldHaveKey($uuid1->toString());
        $this->offsetGet($uuid1->toString())->shouldReturn($department1);
        $this->shouldHaveKey($uuid2->toString());
        $this->offsetGet($uuid2->toString())->shouldReturn($department2);
    }

    public function it_can_push_items(Department $department1, Department $department2, Department $department3) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $department1->getId()->willReturn($uuid1);
        $department2->getId()->willReturn($uuid2);
        $department3->getId()->willReturn($uuid3);
        $this->beConstructedWith($department1, $department2);

        $this->shouldNotHaveKey($uuid3->toString());
        $this->shouldThrow(\OutOfBoundsException::class)->duringOffsetGet($uuid3->toString());

        $this->push($department3);
        $this->shouldHaveKey($uuid3->toString());
        $this->offsetGet($uuid3->toString())->shouldReturn($department3);
    }

    public function it_is_iterable(Department $department1, Department $department2, Department $department3) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $department1->getId()->willReturn($uuid1);
        $department2->getId()->willReturn($uuid2);
        $department3->getId()->willReturn($uuid3);
        $this->beConstructedWith($department1, $department2, $department3);

        $departments = [[$uuid1, $department1], [$uuid2, $department2], [$uuid3, $department3]];
        foreach ($departments as $pair) {
            list($uuid, $department) = $pair;
            $this->current()->shouldReturn($department);
            $this->key()->shouldReturn($uuid->toString());
            $this->valid()->shouldBe(true);
            $this->next();
        }
        $this->valid()->shouldBe(false);

        $this->rewind();
        $this->current()->shouldReturn($department1);
    }

    public function it_is_countable(Department $department1, Department $department2, Department $department3) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $department1->getId()->willReturn($uuid1);
        $department2->getId()->willReturn($uuid2);
        $department3->getId()->willReturn($uuid3);
        $this->beConstructedWith($department1, $department2);

        $this->count()->shouldBe(2);
        $this->push($department3);
        $this->count()->shouldBe(3);
    }

    public function it_can_return_array(
        Department $department1,
        Department $department2,
        Department $department3
    ) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $department1->getId()->willReturn($uuid1);
        $department2->getId()->willReturn($uuid2);
        $department3->getId()->willReturn($uuid3);
        $this->beConstructedWith($department1, $department2, $department3);

        $this->toArray()->shouldReturn([
            $uuid1->toString() => $department1,
            $uuid2->toString() => $department2,
            $uuid3->toString() => $department3,
        ]);
    }

    public function it_cant_set_or_unset_entries_from_collection(Department $department) : void
    {
        $this->shouldThrow(\RuntimeException::class)->duringOffsetSet('key', $department);
        $this->shouldThrow(\RuntimeException::class)->duringOffsetUnset('key');
    }
}
