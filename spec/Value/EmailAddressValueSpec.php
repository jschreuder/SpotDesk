<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Value;

use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EmailAddressValueSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $email = 'some@mail.address';
        $this->beConstructedThrough('get', [$email]);
        $this->shouldHaveType(EmailAddressValue::class);
        $this->toString()->shouldBe($email);
        $this->getLocalPart()->shouldBe('some');
        $this->getDomain()->shouldBe('mail.address');
    }

    public function it_is_not_initializable_without_valid_email()
    {
        $email = 'not-an-email-address';
        $this->beConstructedThrough('get', [$email]);
        $this->shouldThrow(\DomainException::class)->duringInstantiation();
    }
}
