<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Value;

class EmailAddressValue
{
    public static function get(string $emailAddress): self
    {
        return new self($emailAddress);
    }

    /** @var  string */
    private $value;

    private function __construct(string $emailAddress)
    {
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            throw new \DomainException('Invalid e-mail address given: ' . $emailAddress);
        }
        $this->value = $emailAddress;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function getLocalPart(): string
    {
        $parts = explode('@', $this->value, 2);
        return $parts[0];
    }

    public function getDomain(): string
    {
        $parts = explode('@', $this->value, 2);
        return $parts[1];
    }
}
