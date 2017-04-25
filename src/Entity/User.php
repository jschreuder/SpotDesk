<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\EmailAddressValue;

class User
{
    /** @var  EmailAddressValue */
    private $email;

    /** @var  string */
    private $displayName;

    /** @var  string */
    private $password;

    /** @var  ?string */
    private $totpSecret;

    public function __construct(
        EmailAddressValue $email,
        string $displayName,
        string $password,
        ?string $totpSecret
    ) {
        $this->email = $email;
        $this->displayName = $displayName;
        $this->password = $password;
        $this->totpSecret = $totpSecret;
    }

    public function getEmail(): EmailAddressValue
    {
        return $this->email;
    }

    public function setEmail(EmailAddressValue $email): void
    {
        $this->email = $email;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getTotpSecret(): string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(string $totpSecret): void
    {
        $this->totpSecret = $totpSecret;
    }
}
