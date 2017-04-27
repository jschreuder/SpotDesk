<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationServiceInterface;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class ChangePasswordController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    /** @var  UserRepository */
    private $userRepository;

    /** @var  AuthenticationServiceInterface */
    private $authenticationService;

    public function __construct(UserRepository $userRepository, AuthenticationServiceInterface $authenticationService)
    {
        $this->userRepository = $userRepository;
        $this->authenticationService = $authenticationService;
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $filter = new Filter();
        $filter->value('old_password')->string();
        $filter->value('new_password')->string();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('old_password')->string()->lengthBetween(1, null);
        $validator->required('new_password')->string()->lengthBetween(12, null);

        $validationResult = $validator->validate((array) $request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
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
