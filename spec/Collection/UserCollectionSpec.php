<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Collection;

use jschreuder\SpotDesk\Collection\UserCollection;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;

class UserCollectionSpec extends ObjectBehavior
{
    public function it_is_initializable(User $user1, User $user2)
    {
        $email1 = EmailAddressValue::get('one@mail.com');
        $email2 = EmailAddressValue::get('two@mail.com');
        $user1->getEmail()->willReturn($email1);
        $user2->getEmail()->willReturn($email2);

        $this->beConstructedWith($user1, $user2);
        $this->shouldHaveType(UserCollection::class);

        $this->offsetExists($email1->toString())->shouldBe(true);
        $this->offsetGet($email1->toString())->shouldReturn($user1);
        $this->offsetExists($email2->toString())->shouldBe(true);
        $this->offsetGet($email2->toString())->shouldReturn($user2);
    }

    public function it_can_push_items(User $user1, User $user2, User $user3)
    {
        $email1 = EmailAddressValue::get('one@mail.com');
        $email2 = EmailAddressValue::get('two@mail.com');
        $email3 = EmailAddressValue::get('three@mail.com');
        $user1->getEmail()->willReturn($email1);
        $user2->getEmail()->willReturn($email2);
        $user3->getEmail()->willReturn($email3);
        $this->beConstructedWith($user1, $user2);

        $this->offsetExists($email3->toString())->shouldBe(false);
        $this->shouldThrow(\OutOfBoundsException::class)->duringOffsetGet($email3->toString());

        $this->push($user3);
        $this->offsetExists($email3->toString())->shouldBe(true);
        $this->offsetGet($email3->toString())->shouldReturn($user3);
    }

    public function it_is_iterable(User $user1, User $user2, User $user3)
    {
        $email1 = EmailAddressValue::get('one@mail.com');
        $email2 = EmailAddressValue::get('two@mail.com');
        $email3 = EmailAddressValue::get('three@mail.com');
        $user1->getEmail()->willReturn($email1);
        $user2->getEmail()->willReturn($email2);
        $user3->getEmail()->willReturn($email3);
        $this->beConstructedWith($user1, $user2, $user3);

        $users = [[$email1, $user1], [$email2, $user2], [$email3, $user3]];
        foreach ($users as $pair) {
            list($email, $user) = $pair;
            $this->current()->shouldReturn($user);
            $this->key()->shouldReturn($email->toString());
            $this->valid()->shouldBe(true);
            $this->next();
        }
        $this->valid()->shouldBe(false);

        $this->rewind();
        $this->current()->shouldReturn($user1);
    }

    public function it_is_countable(User $user1, User $user2, User $user3)
    {
        $email1 = EmailAddressValue::get('one@mail.com');
        $email2 = EmailAddressValue::get('two@mail.com');
        $email3 = EmailAddressValue::get('three@mail.com');
        $user1->getEmail()->willReturn($email1);
        $user2->getEmail()->willReturn($email2);
        $user3->getEmail()->willReturn($email3);
        $this->beConstructedWith($user1, $user2);

        $this->count()->shouldBe(2);
        $this->push($user3);
        $this->count()->shouldBe(3);
    }

    public function it_can_return_array(User $user1, User $user2, User $user3)
    {
        $email1 = EmailAddressValue::get('one@mail.com');
        $email2 = EmailAddressValue::get('two@mail.com');
        $email3 = EmailAddressValue::get('three@mail.com');
        $user1->getEmail()->willReturn($email1);
        $user2->getEmail()->willReturn($email2);
        $user3->getEmail()->willReturn($email3);
        $this->beConstructedWith($user1, $user2, $user3);

        $this->toArray()->shouldReturn([
            $email1->toString() => $user1,
            $email2->toString() => $user2,
            $email3->toString() => $user3,
        ]);
    }

    public function it_cant_set_or_unset_entries_from_collection(User $user)
    {
        $this->shouldThrow(\RuntimeException::class)->duringOffsetSet('key', $user);
        $this->shouldThrow(\RuntimeException::class)->duringOffsetUnset('key');
    }
}
