<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use jschreuder\SpotDesk\Middleware\SecurityHeadersMiddleware;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SecurityHeadersMiddlewareSpec extends ObjectBehavior
{
    /** @var  string */
    private $siteUrl;

    public function let()
    {
        $this->beConstructedWith(
            $this->siteUrl = 'http://localhost'
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SecurityHeadersMiddleware::class);
    }

    public function it_attaches_security_headers_to_response(
        ServerRequestInterface $request,
        DelegateInterface $delegate,
        ResponseInterface $response1,
        ResponseInterface $response2,
        ResponseInterface $response3,
        ResponseInterface $response4,
        ResponseInterface $response5
    )
    {
        $delegate->process($request)->willReturn($response1);

        $response1->withHeader('X-Frame-Options', new Argument\Token\TypeToken('string'))
            ->willReturn($response2);
        $response2->withHeader('X-XSS-Protection', new Argument\Token\TypeToken('string'))
            ->willReturn($response3);
        $response3->withHeader('X-Content-Type-Options', new Argument\Token\TypeToken('string'))
            ->willReturn($response4);
        $response4->withHeader('Content-Security-Policy', new Argument\Token\TypeToken('string'))
            ->willReturn($response5);

        $this->process($request, $delegate)->shouldReturn($response5);
    }

    public function it_also_attaches_hsts_header_on_https_connection(
        ServerRequestInterface $request,
        DelegateInterface $delegate,
        ResponseInterface $response1,
        ResponseInterface $response2,
        ResponseInterface $response3,
        ResponseInterface $response4,
        ResponseInterface $response5,
        ResponseInterface $response6
    )
    {
        $this->beConstructedWith('https://secure.site');

        $delegate->process($request)->willReturn($response1);

        $response1->withHeader('X-Frame-Options', new Argument\Token\TypeToken('string'))
            ->willReturn($response2);
        $response2->withHeader('X-XSS-Protection', new Argument\Token\TypeToken('string'))
            ->willReturn($response3);
        $response3->withHeader('X-Content-Type-Options', new Argument\Token\TypeToken('string'))
            ->willReturn($response4);
        $response4->withHeader('Content-Security-Policy', new Argument\Token\TypeToken('string'))
            ->willReturn($response5);
        $response5->withHeader('Strict-Transport-Security', new Argument\Token\TypeToken('string'))
            ->willReturn($response6);

        $this->process($request, $delegate)->shouldReturn($response6);
    }
}
