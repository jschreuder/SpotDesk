<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Middleware\AuthorizationMiddleware;
use jschreuder\SpotDesk\Service\AuthorizationService\AuthorizableControllerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\RoleInterface;

class AuthorizationMiddlewareSpec extends ObjectBehavior
{
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
        DelegateInterface $delegate,
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
        $delegate->process($request)->willReturn($response);

        $this->process($request, $delegate)->shouldReturn($response);
    }

    public function it_can_stop_unauthorized_user(
        ServerRequestInterface $request,
        DelegateInterface $delegate,
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
        $delegate->process($request)->shouldNotBeCalled();

        $response = $this->process($request, $delegate);
        $response->shouldBeAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBe(401);
    }

    public function it_will_allow_admins_everywhere(
        ServerRequestInterface $request,
        DelegateInterface $delegate,
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
        $delegate->process($request)->willReturn($response);

        $this->process($request, $delegate)->shouldReturn($response);
    }

    public function it_will_require_unknown_permission_on_non_authorizable_controller(
        ServerRequestInterface $request,
        DelegateInterface $delegate,
        User $user,
        RoleInterface $role,
        ControllerInterface $controller
    )
    {
        $request->getAttribute('controller')->willReturn($controller);
        $request->getAttribute('user')->willReturn($user);
        $user->getRole()->willReturn($role);

        $this->rbac->isGranted($role, AuthorizableControllerInterface::ROLE_ADMIN)->willReturn(false);
        $this->rbac->isGranted($role, AuthorizationMiddleware::UNKNOWN_PERMISSION)->willReturn(false);
        $delegate->process($request)->shouldNotBeCalled();

        $response = $this->process($request, $delegate);
        $response->shouldBeAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBe(401);
    }
}
