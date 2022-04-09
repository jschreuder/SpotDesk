<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Value;

use InvalidArgumentException;

final class MailTransportSecurityValue
{
    const SECURITY_NONE = 'none';
    const SECURITY_SSL = 'ssl';
    const SECURITY_TLS = 'tls';

    public static function get(string $statusType) : self
    {
        return new self($statusType);
    }

    public static function getValues() : array
    {
        return [self::SECURITY_NONE, self::SECURITY_SSL, self::SECURITY_TLS];
    }

    private function __construct(private string $value)
    {
        if (!in_array($value, self::getValues(), true)) {
            throw new InvalidArgumentException('Invalid mail transport security flag: ' . $value);
        }
    }

    public function toString() : string
    {
        return $this->value;
    }
}
