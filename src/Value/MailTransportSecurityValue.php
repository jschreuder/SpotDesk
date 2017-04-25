<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Value;

final class MailTransportSecurityValue
{
    const SECURITY_NONE = 'none';
    const SECURITY_SSL = 'ssl';
    const SECURITY_TLS = 'tls';

    public static function get(string $statusType): self
    {
        return new self($statusType);
    }

    public static function getValues(): array
    {
        return [self::SECURITY_NONE, self::SECURITY_SSL, self::SECURITY_TLS];
    }

    /** @var  string */
    private $value;

    private function __construct(string $transportSecurity)
    {
        if (!in_array($transportSecurity, self::getValues(), true)) {
            throw new \DomainException('Invalid mail transport security flag: ' . $transportSecurity);
        }
        $this->value = $transportSecurity;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
