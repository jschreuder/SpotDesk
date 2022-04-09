<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Exception\SpotDeskException;
use jschreuder\SpotDesk\Service\AuthorizationService\AuthorizableControllerInterface;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Laminas\Permissions\Rbac\Role;
use Laminas\Permissions\Rbac\RoleInterface;

class GuestUser extends User
{
    public function __construct()
    {
        $guestRole = new Role('guest');
        $guestRole->addPermission(AuthorizableControllerInterface::ROLE_PUBLIC);
        parent::__construct(
            EmailAddressValue::get('guest@spotdev.local'),
            'Guest',
            '',
            $guestRole,
            true
        );
    }

    public function setDisplayName(string $displayName) : void
    {
        throw new SpotDeskException('Can not modify NullUser');
    }

    public function setPassword(string $password) : void
    {
        throw new SpotDeskException('Can not modify NullUser');
    }

    public function setRole(RoleInterface $role) : void
    {
        throw new SpotDeskException('Can not modify NullUser');
    }

    public function setActive(bool $active) : void
    {
        throw new SpotDeskException('Can not modify NullUser');
    }
}
