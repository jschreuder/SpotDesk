<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Service\AuthenticationService;

use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationFailedException;
use PhpSpec\ObjectBehavior;

class AuthenticationFailedExceptionSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(AuthenticationFailedException::class);
    }
}
