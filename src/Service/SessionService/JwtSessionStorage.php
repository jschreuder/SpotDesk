<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\SessionService;

use DateTimeImmutable;
use jschreuder\Middle\Session\Session;
use jschreuder\Middle\Session\SessionInterface;
use Lcobucci\JWT\Configuration as JwtConfiguration;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validator;

final class JwtSessionStorage implements SessionStorageInterface
{
    public function __construct(
        private string $siteUrl,
        private JwtConfiguration $jwtConfiguration
    )
    {
    }

    private function createTokenBuilder(int $sessionDuration) : Builder
    {
        return $this->jwtConfiguration->builder()
            ->issuedBy($this->siteUrl)
            ->permittedFor($this->siteUrl)
            ->issuedAt(new DateTimeImmutable())
            ->expiresAt(new DateTimeImmutable('+'.$sessionDuration.' seconds'));
    }

    private function parseToken(string $sessionData) : UnencryptedToken
    {
        return $this->jwtConfiguration->parser()->parse($sessionData);
    }

    private function getValidator() : Validator
    {
        return $this->jwtConfiguration->validator();
    }

    /**
     * Creates a JWT session and returns it as a string
     */
    public function create(array $sessionData, int $sessionDuration) : string
    {
        $tokenBuilder = $this->createTokenBuilder($sessionDuration);
        foreach ($sessionData as $key => $value) {
            $tokenBuilder->withClaim($key, $value);
        }

        return $tokenBuilder->getToken(
            $this->jwtConfiguration->signer(), 
            $this->jwtConfiguration->signingKey()
        )->toString();
    }

    /**
     * Parses the given JWT and returns any verified & validated claims, or
     * null when nothing did.
     */
    public function load(string $sessionData) : SessionInterface
    {
        // Attempt to parse the Token, return empty session when that fails
        try {
            $token = $this->parseToken($sessionData);
        } catch (\Throwable $exception) {
            return new Session();
        }

        // Check claims, reject & return empty session when that fails
        $validator = $this->getValidator();
        $validates = $validator->validate(
            $token,
            new SignedWith($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey()),
            new IssuedBy($this->siteUrl),
            new PermittedFor($this->siteUrl),
        );
        if (!$validates) {
            return new Session();
        }

        return new Session($token->claims()->all());
    }

    public function needsRefresh(SessionInterface $session, int $refreshTimeframe) : bool
    {
        $expires = $session->get('expires') - time();
        return $session->hasChanged() || ($expires < $refreshTimeframe);
    }
}
