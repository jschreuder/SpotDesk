<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Entity\Status;
use jschreuder\SpotDesk\Value\StatusTypeValue;
use PhpSpec\ObjectBehavior;

class StatusSpec extends ObjectBehavior
{
    /** @var  string */
    private $name;

    /** @var  StatusTypeValue */
    private $type;

    public function let() : void
    {
        $this->beConstructedWith(
            $this->name = 'status',
            $this->type = StatusTypeValue::get(StatusTypeValue::TYPE_PAUSED)
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(Status::class);
    }

    public function it_can_access_its_properties() : void
    {
        $this->getName()->shouldReturn($this->name);
        $this->getType()->shouldReturn($this->type);
    }

    public function it_can_change_some_properties() : void
    {
        $name = 'newstatus';
        $this->setName($name);
        $this->getName()->shouldReturn($name);
    }

    public function it_can_tell_when_its_open() : void
    {
        $this->beConstructedWith($this->name, StatusTypeValue::get(StatusTypeValue::TYPE_OPEN));
        $this->isOpen()->shouldReturn(true);
        $this->isClosed()->shouldReturn(false);
        $this->isPaused()->shouldReturn(false);
    }

    public function it_can_tell_when_its_paused() : void
    {
        $this->beConstructedWith($this->name, StatusTypeValue::get(StatusTypeValue::TYPE_PAUSED));
        $this->isOpen()->shouldReturn(false);
        $this->isClosed()->shouldReturn(false);
        $this->isPaused()->shouldReturn(true);
    }

    public function it_can_tell_when_its_closed() : void
    {
        $this->beConstructedWith($this->name, StatusTypeValue::get(StatusTypeValue::TYPE_CLOSED));
        $this->isOpen()->shouldReturn(false);
        $this->isClosed()->shouldReturn(true);
        $this->isPaused()->shouldReturn(false);
    }
}
