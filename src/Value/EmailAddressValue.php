<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Value;

use InvalidArgumentException;

final class EmailAddressValue
{
    public static function get(string $emailAddress) : self
    {
        return new self($emailAddress);
    }

    private function __construct(private string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid e-mail address given: ' . $value);
        }
        $this->value = mb_strtolower($value);
    }

    public function toString() : string
    {
        return $this->value;
    }

    public function isEqual(self $emailAddressValue) : bool
    {
        return $this->toString() === $emailAddressValue->toString();
    }

    public function getLocalPart() : string
    {
        $parts = explode('@', $this->value, 2);
        return $parts[0];
    }

    public function getDomain() : string
    {
        $parts = explode('@', $this->value, 2);
        return $parts[1];
    }
}
