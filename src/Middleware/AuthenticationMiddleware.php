<?php

namespace jschreuder\SpotDesk\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationFailedException;
use jschreuder\SpotDesk\Service\AuthenticationServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class AuthenticationMiddleware implements MiddlewareInterface
{
    const AUTHORIZATION_HEADER = 'SpotDesk-Authorization';

    /** @var  AuthenticationServiceInterface */
    private $authenticationService;

    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        // Respond to login attempt
        if ($request->getMethod() === 'POST' && trim($request->getUri()->getPath(), '/') === 'login') {
            return $this->login($request);
        }

        // Check login status
        $sessionId = $request->getHeader(self::AUTHORIZATION_HEADER);
        if (!$this->authenticationService->checkLogin($sessionId)) {
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        // Check if session needs refresh, will add new session ID to response when it does
        $response = $delegate->process($request);
        if ($newSessionId = $this->authenticationService->refreshSession($sessionId)) {
            $response = $response->withHeader(self::AUTHORIZATION_HEADER, $newSessionId);
        }
        return $response;
    }

    private function login(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $body = $request->getParsedBody();
            $sessionId = $this->authenticationService->login($body['user'], $body['pass']);
        } catch (AuthenticationFailedException $exception) {
            return new JsonResponse(['message' => 'Login failed'], 401);
        }

        return new JsonResponse(['message' => 'Login successful'], 201, [
            self::AUTHORIZATION_HEADER => $sessionId,
        ]);
    }
}
