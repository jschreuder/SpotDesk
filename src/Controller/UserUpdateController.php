<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Particle\Filter\Filter;
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Permissions\Rbac\Rbac;

class UserUpdateController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    /** @var  UserRepository */
    private $userRepository;

    /** @var  Rbac */
    private $rbac;

    public function __construct(UserRepository $userRepository, Rbac $rbac)
    {
        $this->userRepository = $userRepository;
        $this->rbac = $rbac;
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['email'] = base64_decode($request->getAttribute('email'));

        $filter = new Filter();
        $filter->value('active')->bool();
        $filter->value('display_name')->string()->trim();
        $filter->value('role')->string();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('email')->string()->email();
        $validator->required('active')->bool();
        $validator->required('display_name')->string()->lengthBetween(2, 63);
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
        $user = $this->userRepository->getUserByEmail(EmailAddressValue::get($body['email']));

        $user->setDisplayName($body['display_name']);
        $user->setRole($this->rbac->getRole($body['role']));
        $user->setActive($body['active']);
        $this->userRepository->updateUser($user);

        return new JsonResponse([
            'user' => [
                'email' => $user->getEmail(),
                'display_name' => $user->getDisplayName(),
                'role' => $user->getRole()->getName(),
                'active' => $user->isActive(),
            ]
        ], 200);
    }
}
