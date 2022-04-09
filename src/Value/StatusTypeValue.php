<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Value;

use InvalidArgumentException;

final class StatusTypeValue
{
    const TYPE_OPEN = 'open';
    const TYPE_PAUSED = 'paused';
    const TYPE_CLOSED = 'closed';

    public static function get(string $statusType) : self
    {
        return new self($statusType);
    }

    public static function getValues() : array
    {
        return [self::TYPE_OPEN, self::TYPE_PAUSED, self::TYPE_CLOSED];
    }

    private function __construct(private string $value)
    {
        if (!in_array($value, [self::TYPE_OPEN, self::TYPE_PAUSED, self::TYPE_CLOSED], true)) {
            throw new InvalidArgumentException('Invalid status type: ' . $value);
        }
    }

    public function toString() : string
    {
        return $this->value;
    }
}
