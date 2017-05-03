<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use jschreuder\SpotDesk\Entity\GuestUser;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationServiceInterface;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

final class AuthenticationMiddleware implements MiddlewareInterface
{
    /** @var  AuthenticationServiceInterface */
    private $authenticationService;

    /** @var  UserRepository */
    private $userRepository;

    public function __construct(AuthenticationServiceInterface $authenticationService, UserRepository $userRepository)
    {
        $this->authenticationService = $authenticationService;
        $this->userRepository = $userRepository;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        // Respond to login attempt
        if ($request->getMethod() === 'POST' && trim($request->getUri()->getPath(), '/') === 'login') {
            $body = (array) $request->getParsedBody();
            return $this->authenticationService->login($body['user'] ?? '', $body['pass'] ?? '');
        }

        // Create user object from session or generate guest user
        $session = $this->authenticationService->getSession($request);
        try {
            $userEmail = EmailAddressValue::get(strval($session->get('user')));
            $user = $this->userRepository->getUserByEmail($userEmail);
            if (!$user->isActive()) {
                throw new \OutOfBoundsException('Inactive user');
            }
        } catch (\DomainException | \OutOfBoundsException $exception) {
            $user = new GuestUser();
        }

        // Process request with session and user attributes added
        $response = $delegate->process(
            $request->withAttribute('session', $session)->withAttribute('user', $user)
        );
        // Attaches session refresh if necessary
        return $this->authenticationService->attachSession($response, $session);
    }
}
