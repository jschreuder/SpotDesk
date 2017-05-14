<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Service\AuthorizationService\AuthorizableControllerInterface;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Zend\Permissions\Rbac\Role;
use Zend\Permissions\Rbac\RoleInterface;

class GuestUser extends User
{
    public function __construct()
    {
        parent::__construct(
            EmailAddressValue::get('guest@spotdev.local'),
            'Guest',
            '',
            (new Role('guest'))->addPermission(AuthorizableControllerInterface::ROLE_PUBLIC),
            true
        );
    }

    public function setDisplayName(string $displayName) : void
    {
        throw new \RuntimeException('Can not modify NullUser');
    }

    public function setPassword(string $password) : void
    {
        throw new \RuntimeException('Can not modify NullUser');
    }

    public function setRole(RoleInterface $role) : void
    {
        throw new \RuntimeException('Can not modify NullUser');
    }

    public function setActive(bool $active) : void
    {
        throw new \RuntimeException('Can not modify NullUser');
    }
}
