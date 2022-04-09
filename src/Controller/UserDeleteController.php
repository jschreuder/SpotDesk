<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\ValidationService;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Validator\EmailAddress as EmailAddressValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserDeleteController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['email'] = base64_decode($request->getAttribute('email'));
        return $request->withParsedBody($body);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        ValidationService::validate($request, [
            'email' => new EmailAddressValidator(),
        ]);
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();
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
