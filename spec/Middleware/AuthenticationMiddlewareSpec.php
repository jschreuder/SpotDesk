<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Entity\GuestUser;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Middleware\AuthenticationMiddleware;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationServiceInterface;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument\Token\TypeToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class AuthenticationMiddlewareSpec extends ObjectBehavior
{
    /** @var  AuthenticationServiceInterface */
    private $authenticationService;

    /** @var  UserRepository */
    private $userRepository;

    public function let(AuthenticationServiceInterface $authenticationService, UserRepository $userRepository) : void
    {
        $this->beConstructedWith(
            $this->authenticationService = $authenticationService,
            $this->userRepository = $userRepository
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(AuthenticationMiddleware::class);
    }

    public function it_can_handle_login_request(
        ServerRequestInterface $request,
        UriInterface $uri,
        DelegateInterface $delegate,
        ResponseInterface $response
    ) : void
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

    public function it_creates_guest_user_and_empty_session_when_not_logged_in(
        ServerRequestInterface $request1,
        ServerRequestInterface $request2,
        ServerRequestInterface $request3,
        SessionInterface $session,
        UriInterface $uri,
        DelegateInterface $delegate,
        ResponseInterface $response
    ) : void
    {
        $request1->getMethod()->willReturn('GET');
        $request1->getUri()->willReturn($uri);
        $uri->getPath()->willReturn('/something');

        $this->authenticationService->getSession($request1)->willReturn($session);

        $request1->withAttribute('session', $session)->willReturn($request2);
        $request2->withAttribute('user', new TypeToken(GuestUser::class))->willReturn($request3);

        $delegate->process($request3)->willReturn($response);
        $this->authenticationService->attachSession($response, $session)->willReturn($response);

        $this->process($request1, $delegate)->shouldReturn($response);
    }

    public function it_attaches_session_to_the_request_and_response(
        ServerRequestInterface $request1,
        ServerRequestInterface $request2,
        ServerRequestInterface $request3,
        UriInterface $uri,
        DelegateInterface $delegate,
        SessionInterface $session,
        User $user,
        ResponseInterface $response1,
        ResponseInterface $response2
    ) : void
    {
        $userEmail = 'user@mail.co';

        $request1->getMethod()->willReturn('GET');
        $request1->getUri()->willReturn($uri);
        $uri->getPath()->willReturn('/something');

        $this->authenticationService->getSession($request1)->willReturn($session);
        $session->get('user')->willReturn($userEmail);
        $this->userRepository->getUserByEmail(EmailAddressValue::get($userEmail))->willReturn($user);
        $user->isActive()->willReturn(true);

        $request1->withAttribute('session', $session)->willReturn($request2);
        $request2->withAttribute('user', $user)->willReturn($request3);
        $delegate->process($request3)->willReturn($response1);

        $this->authenticationService->attachSession($response1, $session)->willReturn($response2);

        $this->process($request1, $delegate)->shouldReturn($response2);
    }
}
