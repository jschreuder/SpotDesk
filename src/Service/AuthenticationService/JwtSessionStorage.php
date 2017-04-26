<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\Middle\Session\Session;
use jschreuder\Middle\Session\SessionInterface;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Claim;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\ValidationData;

class JwtSessionStorage implements SessionStorageInterface
{
    /** @var  string */
    private $siteUrl;

    /** @var  Signer */
    private $jwtSigner;

    /** @var  mixed */
    private $jwtKey;

    public function __construct(string $siteUrl, Signer $jwtSigner, $jwtKey)
    {
        $this->siteUrl = $siteUrl;
        $this->jwtSigner = $jwtSigner;
        $this->jwtKey = $jwtKey;
    }

    /**
     * Creates a JWT session and returns it as a string
     */
    public function create(string $userId, int $sessionDuration): string
    {
        $token = (new Builder())->setIssuer($this->siteUrl)
            ->setAudience($this->siteUrl)
            ->setId(sha1($this->siteUrl . $userId), true)
            ->setIssuedAt(time())
            ->setExpiration(time() + $sessionDuration)
            ->set('user', $userId)
            ->sign($this->jwtSigner, $this->jwtKey)
            ->getToken();
        return strval($token);
    }

    /**
     * Parses the given JWT and returns any verified & validated claims, or
     * null when nothing did.
     */
    public function load(string $sessionData): ?SessionInterface
    {
        // Attempt to parse the Token, fail when it won't parse
        try {
            $token = (new Parser())->parse($sessionData);
        } catch (\Throwable $exception) {
            return null;
        }

        // Check signature
        if (!$token->verify($this->jwtSigner, $this->jwtKey)) {
            return null;
        }

        // Check claims
        $validation = new ValidationData();
        $validation->setIssuer($this->siteUrl);
        $validation->setAudience($this->siteUrl);
        if (!$token->validate($validation)) {
            return null;
        }

        $data = $token->getClaims();
        return new Session(array_map(function (Claim $claim) {
            return $claim->getValue();
        }, $data));
    }

    public function needsRefresh(SessionInterface $session, int $refreshTimeframe) : bool
    {
        $expires = $session->get('expires');
        return $expires - time() < $refreshTimeframe;
    }
}
