<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Middleware;

use InvalidArgumentException;
use jschreuder\Middle\Exception\AuthenticationException;
use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Entity\GuestUser;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use OutOfBoundsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(private AuthenticationServiceInterface $authenticationService)
    {
    }

    /** @throws  AuthenticationException */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler) : ResponseInterface
    {
        /** @var  SessionInterface $session */
        $session = $request->getAttribute('session');

        // Respond to login attempt
        if ($request->getMethod() === 'POST' && trim($request->getUri()->getPath(), '/') === 'login') {
            return $this->login($request, $session);
        }

        // Create user object from session or generate guest user
        try {
            $user = $this->authenticationService->fetchUser(strval($session->get('user')));
            if (!$user->isActive()) {
                throw new AuthenticationException('Inactive user');
            }
        } catch (InvalidArgumentException | OutOfBoundsException $exception) {
            $user = new GuestUser();
        }

        // Process request with authenticated user attribute added
        return $requestHandler->handle(
            $request->withAttribute('user', $user)
        );
    }

    private function login(ServerRequestInterface $request, SessionInterface $session) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $user = $body['user'] ?? '';
        $pass = $body['pass'] ?? '';
        if ($this->authenticationService->login($user, $pass, $session)) {
            return new JsonResponse(['message' => 'Login succeeded'], 201);
        }
        return new JsonResponse(['message' => 'Login failed'], 401);
    }
}
