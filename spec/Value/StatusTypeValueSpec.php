<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Value;

use jschreuder\SpotDesk\Value\StatusTypeValue;
use PhpSpec\ObjectBehavior;

class StatusTypeValueSpec extends ObjectBehavior
{
    public function it_can_contain_open() : void
    {
        $this->beConstructedThrough('get', [StatusTypeValue::TYPE_OPEN]);
        $this->shouldHaveType(StatusTypeValue::class);
        $this->toString()->shouldReturn(StatusTypeValue::TYPE_OPEN);
    }

    public function it_can_contain_paused() : void
    {
        $this->beConstructedThrough('get', [StatusTypeValue::TYPE_PAUSED]);
        $this->shouldHaveType(StatusTypeValue::class);
        $this->toString()->shouldReturn(StatusTypeValue::TYPE_PAUSED);
    }

    public function it_can_contain_closed() : void
    {
        $this->beConstructedThrough('get', [StatusTypeValue::TYPE_CLOSED]);
        $this->shouldHaveType(StatusTypeValue::class);
        $this->toString()->shouldReturn(StatusTypeValue::TYPE_CLOSED);
    }

    public function it_can_return_all_its_possible_values() : void
    {
        $this->beConstructedThrough('get', [StatusTypeValue::TYPE_CLOSED]);
        $this->getValues()->shouldReturn([
            StatusTypeValue::TYPE_OPEN,
            StatusTypeValue::TYPE_PAUSED,
            StatusTypeValue::TYPE_CLOSED,
        ]);
    }

    public function it_errors_on_invalid_value() : void
    {
        $this->beConstructedThrough('get', ['nonsense']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
