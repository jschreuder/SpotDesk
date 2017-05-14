<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\AuthorizationService;

interface AuthorizableControllerInterface
{
    const ROLE_PUBLIC = 'public';
    const ROLE_ADMIN = 'admin';

    public function getRequiredPermission() : string;
}
