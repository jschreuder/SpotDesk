<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Middleware\AuthenticationMiddleware;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationServiceInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class AuthenticationMiddlewareSpec extends ObjectBehavior
{
    /** @var  AuthenticationServiceInterface */
    private $authenticationService;

    public function let(AuthenticationServiceInterface $authenticationService)
    {
        $this->beConstructedWith(
            $this->authenticationService = $authenticationService
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AuthenticationMiddleware::class);
    }

    public function it_allows_public_access_to_root(
        ServerRequestInterface $request,
        UriInterface $uri,
        DelegateInterface $delegate,
        ResponseInterface $response
    )
    {
        $request->getMethod()->willReturn('GET');
        $request->getUri()->willReturn($uri);
        $uri->getPath()->willReturn('/');

        $delegate->process($request)->willReturn($response);

        $this->process($request, $delegate)->shouldReturn($response);
    }

    public function it_can_handle_login_request(
        ServerRequestInterface $request,
        UriInterface $uri,
        DelegateInterface $delegate,
        ResponseInterface $response
    )
    {
        $username = 'user@name.email';
        $password = 'i-ll-never-tell';

        $request->getMethod()->willReturn('POST');
        $request->getUri()->willReturn($uri);
        $uri->getPath()->willReturn('/login');
        $request->getParsedBody()->willReturn(['user' => $username, 'pass' => $password]);

        $this->authenticationService->login($username, $password)->willReturn($response);

        $this->process($request, $delegate)->shouldReturn($response);
    }

    public function it_sends_error_response_when_not_logged_in(
        ServerRequestInterface $request,
        UriInterface $uri,
        DelegateInterface $delegate
    )
    {
        $request->getMethod()->willReturn('GET');
        $request->getUri()->willReturn($uri);
        $uri->getPath()->willReturn('/something');

        $this->authenticationService->getSession($request)->willReturn(null);

        $response = $this->process($request, $delegate);
        $response->getStatusCode()->shouldBe(401);
    }

    public function it_attaches_session_to_the_request_and_response(
        ServerRequestInterface $request1,
        ServerRequestInterface $request2,
        UriInterface $uri,
        DelegateInterface $delegate,
        SessionInterface $session,
        ResponseInterface $response1,
        ResponseInterface $response2
    )
    {
        $request1->getMethod()->willReturn('GET');
        $request1->getUri()->willReturn($uri);
        $uri->getPath()->willReturn('/something');

        $this->authenticationService->getSession($request1)->willReturn($session);
        $request1->withAttribute('session', $session)->willReturn($request2);
        $delegate->process($request2)->willReturn($response1);

        $this->authenticationService->attachSession($response1, $session)->willReturn($response2);

        $this->process($request1, $delegate)->shouldReturn($response2);
    }
}
