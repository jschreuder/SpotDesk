<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Value;

use jschreuder\SpotDesk\Value\MailTransportSecurityValue;
use PhpSpec\ObjectBehavior;

class MailTransportSecurityValueSpec extends ObjectBehavior
{
    public function it_can_contain_none() : void
    {
        $this->beConstructedThrough('get', [MailTransportSecurityValue::SECURITY_NONE]);
        $this->shouldHaveType(MailTransportSecurityValue::class);
        $this->toString()->shouldReturn(MailTransportSecurityValue::SECURITY_NONE);
    }

    public function it_can_contain_ssl() : void
    {
        $this->beConstructedThrough('get', [MailTransportSecurityValue::SECURITY_SSL]);
        $this->shouldHaveType(MailTransportSecurityValue::class);
        $this->toString()->shouldReturn(MailTransportSecurityValue::SECURITY_SSL);
    }

    public function it_can_contain_tls() : void
    {
        $this->beConstructedThrough('get', [MailTransportSecurityValue::SECURITY_TLS]);
        $this->shouldHaveType(MailTransportSecurityValue::class);
        $this->toString()->shouldReturn(MailTransportSecurityValue::SECURITY_TLS);
    }

    public function it_can_return_all_its_possible_values() : void
    {
        $this->beConstructedThrough('get', [MailTransportSecurityValue::SECURITY_TLS]);
        $this->getValues()->shouldReturn([
            MailTransportSecurityValue::SECURITY_NONE,
            MailTransportSecurityValue::SECURITY_SSL,
            MailTransportSecurityValue::SECURITY_TLS,
        ]);
    }

    public function it_errors_on_invalid_value() : void
    {
        $this->beConstructedThrough('get', ['nonsense']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
