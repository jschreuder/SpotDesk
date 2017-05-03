<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationServiceInterface;
use Particle\Filter\Filter;
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Permissions\Rbac\Rbac;

class UserCreateController implements ControllerInterface, RequestValidatorInterface, RequestFilterInterface
{
    /** @var  AuthenticationServiceInterface */
    private $authenticationService;

    /** @var  Rbac */
    private $rbac;

    public function __construct(AuthenticationServiceInterface $authenticationService, Rbac $rbac)
    {
        $this->authenticationService = $authenticationService;
        $this->rbac = $rbac;
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $filter = new Filter();
        $filter->value('email')->string()->trim();
        $filter->value('display_name')->string()->trim();
        $filter->value('password')->string();
        $filter->value('role')->string();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('email')->string()->email()->lengthBetween(6, 123);
        $validator->optional('display_name')->string()->lengthBetween(2, 63);
        $validator->required('password')->string()->lengthBetween(12, null);
        $validator->required('role')->string()->callback(function ($value) {
            if (!$this->rbac->hasRole($value)) {
                throw new InvalidValueException('Unknown role: ' . $value, 'unknown_role');
            }
            return true;
        });

        $validationResult = $validator->validate((array) $request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
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
