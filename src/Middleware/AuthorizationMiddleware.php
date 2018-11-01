<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Middleware;

use jschreuder\Middle\Exception\AuthenticationException;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Service\AuthorizationService\AuthorizableControllerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
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

    /** @throws  AuthenticationException */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler) : ResponseInterface
    {
        $role = $this->getUserRole($request);
        $permission = $this->getPermission($request);

        // Only allow access to delegate when permission is granted to user's role
        if (
            $this->rbac->isGranted($role, AuthorizableControllerInterface::ROLE_ADMIN)
            || $this->rbac->isGranted($role, $permission)
        ) {
            return $requestHandler->handle($request);
        }

        // YOU... SHALL NOT PASS!
        return new JsonResponse(['message' => 'Not authorized'], 403);
    }

    /** @throws  AuthenticationException */
    private function getUserRole(ServerRequestInterface $request) : RoleInterface
    {
        $user = $request->getAttribute('user');
        if (!$user instanceof User) {
            throw new AuthenticationException('No user available on request');
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
