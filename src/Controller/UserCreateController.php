<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationServiceInterface;
use jschreuder\SpotDesk\Service\FilterService;
use jschreuder\SpotDesk\Service\ValidationService;
use jschreuder\SpotDesk\Service\Validator\RoleValidator;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Filter\StringTrim;
use Laminas\Permissions\Rbac\Rbac;
use Laminas\Validator\EmailAddress as EmailAddressValidator;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorChain;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserCreateController implements ControllerInterface, RequestValidatorInterface, RequestFilterInterface
{
    public function __construct(
        private AuthenticationServiceInterface $authenticationService, 
        private Rbac $rbac
    )
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        return FilterService::filter($request, [
            'email' => new StringTrim(),
            'display_name' => new StringTrim(),
            'password' => strval(...),
            'role' => strval(...),
        ]);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        ValidationService::validate($request, [
            'email' => (new ValidatorChain())
                ->attach(new EmailAddressValidator())
                ->attach(new StringLength(['min' => 6, 'max' => 123])),
            'display_name' => new StringLength(['min' => 2, 'max' => 63]),
            'password' => new StringLength(['min' => 12]),
            'role' => new RoleValidator(['rbac' => $this->rbac]),
        ], ['display_name']);
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();

        $user = $this->authenticationService->createUser(
            $body['email'],
            $body['display_name'],
            $body['password'],
            $body['role']
        );

        return new JsonResponse([
            'user' => [
                'email' => $user->getEmail(),
                'display_name' => $user->getDisplayName(),
                'active' => $user->isActive(),
                'role' => $user->getRole()->getName(),
            ]
        ], 201);
    }
}
