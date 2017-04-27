<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Service\AuthenticationService\JwtSessionStorage;
use Lcobucci\JWT\Signer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class JwtSessionStorageSpec extends ObjectBehavior
{
    /** @var  string */
    private $siteUrl;

    /** @var  Signer */
    private $jwtSigner;

    /** @var  mixed */
    private $jwtKey;

    public function let()
    {
        $this->beConstructedWith(
            $this->siteUrl = 'http://localhost/',
            $this->jwtSigner = new Signer\Hmac\Sha256(),
            $this->jwtKey = 'my-super-duper-secret-key'
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(JwtSessionStorage::class);
    }

    public function it_can_create_a_token_and_load_it_into_a_session()
    {
        $user = 'user@id.me';

        $token = $this->create($user, 1800);
        $token->shouldBeString();

        $session = $this->load($token);
        $session->shouldBeAnInstanceOf(SessionInterface::class);
        $session->get('user')->shouldBe($user);
    }

    public function it_returns_null_when_loading_bogus_session()
    {
        $this->load('bogus')->shouldBeNull();
    }

    // @todo add specs for invalid signature and non-matching claims

    public function it_can_tell_when_session_needs_a_refresh(SessionInterface $session)
    {
        // Set expiration of session 25 seconds into the future
        $session->get('expires')->willReturn(time() + 25);

        // If only requires refresh within the last 5 seconds this should be false
        $this->needsRefresh($session, 5)->shouldBe(false);

        // If it requires refresh within the last 50 seconds, we're there and it returns true
        $this->needsRefresh($session, 50)->shouldBe(true);
    }
}
