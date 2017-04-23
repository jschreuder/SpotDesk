<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SecurityHeadersMiddleware implements MiddlewareInterface
{
    /** @var  string */
    private $siteUrl;

    public function __construct($siteUrl)
    {
        $this->siteUrl = $siteUrl;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $response = $delegate->process($request);

        // Add a bunch of security related headers to the response
        $response = $response
            ->withAddedHeader('X-Frame-Options', 'DENY')
            ->withAddedHeader('X-XSS-Protection', '1; mode=block')
            ->withAddedHeader('X-Content-Type-Options', 'nosniff')
            ->withAddedHeader(
                'Content-Security-Policy',
                'default-src \'self\'; script-src \'self\'; img-src \'self\'; style-src \'self\'; '
                . 'form-action \'self\'; font-src \'self\'; child-src \'none\'; object-src \'none\''
            );

        // Determine STS based on site URL
        if (strpos($this->siteUrl, 'https://') === 0) {
            $response = $response->withAddedHeader(
                'Strict-Transport-Security',
                'max-age=15552000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
