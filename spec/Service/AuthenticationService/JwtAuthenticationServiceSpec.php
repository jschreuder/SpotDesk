<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\AuthenticationService\JwtAuthenticationService;
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
            $this->passwordOptions = [],
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
}
