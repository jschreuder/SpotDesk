<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationService;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\RoleInterface;

class AuthenticationServiceSpec extends ObjectBehavior
{
    /** @var  UserRepository */
    private $userRepository;

    /** @var  Rbac */
    private $rbac;

    /** @var  int */
    private $passwordAlgorithm;

    /** @var  array */
    private $passwordOptions;

    public function let(UserRepository $userRepository, Rbac $rbac) : void
    {
        $this->beConstructedWith(
            $this->userRepository = $userRepository,
            $this->rbac = $rbac,
            $this->passwordAlgorithm = PASSWORD_BCRYPT,
            $this->passwordOptions = ['cost' => 8]
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(AuthenticationService::class);
    }

    public function it_can_create_a_user(RoleInterface $role) : void
    {
        $userMail = 'user@test.dev';
        $displayName = 'Username';
        $password = 'my-secret';
        $roleName = 'user';

        $this->rbac->getRole($roleName)->willReturn($role);
        $this->userRepository->createUser(new Argument\Token\TypeToken(User::class));

        $this->createUser($userMail, $displayName, $password, $roleName);
    }

    public function it_can_fetch_a_user(User $user) : void
    {
        $emailAddress = 'user@test.dev';
        $this->userRepository->getUserByEmail(EmailAddressValue::get($emailAddress))->willReturn($user);
        $this->fetchUser($emailAddress)->shouldReturn($user);
    }

    public function it_can_change_a_user_password(User $user)
    {
        $this->userRepository->updatePassword($user, new Argument\Token\TypeToken('string'))->shouldBeCalled();
        $this->changePassword($user, 'new-password');
    }

    public function it_can_verify_a_user_password(User $user)
    {
        $password = 'password';
        $user->getPassword()->willReturn(password_hash($password, PASSWORD_DEFAULT));
        $this->checkPassword($user, $password)->shouldBe(true);
        $this->checkPassword($user, 'nope')->shouldBe(false);
    }

    public function it_can_login(User $user, SessionInterface $session) : void
    {
        $userMail = 'user@test.dev';
        $password = 'my-secret';
        $user->getEmail()->willReturn(EmailAddressValue::get($userMail));
        $user->getPassword()->willReturn(password_hash(
            $password,
            $this->passwordAlgorithm, $this->passwordOptions
        ));
        $user->isActive()->willReturn(true);

        $this->userRepository->getUserByEmail(new Argument\Token\TypeToken(EmailAddressValue::class))
            ->willReturn($user);

        $session->set('user', $userMail);

        $this->login($userMail, $password, $session)->shouldBe(true);
    }

    public function it_fails_login_on_invalid_emailaddress(SessionInterface $session) : void
    {
        $userEmail = 'not an e-mailaddress';
        $this->login($userEmail, 'pass', $session)->shouldBe(false);
    }

    public function it_fails_when_user_is_not_found(SessionInterface $session) : void
    {
        $userMail = 'user@test.dev';
        $this->userRepository->getUserByEmail(new Argument\Token\TypeToken(EmailAddressValue::class))
            ->willThrow(new \OutOfBoundsException());
        $this->login($userMail, 'pass', $session)->shouldBe(false);
    }

    public function it_fails_login_on_disabled_user(User $user, SessionInterface $session) : void
    {
        $userMail = 'user@test.dev';
        $password = 'my-secret';
        $user->getEmail()->willReturn(EmailAddressValue::get($userMail));
        $user->isActive()->willReturn(false);

        $this->userRepository->getUserByEmail(new Argument\Token\TypeToken(EmailAddressValue::class))
            ->willReturn($user);

        $this->login($userMail, $password, $session)->shouldBe(false);
    }

    public function it_fails_login_on_incorrect_password(User $user, SessionInterface $session) : void
    {
        $userMail = 'user@test.dev';
        $password = 'my-secret';
        $user->getEmail()->willReturn(EmailAddressValue::get($userMail));
        $user->getPassword()->willReturn(password_hash(
            $password,
            $this->passwordAlgorithm, $this->passwordOptions
        ));
        $user->isActive()->willReturn(true);

        $this->userRepository->getUserByEmail(new Argument\Token\TypeToken(EmailAddressValue::class))
            ->willReturn($user);

        $this->login($userMail, 'nope', $session)->shouldBe(false);
    }

    public function it_regenerates_password_when_necessary(User $user, SessionInterface $session) : void
    {
        $userMail = 'user@test.dev';
        $password = 'my-secret';
        $user->getEmail()->willReturn(EmailAddressValue::get($userMail));
        $user->getPassword()->willReturn(password_hash(
            $password,
            $this->passwordAlgorithm, ['cost' => 5]
        ));
        $user->isActive()->willReturn(true);

        $this->userRepository->getUserByEmail(new Argument\Token\TypeToken(EmailAddressValue::class))
            ->willReturn($user);
        $this->userRepository->updatePassword($user, new Argument\Token\TypeToken('string'))
            ->shouldBeCalled();

        $session->set('user', $userMail);

        $this->login($userMail, $password, $session)->shouldBe(true);
    }
}
