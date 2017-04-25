<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\StatusTypeValue;

class Status
{
    const STATUS_NEW = 'new';
    const STATUS_OPEN = 'open';
    const STATUS_AWAITING_CLIENT = 'awaiting-client';
    const STATUS_CLOSED = 'closed';

    /** @var  string */
    private $name;

    /** @var  StatusTypeValue */
    private $type;

    public function __construct(string $name, StatusTypeValue $type)
    {
        $this->name = $name;
        $this->type = $type;
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
