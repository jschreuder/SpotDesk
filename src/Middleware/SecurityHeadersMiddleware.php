<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function __construct(private string $siteUrl)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler) : ResponseInterface
    {
        $response = $requestHandler->handle($request);

        // Add a bunch of security related headers to the response
        $response = $response
            ->withHeader('X-Frame-Options', 'DENY')
            ->withHeader('X-XSS-Protection', '1; mode=block')
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader(
                'Content-Security-Policy',
                'default-src \'self\'; script-src \'self\' \'sha256-no9xHsC3XWcOv0jYdPiPE8EclOgXfoMbP647ArOoT1E=\';'
                . 'img-src \'self\'  data:; style-src \'self\' \'unsafe-inline\'; '
                . 'form-action \'self\'; font-src \'self\'; child-src \'self\'; object-src \'none\''
            );

        // Determine STS based on site URL
        if (strpos($this->siteUrl, 'https://') === 0) {
            $response = $response->withHeader(
                'Strict-Transport-Security',
                'max-age=15552000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
