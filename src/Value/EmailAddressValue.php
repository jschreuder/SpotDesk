<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Value;

final class EmailAddressValue
{
    public static function get(string $emailAddress) : self
    {
        return new self($emailAddress);
    }

    /** @var  string */
    private $value;

    private function __construct(string $emailAddress)
    {
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid e-mail address given: ' . $emailAddress);
        }
        $this->value = mb_strtolower($emailAddress);
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
