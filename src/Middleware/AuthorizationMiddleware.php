<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Service\AuthorizationService\AuthorizableControllerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\RoleInterface;

class AuthorizationMiddleware implements MiddlewareInterface
{
    const UNKNOWN_PERMISSION = '__authorization_not_set';

    /** @var  Rbac */
    private $rbac;

    public function __construct(Rbac $rbac)
    {
        $this->rbac = $rbac;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        $role = $this->getUserRole($request);
        $permission = $this->getPermission($request);

        // Only allow access to delegate when permission is granted to user's role
        if (
            $this->rbac->isGranted($role, AuthorizableControllerInterface::ROLE_ADMIN)
            || $this->rbac->isGranted($role, $permission)
        ) {
            return $delegate->process($request);
        }

        // YOU... SHALL NOT PASS!
        return new JsonResponse(['message' => 'Not authorized'], 401);
    }

    private function getUserRole(ServerRequestInterface $request) : RoleInterface
    {
        $user = $request->getAttribute('user');
        if (!$user instanceof User) {
            throw new \RuntimeException('No user available on request');
        }

        return $user->getRole();
    }

    private function getPermission(ServerRequestInterface $request) : string
    {
        $controller = $request->getAttribute('controller');
        return ($controller instanceof AuthorizableControllerInterface)
            ? $controller->getRequiredPermission()
            : self::UNKNOWN_PERMISSION;
    }
}
