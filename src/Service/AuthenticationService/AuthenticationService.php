<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

final class AuthenticationService implements AuthenticationServiceInterface
{
    const AUTHORIZATION_HEADER = 'SpotDesk-Authorization';

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

    public function __construct(
        UserRepository $userRepository,
        int $passwordAlgorithm,
        array $passwordOptions,
        SessionStorageInterface $sessionStorage,
        int $sessionDuration = 3600,
        float $sessionRefreshAfter = .5
    ) {
        $this->userRepository = $userRepository;
        $this->passwordAlgorithm = $passwordAlgorithm;
        $this->passwordOptions = $passwordOptions;
        $this->sessionStorage = $sessionStorage;
        $this->sessionDuration = $sessionDuration;
        $this->sessionRefreshAfter = $sessionRefreshAfter;
    }

    public function createUser(string $email, string $displayName, string $password) : User
    {
        $user = new User(
            EmailAddressValue::get($email),
            $displayName,
            password_hash($password, $this->passwordAlgorithm, $this->passwordOptions),
            null
        );
        $this->userRepository->createUser($user);
        return $user;
    }

    public function login(string $email, string $password) : ResponseInterface
    {
        try {
            $user = $this->userRepository->getUserByEmail(EmailAddressValue::get($email));
        } catch (\OutOfBoundsException | \DomainException $exception) {
            return new JsonResponse(['message' => 'Login failed'], 401);
        }

        if (!password_verify($password, $user->getPassword())) {
            return new JsonResponse(['message' => 'Login failed'], 401);
        }

        if (password_needs_rehash($user->getPassword(), $this->passwordAlgorithm, $this->passwordOptions)) {
            $this->userRepository->updatePassword(
                $user,
                password_hash($password, $this->passwordAlgorithm, $this->passwordOptions)
            );
        }

        $sessionData = $this->sessionStorage->create($user->getEmail()->toString(), $this->sessionDuration);
        return new JsonResponse(['message' => 'Login succeeded'], 201, [
            self::AUTHORIZATION_HEADER => $sessionData,
        ]);
    }

    public function getSession(ServerRequestInterface $request) : ?SessionInterface
    {
        $sessionId = $request->getHeaderLine(self::AUTHORIZATION_HEADER);
        if (!$sessionId) {
            return null;
        }

        return $this->sessionStorage->load($sessionId);
    }

    public function attachSession(ResponseInterface $response, SessionInterface $session) : ResponseInterface
    {
        $refreshTimeframe = intval($this->sessionDuration * $this->sessionRefreshAfter);
        if (!$this->sessionStorage->needsRefresh($session, $refreshTimeframe)) {
            return $response;
        };

        return $response->withHeader(
            self::AUTHORIZATION_HEADER,
            $this->sessionStorage->create($session->get('user'), $this->sessionDuration)
        );
    }
}
