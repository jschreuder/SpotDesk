<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationFailedException;
use jschreuder\SpotDesk\Service\AuthenticationService\JwtAuthenticationService;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Lcobucci\JWT\Signer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class JwtAuthenticationServiceSpec extends ObjectBehavior
{
    /** @var  UserRepository */
    private $userRepository;

    /** @var  int */
    private $passwordAlgorithm;

    /** @var  array */
    private $passwordOptions;

    /** @var  string */
    private $siteUrl;

    /** @var  Signer */
    private $jwtSigner;

    /** @var  mixed */
    private $jwtKey;

    /** @var  int */
    private $sessionDuration;

    /** @var  float between 0 and 1, after how much of the duration a session should be refreshed */
    private $sessionRefreshAfter;

    public function let(UserRepository $userRepository, Signer $signer)
    {
        $this->beConstructedWith(
            $this->userRepository = $userRepository,
            $this->passwordAlgorithm = PASSWORD_BCRYPT,
            $this->passwordOptions = ['cost' => 8],
            $this->siteUrl = 'http://localhost:8080',
            $this->jwtSigner = $signer,
            $this->jwtKey = 'my-super-duper-secret-key',
            $this->sessionDuration = 7200,
            $this->sessionRefreshAfter = 0.5
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(JwtAuthenticationService::class);
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
        $user->getEmail()->willReturn(EmailAddressValue::get($userMail));
        $user->getPassword()->willReturn(password_hash(
            $password,
            $this->passwordAlgorithm, $this->passwordOptions
        ));

        $this->userRepository->getUserByEmail(new Argument\Token\TypeToken(EmailAddressValue::class))
            ->willReturn($user);

        $this->login($userMail, $password)->shouldBeString();
    }

    public function it_fails_login_on_invalid_emailaddress()
    {
        $userEmail = 'not an e-mailaddress';
        $this->shouldThrow(AuthenticationFailedException::class)->duringLogin($userEmail, 'pass');
    }

    public function it_fails_when_user_is_not_found()
    {
        $userMail = 'user@test.dev';
        $this->userRepository->getUserByEmail(new Argument\Token\TypeToken(EmailAddressValue::class))
            ->willThrow(new \OutOfBoundsException());
        $this->shouldThrow(AuthenticationFailedException::class)->duringLogin($userMail, 'pass');
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

        $this->login($userMail, $password)->shouldBeString();
    }
}
