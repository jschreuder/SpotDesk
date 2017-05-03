<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Value\EmailAddressValue;
use Zend\Permissions\Rbac\RoleInterface;

class User
{
    /** @var  EmailAddressValue */
    private $email;

    /** @var  string */
    private $displayName;

    /** @var  string */
    private $password;

    /** @var  RoleInterface */
    private $role;

    /** @var  bool */
    private $active;

    public function __construct(
        EmailAddressValue $email,
        string $displayName,
        string $password,
        RoleInterface $role,
        bool $active = true
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
