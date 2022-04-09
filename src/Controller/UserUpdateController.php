<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use InvalidArgumentException;
use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\FilterService;
use jschreuder\SpotDesk\Service\ValidationService;
use jschreuder\SpotDesk\Service\Validator\RoleValidator;
use jschreuder\SpotDesk\Service\Validator\TypeValidator;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Filter\StringTrim;
use Laminas\Permissions\Rbac\Rbac;
use Laminas\Validator\EmailAddress as EmailAddressValidator;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorChain;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserUpdateController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(
        private UserRepository $userRepository, 
        private Rbac $rbac
    )
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['email'] = base64_decode($request->getAttribute('email'));
        return FilterService::filter($request->withParsedBody($body), [
            'active' => boolval(...),
            'display_name' => new StringTrim(),
            'role' => strval(...),
        ]);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        ValidationService::validate($request, [
            'email' => (new ValidatorChain())
                ->attach(new EmailAddressValidator())
                ->attach(new StringLength(['min' => 6, 'max' => 123])),
            'active' => new TypeValidator(['type' => boolean::class]),
            'display_name' => new StringLength(['min' => 2, 'max' => 63]),
            'role' => new RoleValidator(['rbac' => $this->rbac]),
        ]);
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
