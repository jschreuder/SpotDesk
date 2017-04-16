<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\AuthenticationServiceInterface;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;

class JwtAuthenticationService implements AuthenticationServiceInterface
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

    /** @var  int */
    private $sessionRefreshAfter;

    public function __construct(
        UserRepository $userRepository,
        int $passwordAlgorithm,
        array $passwordOptions,
        string $siteUrl,
        Signer $jwtSigner,
        $jwtKey,
        int $sessionDuration = 3600,
        int $sessionRefreshAfter = 1800
    )
    {
        $this->userRepository = $userRepository;
        $this->passwordAlgorithm = $passwordAlgorithm;
        $this->passwordOptions = $passwordOptions;
        $this->siteUrl = $siteUrl;
        $this->jwtSigner = $jwtSigner;
        $this->jwtKey = $jwtKey;
        $this->sessionDuration = $sessionDuration;
        $this->sessionRefreshAfter = $sessionRefreshAfter;
    }

    public function login($email, $password): string
    {
        try {
            $user = $this->userRepository->getUserByEmail($email);
        } catch (\OutOfBoundsException $exception) {
            throw new AuthenticationFailedException('Unknown user');
        }

        if (!password_verify($password, $user->getPassword())) {
            throw new AuthenticationFailedException('Invalid password');
        }

        if (password_needs_rehash($user->getPassword(), $this->passwordAlgorithm, $this->passwordOptions)) {
            $this->userRepository->updatePassword(
                $user,
                password_hash($password, $this->passwordAlgorithm, $this->passwordOptions)
            );
        }

        return strval($this->createSessionJwt($user->getEmail()->toString()));
    }

    private function createSessionJwt(string $userId): Token
    {
        return (new Builder())->setIssuer($this->siteUrl)
            ->setAudience($this->siteUrl)
            ->setId(sha1($this->siteUrl . $userId), true)
            ->setIssuedAt(time())
            ->setExpiration(time() + $this->sessionDuration)
            ->set('user', $userId)
            ->sign($this->jwtSigner, $this->jwtKey)
            ->getToken();
    }

    public function checkLogin(string $sessionId): bool
    {
        $jwt = (new Parser())->parse($sessionId);

        // Check signature
        if (!$jwt->verify($this->jwtSigner, $this->jwtKey)) {
            return false;
        }

        // Check claims
        $validation = new ValidationData();
        $validation->setIssuer($this->siteUrl);
        $validation->setAudience($this->siteUrl);
        if (!$jwt->validate($validation)) {
            return false;
        }

        return true;
    }

    public function refreshSession($sessionId): ?string
    {
        $jwt = (new Parser())->parse($sessionId);
        $issuedAt = $jwt->getClaim('iat');

        // Check if refresh is need, return null otherwise
        if ($issuedAt + $this->sessionRefreshAfter > time()) {
            return null;
        }

        // Return fresh token
        return strval($this->createSessionJwt($jwt->getClaim('user')));
    }
}
