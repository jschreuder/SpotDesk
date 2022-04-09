<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\AuthenticationService;

use InvalidArgumentException;
use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Laminas\Permissions\Rbac\Rbac;
use OutOfBoundsException;

final class AuthenticationService implements AuthenticationServiceInterface
{
    const AUTHORIZATION_HEADER = 'SpotDesk-Authorization';

    public function __construct(
        private UserRepository $userRepository,
        private Rbac $rbac,
        private string|int $passwordAlgorithm,
        private array $passwordOptions
    )
    {
    }

    private function hashPassword(string $password) : string
    {
        return password_hash($password, $this->passwordAlgorithm, $this->passwordOptions);
    }

    public function createUser(string $email, string $displayName, string $password, string $roleName) : User
    {
        $user = new User(
            EmailAddressValue::get($email),
            $displayName,
            $this->hashPassword($password),
            $this->rbac->getRole($roleName)
        );
        $this->userRepository->createUser($user);
        return $user;
    }

    public function fetchUser(string $emailAddress) : User
    {
        return $this->userRepository->getUserByEmail(EmailAddressValue::get($emailAddress));
    }

    public function changePassword(User $user, string $newPassword) : void
    {
        $this->userRepository->updatePassword($user, $this->hashPassword($newPassword));
    }

    public function checkPassword(User $user, string $password) : bool
    {
        return password_verify($password, $user->getPassword());
    }

    public function login(string $email, string $password, SessionInterface $session) : bool
    {
        // Attempt to fetch user from database, just return on failure
        try {
            $user = $this->userRepository->getUserByEmail(EmailAddressValue::get($email));
        } catch (OutOfBoundsException | InvalidArgumentException $exception) {
            return false;
        }

        // Check if user is active and if given password is valid, just return otherwise
        if (!$user->isActive() || !$this->checkPassword($user, $password)) {
            return false;
        }

        // Force password rehash when algorithm or options have been changed
        if (password_needs_rehash($user->getPassword(), $this->passwordAlgorithm, $this->passwordOptions)) {
            $this->userRepository->updatePassword(
                $user,
                password_hash($password, $this->passwordAlgorithm, $this->passwordOptions)
            );
        }

        // All passes, add data to session
        $session->set('user', $user->getEmail()->toString());
        return true;
    }
}
