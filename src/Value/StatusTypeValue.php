<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Value;

final class StatusTypeValue
{
    const TYPE_OPEN = 'open';
    const TYPE_PAUSED = 'paused';
    const TYPE_CLOSED = 'closed';

    public static function get(string $statusType): self
    {
        return new self($statusType);
    }

    public static function getValues(): array
    {
        return [self::TYPE_OPEN, self::TYPE_PAUSED, self::TYPE_CLOSED];
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
