<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\StatusTypeValue;

class Status
{
    const STATUS_NEW = 'new';
    const STATUS_OPEN = 'open';
    const STATUS_AWAITING_CLIENT = 'awaiting-client';
    const STATUS_CLOSED = 'closed';

    public function __construct(private string $name, private StatusTypeValue $type)
    {
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getType() : StatusTypeValue
    {
        return $this->type;
    }

    public function isOpen() : bool
    {
        return $this->type->toString() === StatusTypeValue::TYPE_OPEN;
    }

    public function isPaused() : bool
    {
        return $this->type->toString() === StatusTypeValue::TYPE_PAUSED;
    }

    public function isClosed() : bool
    {
        return $this->type->toString() === StatusTypeValue::TYPE_CLOSED;
    }
}
