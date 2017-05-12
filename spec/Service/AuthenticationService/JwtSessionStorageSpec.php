<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Service\AuthenticationService\JwtSessionStorage;
use Lcobucci\JWT\Signer;
use PhpSpec\ObjectBehavior;

class JwtSessionStorageSpec extends ObjectBehavior
{
    /** @var  string */
    private $siteUrl;

    /** @var  Signer */
    private $jwtSigner;

    /** @var  mixed */
    private $jwtKey;

    public function let() : void
    {
        $this->beConstructedWith(
            $this->siteUrl = 'http://localhost/',
            $this->jwtSigner = new Signer\Hmac\Sha256(),
            $this->jwtKey = 'my-super-duper-secret-key'
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(JwtSessionStorage::class);
    }

    public function it_can_create_a_token_and_load_it_into_a_session() : void
    {
        $user = 'user@id.me';

        $token = $this->create(['user' => $user], 1800);
        $token->shouldBeString();

        $session = $this->load($token);
        $session->shouldBeAnInstanceOf(SessionInterface::class);
        $session->get('user')->shouldBe($user);
    }

    public function it_returns_empty_session_when_loading_bogus_session() : void
    {
        $session = $this->load('bogus');
        $session->shouldBeAnInstanceOf(SessionInterface::class);
        $session->isEmpty()->shouldBe(true);
    }

    public function it_returns_empty_session_on_invalid_signature() : void
    {
        $user = 'user@id.me';

        $token = $this->create(['user' => $user], 1800);
        $token = substr($token->getWrappedObject(), 0, -3);

        $session = $this->load($token);
        $session->shouldBeAnInstanceOf(SessionInterface::class);
        $session->isEmpty()->shouldBe(true);
    }

    // @todo add specs for non-matching claims

    public function it_can_tell_when_session_needs_a_refresh_due_to_changes(SessionInterface $session) : void
    {
        $session->get('expires')->willReturn(time() + 50);
        $session->hasChanged()->willReturn(true);
        $this->needsRefresh($session, 25)->shouldBe(true);
    }

    public function it_can_tell_when_session_does_not_need_a_refresh_due_to_changes(SessionInterface $session) : void
    {
        $session->get('expires')->willReturn(time() + 50);
        $session->hasChanged()->willReturn(false);
        $this->needsRefresh($session, 25)->shouldBe(false);
    }

    public function it_can_tell_when_session_needs_a_refresh_to_prevent_expiration(SessionInterface $session) : void
    {
        // Set expiration of session 25 seconds into the future
        $session->get('expires')->willReturn(time() + 25);
        $session->hasChanged()->willReturn(false);

        // If only requires refresh within the last 5 seconds this should be false
        $this->needsRefresh($session, 5)->shouldBe(false);

        // If it requires refresh within the last 50 seconds, we're there and it returns true
        $this->needsRefresh($session, 50)->shouldBe(true);
    }
}
