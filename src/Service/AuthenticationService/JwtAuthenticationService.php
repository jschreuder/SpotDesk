<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\Middle\Session\Session;
use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\AuthenticationServiceInterface;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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

    /** @var  float between 0 and 1, after how much of the duration a session should be refreshed */
    private $sessionRefreshAfter;

    public function __construct(
        UserRepository $userRepository,
        int $passwordAlgorithm,
        array $passwordOptions,
        string $siteUrl,
        Signer $jwtSigner,
        $jwtKey,
        int $sessionDuration = 3600,
        float $sessionRefreshAfter = .5
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

    public function createUser(string $email, string $displayName, string $password): User
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

    public function login(string $email, string $password): string
    {
        try {
            $user = $this->userRepository->getUserByEmail(EmailAddressValue::get($email));
        } catch (\OutOfBoundsException|\DomainException $exception) {
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

    public function checkLogin(ServerRequestInterface $request, string $authorizationHeader): ?SessionInterface
    {
        $sessionId = $request->getHeaderLine($authorizationHeader);
        if (!$sessionId) {
            return null;
        }

        // Attempt to parse the Token, fail when it won't parse
        try {
            $jwt = (new Parser())->parse($sessionId);
        } catch (\Throwable $exception) {
            return null;
        }

        // Check signature
        if (!$jwt->verify($this->jwtSigner, $this->jwtKey)) {
            return null;
        }

        // Check claims
        $validation = new ValidationData();
        $validation->setIssuer($this->siteUrl);
        $validation->setAudience($this->siteUrl);
        if (!$jwt->validate($validation)) {
            return null;
        }

        $session = new Session([
            'user' => $jwt->getClaim('user'),
            'jwt' => $jwt,
        ]);

        return $session;
    }

    public function refreshSession(
        ResponseInterface $response,
        string $authorizationHeader,
        SessionInterface $session
    ): ResponseInterface
    {
        $jwt = $session->get('jwt');
        $issuedAt = $jwt->getClaim('iat');

        // Check if refresh is need, return null otherwise
        if ($issuedAt + ($this->sessionDuration * $this->sessionRefreshAfter) > time()) {
            return $response;
        }

        // Return fresh token
        return $response->withHeader(
            $authorizationHeader,
            strval($this->createSessionJwt($jwt->getClaim('user')))
        );
    }
}
