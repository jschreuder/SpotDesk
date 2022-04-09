<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\EmailAddressValue;
use Laminas\Permissions\Rbac\RoleInterface;

class User
{
    public function __construct(
        private EmailAddressValue $email,
        private string $displayName,
        private string $password,
        private RoleInterface $role,
        private bool $active = true
    ) {
        $this->email = $email;
        $this->displayName = $displayName;
        $this->password = $password;
        $this->role = $role;
        $this->active = $active;
    }

    public function getEmail() : EmailAddressValue
    {
        return $this->email;
    }

    public function getDisplayName() : string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName) : void
    {
        $this->displayName = $displayName;
    }

    public function getPassword() : string
    {
        return $this->password;
    }

    public function setPassword(string $password) : void
    {
        $this->password = $password;
    }

    public function getRole() : RoleInterface
    {
        return $this->role;
    }

    public function setRole(RoleInterface $role) : void
    {
        $this->role = $role;
    }

    public function isActive() : bool
    {
        return $this->active;
    }

    public function setActive(bool $active) : void
    {
        $this->active = $active;
    }
}
