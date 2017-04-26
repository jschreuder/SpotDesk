<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

final class AuthenticationMiddleware implements MiddlewareInterface
{
    /** @var  AuthenticationServiceInterface */
    private $authenticationService;

    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        // Allow site template without auth
        if ($request->getMethod() === 'GET' && trim($request->getUri()->getPath(), '/') === '') {
            return $delegate->process($request);
        }
        // Respond to login attempt
        if ($request->getMethod() === 'POST' && trim($request->getUri()->getPath(), '/') === 'login') {
            $body = (array) $request->getParsedBody();
            return $this->authenticationService->login($body['user'] ?? '', $body['pass'] ?? '');
        }

        // Check login status
        $session = $this->authenticationService->getSession($request);
        if (is_null($session)) {
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        // Check if session needs refresh, will add new session ID to response when it does
        $response = $delegate->process($request->withAttribute('session', $session));
        return $this->authenticationService->attachSession($response, $session);
    }
}
