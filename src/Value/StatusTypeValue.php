<?php

namespace jschreuder\SpotDesk\Value;

class StatusTypeValue
{
    const TYPE_OPEN = 'open';
    const TYPE_PAUSED = 'paused';
    const TYPE_CLOSED = 'closed';

    static public function get(string $statusType): self
    {
        return new self($statusType);
    }

    /** @var  string */
    private $value;

    private function __construct(string $statusType)
    {
        if (!in_array($statusType, [self::TYPE_OPEN, self::TYPE_PAUSED, self::TYPE_CLOSED], true)) {
            throw new \DomainException('Invalid status type: ' . $statusType);
        }
        $this->value = $statusType;
    }

    public function toString(): string
    {
        return $this->value;
    }
}