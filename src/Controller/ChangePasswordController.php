<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationServiceInterface;
use jschreuder\SpotDesk\Service\FilterService;
use jschreuder\SpotDesk\Service\ValidationService;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Validator\StringLength;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ChangePasswordController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(
        private UserRepository $userRepository, 
        private AuthenticationServiceInterface $authenticationService
    )
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        return FilterService::filter($request, [
            'old_password' => strval(...), 
            'new_password' => strval(...),
        ]);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        ValidationService::validate($request, [
            'old_password' => new StringLength(['min' => 1]),
            'new_password' => new StringLength(['min' => 12]),
        ]);
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $session = $request->getAttribute('session');

        $user = $this->userRepository->getUserByEmail(EmailAddressValue::get($session->get('user')));
        if (!$this->authenticationService->checkPassword($user, $body['old_password'])) {
            return new JsonResponse(['message' => 'Invalid old password'], 400);
        }

        $this->authenticationService->changePassword($user, $body['new_password']);
        return new JsonResponse(['message' => 'Password changed'], 200);
    }
}
