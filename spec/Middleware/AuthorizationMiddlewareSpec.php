<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Middleware;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Exception\AuthenticationException;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Middleware\AuthorizationMiddleware;
use jschreuder\SpotDesk\Service\AuthorizationService\AuthorizableControllerInterface;
use Laminas\Permissions\Rbac\Rbac;
use Laminas\Permissions\Rbac\RoleInterface;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthorizationMiddlewareSpec extends ObjectBehavior
{
    /** @var  Rbac */
    private $rbac;

    public function let(Rbac $rbac)
    {
        $this->beConstructedWith(
            $this->rbac = $rbac
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(AuthorizationMiddleware::class);
    }

    public function it_can_authorize_a_user(
        ServerRequestInterface $request,
        RequestHandlerInterface $requestHandler,
        ResponseInterface $response,
        User $user,
        RoleInterface $role,
        AuthorizableControllerInterface $controller
    ) : void
    {
        $permission = 'test';

        $request->getAttribute('controller')->willReturn($controller);
        $request->getAttribute('user')->willReturn($user);
        $user->getRole()->willReturn($role);
        $controller->getRequiredPermission()->willReturn($permission);

        $this->rbac->isGranted($role, AuthorizableControllerInterface::ROLE_ADMIN)->willReturn(false);
        $this->rbac->isGranted($role, $permission)->willReturn(true);
        $requestHandler->handle($request)->willReturn($response);

        $this->process($request, $requestHandler)->shouldReturn($response);
    }

    public function it_can_stop_unauthorized_user(
        ServerRequestInterface $request,
        RequestHandlerInterface $requestHandler,
        User $user,
        RoleInterface $role,
        AuthorizableControllerInterface $controller
    ) : void
    {
        $permission = 'test';

        $request->getAttribute('controller')->willReturn($controller);
        $request->getAttribute('user')->willReturn($user);
        $user->getRole()->willReturn($role);
        $controller->getRequiredPermission()->willReturn($permission);

        $this->rbac->isGranted($role, AuthorizableControllerInterface::ROLE_ADMIN)->willReturn(false);
        $this->rbac->isGranted($role, $permission)->willReturn(false);
        $requestHandler->handle($request)->shouldNotBeCalled();

        $response = $this->process($request, $requestHandler);
        $response->shouldBeAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBe(403);
    }

    public function it_will_allow_admins_everywhere(
        ServerRequestInterface $request,
        RequestHandlerInterface $requestHandler,
        ResponseInterface $response,
        User $user,
        RoleInterface $role,
        AuthorizableControllerInterface $controller
    ) : void
    {
        $permission = 'test';

        $request->getAttribute('controller')->willReturn($controller);
        $request->getAttribute('user')->willReturn($user);
        $user->getRole()->willReturn($role);
        $controller->getRequiredPermission()->willReturn($permission);

        $this->rbac->isGranted($role, AuthorizableControllerInterface::ROLE_ADMIN)->willReturn(true);
        $this->rbac->isGranted($role, $permission)->shouldNotBeCalled();
        $requestHandler->handle($request)->willReturn($response);

        $this->process($request, $requestHandler)->shouldReturn($response);
    }

    public function it_will_require_unknown_permission_on_non_authorizable_controller(
        ServerRequestInterface $request,
        RequestHandlerInterface $requestHandler,
        User $user,
        RoleInterface $role,
        ControllerInterface $controller
    ) : void
    {
        $request->getAttribute('controller')->willReturn($controller);
        $request->getAttribute('user')->willReturn($user);
        $user->getRole()->willReturn($role);

        $this->rbac->isGranted($role, AuthorizableControllerInterface::ROLE_ADMIN)->willReturn(false);
        $this->rbac->isGranted($role, AuthorizationMiddleware::UNKNOWN_PERMISSION)->willReturn(false);
        $requestHandler->handle($request)->shouldNotBeCalled();

        $response = $this->process($request, $requestHandler);
        $response->shouldBeAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBe(403);
    }

    public function it_will_error_when_request_has_no_user_attribute(
        ServerRequestInterface $request,
        RequestHandlerInterface $requestHandler
    ) : void
    {
        $request->getAttribute('user')->willReturn(null);
        $this->shouldThrow(AuthenticationException::class)->duringProcess($request, $requestHandler);
    }
}
