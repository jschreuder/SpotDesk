<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationServiceInterface;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class CreateUserController implements ControllerInterface, RequestValidatorInterface, RequestFilterInterface
{
    /** @var  AuthenticationServiceInterface */
    private $authenticationService;

    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array)$request->getParsedBody();
        $filter = new Filter();
        $filter->value('email')->string()->trim();
        $filter->value('display_name')->string()->trim();
        $filter->value('password')->string();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('email')->string()->email()->lengthBetween(6, 123);
        $validator->optional('display_name')->string()->lengthBetween(2, 63);
        $validator->required('password')->string()->lengthBetween(8, null);

        $validationResult = $validator->validate((array)$request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array)$request->getParsedBody();

        $user = $this->authenticationService->createUser(
            $body['email'],
            $body['display_name'],
            $body['password']
        );

        return new JsonResponse([
            'user' => [
                'email' => $user->getEmail(),
                'display_name' => $user->getDisplayName(),
            ]
        ], 201);
    }
}
