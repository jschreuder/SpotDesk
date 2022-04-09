<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\ValidationService;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Validator\EmailAddress as EmailAddressValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserGetOneController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(
        private UserRepository $userRepository, 
        private DepartmentRepository $departmentRepository
    )
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
        $email = EmailAddressValue::get(base64_decode($request->getAttribute('email')));
        $user = $this->userRepository->getUserByEmail($email);
        $userDepartments = $this->departmentRepository->getDepartmentsForUser($user);

        return new JsonResponse([
            'user' => [
                'email' => $user->getEmail()->toString(),
                'display_name' => $user->getDisplayName(),
                'role' => $user->getRole()->getName(),
                'active' => $user->isActive(),
            ],
            'departments' => array_map(function (Department $department) : array {
                return [
                    'department_id' => $department->getId()->toString(),
                    'name' => $department->getName(),
                    'parent_id' => $department->getParent() ? $department->getParent()->getId()->toString() : null,
                    'parent_name' => $department->getParent() ? $department->getParent()->getName() : null,
                ];
            }, $userDepartments->toArray()),
        ], 200);
    }
}
