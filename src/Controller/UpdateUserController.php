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

class UpdateUserController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    /** @var  UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['email'] = base64_decode($request->getAttribute('email'));

        $filter = new Filter();
        $filter->value('email')->string()->trim();
        $filter->value('active')->bool();
        $filter->value('display_name')->string()->trim();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('email')->string()->email();
        $validator->required('active')->bool();
        $validator->required('display_name')->string()->lengthBetween(2, 63);

        $validationResult = $validator->validate((array) $request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $user = $this->userRepository->getUserByEmail(EmailAddressValue::get($body['email']));

        $user->setDisplayName($body['display_name']);
        $user->setActive($body['active']);
        $this->userRepository->updateUser($user);

        return new JsonResponse([
            'user' => [
                'email' => $user->getEmail(),
                'display_name' => $user->getDisplayName(),
                'active' => $user->isActive(),
            ]
        ], 200);
    }
}
