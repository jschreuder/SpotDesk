<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationService;
use jschreuder\SpotDesk\Service\AuthenticationService\SessionStorageInterface;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationServiceSpec extends ObjectBehavior
{
    /** @var  UserRepository */
    private $userRepository;

    /** @var  int */
    private $passwordAlgorithm;

    /** @var  array */
    private $passwordOptions;

    /** @var  SessionStorageInterface */
    private $sessionStorage;

    /** @var  int */
    private $sessionDuration;

    /** @var  float between 0 and 1, after how much of the duration a session should be refreshed */
    private $sessionRefreshAfter;

    public function let(UserRepository $userRepository, SessionStorageInterface $sessionStorage)
    {
        $this->beConstructedWith(
            $this->userRepository = $userRepository,
            $this->passwordAlgorithm = PASSWORD_BCRYPT,
            $this->passwordOptions = ['cost' => 8],
            $this->sessionStorage = $sessionStorage,
            $this->sessionDuration = 7200,
            $this->sessionRefreshAfter = 0.5
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AuthenticationService::class);
    }

    public function it_can_create_a_user()
    {
        $userMail = 'user@test.dev';
        $displayName = 'Username';
        $password = 'my-secret';

        $this->userRepository->createUser(new Argument\Token\TypeToken(User::class));

        $this->createUser($userMail, $displayName, $password);
    }

    public function it_can_login(User $user)
    {
        $userMail = 'user@test.dev';
        $password = 'my-secret';
        $sessionToken = 'session-token';
        $user->getEmail()->willReturn(EmailAddressValue::get($userMail));
        $user->getPassword()->willReturn(password_hash(
            $password,
            $this->passwordAlgorithm, $this->passwordOptions
        ));

        $this->userRepository->getUserByEmail(new Argument\Token\TypeToken(EmailAddressValue::class))
            ->willReturn($user);
        $this->sessionStorage->create($userMail, $this->sessionDuration)
            ->willReturn($sessionToken);

        $response = $this->login($userMail, $password);
        $response->shouldBeAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBe(201);
        $response->getHeaderLine(AuthenticationService::AUTHORIZATION_HEADER)->shouldBe($sessionToken);
    }

    public function it_fails_login_on_invalid_emailaddress()
    {
        $userEmail = 'not an e-mailaddress';
        $response = $this->login($userEmail, 'pass');
        $response->shouldBeAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBe(401);
    }

    public function it_fails_when_user_is_not_found()
    {
        $userMail = 'user@test.dev';
        $this->userRepository->getUserByEmail(new Argument\Token\TypeToken(EmailAddressValue::class))
            ->willThrow(new \OutOfBoundsException());
        $response = $this->login($userMail, 'pass');
        $response->shouldBeAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBe(401);
    }

    public function it_fails_login_on_incorrect_password(User $user)
    {
        $userMail = 'user@test.dev';
        $password = 'my-secret';
        $user->getEmail()->willReturn(EmailAddressValue::get($userMail));
        $user->getPassword()->willReturn(password_hash(
            $password,
            $this->passwordAlgorithm, $this->passwordOptions
        ));

        $this->userRepository->getUserByEmail(new Argument\Token\TypeToken(EmailAddressValue::class))
            ->willReturn($user);

        $response = $this->login($userMail, 'nope');
        $response->shouldBeAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBe(401);
    }

    public function it_regenerates_password_when_necessary(User $user)
    {
        $userMail = 'user@test.dev';
        $password = 'my-secret';
        $user->getEmail()->willReturn(EmailAddressValue::get($userMail));
        $user->getPassword()->willReturn(password_hash(
            $password,
            $this->passwordAlgorithm, ['cost' => 5]
        ));

        $this->userRepository->getUserByEmail(new Argument\Token\TypeToken(EmailAddressValue::class))
            ->willReturn($user);
        $this->userRepository->updatePassword($user, new Argument\Token\TypeToken('string'))
            ->shouldBeCalled();
        $this->sessionStorage->create($userMail, $this->sessionDuration)
            ->willReturn('session-token');

        $response = $this->login($userMail, $password);
        $response->shouldBeAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBe(201);
    }

    public function it_can_retrieve_a_session(ServerRequestInterface $request, SessionInterface $session)
    {
        $sessionData = 'data';
        $request->getHeaderLine(AuthenticationService::AUTHORIZATION_HEADER)->willReturn($sessionData);

        $this->sessionStorage->load($sessionData)->willReturn($session);

        $this->getSession($request)->shouldReturn($session);
    }

    public function it_returns_empty_when_there_is_no_session_data(ServerRequestInterface $request)
    {
        $request->getHeaderLine(AuthenticationService::AUTHORIZATION_HEADER)->willReturn(null);
        $this->getSession($request)->shouldReturn(null);
    }

    public function it_returns_empty_when_there_is_no_valid_session(ServerRequestInterface $request)
    {
        $sessionData = 'nope';
        $request->getHeaderLine(AuthenticationService::AUTHORIZATION_HEADER)->willReturn($sessionData);

        $this->sessionStorage->load($sessionData)->willReturn(null);

        $this->getSession($request)->shouldReturn(null);
    }

    public function it_can_attach_a_session_to_response(
        ResponseInterface $response,
        ResponseInterface $responseWithSession,
        SessionInterface $session
    ) {
        $this->sessionStorage->needsRefresh($session, 3600)->willReturn(true);

        $userMail = 'user@test.dev';
        $sessionData = 'data';
        $session->get('user')->willReturn($userMail);
        $this->sessionStorage->create($userMail, $this->sessionDuration)->willReturn($sessionData);
        $response->withHeader(AuthenticationService::AUTHORIZATION_HEADER, $sessionData)
            ->willReturn($responseWithSession);

        $this->attachSession($response, $session)->shouldReturn($responseWithSession);
    }

    public function it_will_do_nothing_when_no_session_refresh_is_necessary(
        ResponseInterface $response,
        SessionInterface $session
    ) {
        $this->sessionStorage->needsRefresh($session, 3600)->willReturn(false);
        $this->attachSession($response, $session)->shouldReturn($response);
    }
}