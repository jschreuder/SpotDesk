<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class DeleteUserController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    /** @var  UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function filterRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $body = (array)$request->getParsedBody();
        $body['email'] = base64_decode($request->getAttribute('email'));
        $filter = new Filter();
        $filter->value('email')->string()->trim();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request): void
    {
        $validator = new Validator();
        $validator->required('email')->email();

        $validationResult = $validator->validate((array)$request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
    }

    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $body = (array)$request->getParsedBody();
        $email = EmailAddressValue::get($body['email']);

        $user = $this->userRepository->getUserByEmail($email);
        $this->userRepository->deleteUser($user);

        return new JsonResponse([
            'user' => [
                'email' => $user->getEmail()->toString(),
            ]
        ], 200);
    }
}
