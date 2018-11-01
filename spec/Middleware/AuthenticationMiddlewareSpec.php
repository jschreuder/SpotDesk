<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Middleware;

use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Entity\GuestUser;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Middleware\AuthenticationMiddleware;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationServiceInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument\Token\TypeToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddlewareSpec extends ObjectBehavior
{
    /** @var  AuthenticationServiceInterface */
    private $authenticationService;

    public function let(AuthenticationServiceInterface $authenticationService) : void
    {
        $this->beConstructedWith(
            $this->authenticationService = $authenticationService
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(AuthenticationMiddleware::class);
    }

    public function it_can_handle_login_request(
        ServerRequestInterface $request,
        UriInterface $uri,
        RequestHandlerInterface $requestHandler,
        SessionInterface $session
    ) : void
    {
        $username = 'user@name.email';
        $password = 'i-ll-never-tell';

        $request->getMethod()->willReturn('POST');
        $request->getUri()->willReturn($uri);
        $uri->getPath()->willReturn('/login');
        $request->getParsedBody()->willReturn(['user' => $username, 'pass' => $password]);
        $request->getAttribute('session')->willReturn($session);

        $this->authenticationService->login($username, $password, $session)->willReturn(true);

        $response = $this->process($request, $requestHandler);
        $response->shouldBeAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBe(201);
    }

    public function it_can_handle_a_failed_login(
        ServerRequestInterface $request,
        UriInterface $uri,
        RequestHandlerInterface $requestHandler,
        SessionInterface $session
    ) : void
    {
        $username = 'user@name.email';
        $password = 'i-ll-never-tell';

        $request->getMethod()->willReturn('POST');
        $request->getUri()->willReturn($uri);
        $uri->getPath()->willReturn('/login');
        $request->getParsedBody()->willReturn(['user' => $username, 'pass' => $password]);
        $request->getAttribute('session')->willReturn($session);

        $this->authenticationService->login($username, $password, $session)->willReturn(false);

        $response = $this->process($request, $requestHandler);
        $response->shouldBeAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBe(401);
    }

    public function it_creates_guest_user_and_empty_session_when_not_logged_in(
        ServerRequestInterface $request1,
        ServerRequestInterface $request2,
        SessionInterface $session,
        UriInterface $uri,
        RequestHandlerInterface $requestHandler,
        ResponseInterface $response
    ) : void
    {
        $request1->getMethod()->willReturn('GET');
        $request1->getUri()->willReturn($uri);
        $uri->getPath()->willReturn('/something');
        $request1->getAttribute('session')->willReturn($session);

        $this->authenticationService->fetchUser('')->willThrow(new \OutOfBoundsException());

        $request1->withAttribute('user', new TypeToken(GuestUser::class))->willReturn($request2);

        $requestHandler->handle($request2)->willReturn($response);

        $this->process($request1, $requestHandler)->shouldReturn($response);
    }

    public function it_attaches_user_to_the_request_when_logged_in(
        ServerRequestInterface $request1,
        ServerRequestInterface $request2,
        UriInterface $uri,
        RequestHandlerInterface $requestHandler,
        SessionInterface $session,
        User $user,
        ResponseInterface $response
    ) : void
    {
        $userEmail = 'user@mail.co';

        $request1->getMethod()->willReturn('GET');
        $request1->getUri()->willReturn($uri);
        $uri->getPath()->willReturn('/something');
        $request1->getAttribute('session')->willReturn($session);

        $session->get('user')->willReturn($userEmail);
        $this->authenticationService->fetchUser($userEmail)->willReturn($user);
        $user->isActive()->willReturn(true);

        $request1->withAttribute('user', $user)->willReturn($request2);
        $requestHandler->handle($request2)->willReturn($response);

        $this->process($request1, $requestHandler)->shouldReturn($response);
    }

    public function it_attaches_falls_back_to_guest_on_inactive_user(
        ServerRequestInterface $request1,
        ServerRequestInterface $request2,
        UriInterface $uri,
        RequestHandlerInterface $requestHandler,
        SessionInterface $session,
        User $user,
        ResponseInterface $response
    ) : void
    {
        $userEmail = 'user@mail.co';

        $request1->getMethod()->willReturn('GET');
        $request1->getUri()->willReturn($uri);
        $uri->getPath()->willReturn('/something');
        $request1->getAttribute('session')->willReturn($session);

        $session->get('user')->willReturn($userEmail);
        $this->authenticationService->fetchUser($userEmail)->willThrow(new \OutOfBoundsException());
        $user->isActive()->willReturn(false);

        $request1->withAttribute('user', new TypeToken(GuestUser::class))->willReturn($request2);
        $requestHandler->handle($request2)->willReturn($response);

        $this->process($request1, $requestHandler)->shouldReturn($response);
    }
}
