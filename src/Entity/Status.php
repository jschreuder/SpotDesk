<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\StatusTypeValue;

class Status
{
    /** @var  string */
    private $status;

    /** @var  StatusTypeValue */
    private $type;

    public function __construct(string $status, StatusTypeValue $type)
    {
        $this->status = $status;
        $this->type = $type;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getType(): StatusTypeValue
    {
        return $this->type;
    }
}
