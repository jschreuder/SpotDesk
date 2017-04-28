<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class GetUserController implements ControllerInterface
{
    /** @var  UserRepository */
    private $userRepository;

    /** @var  DepartmentRepository */
    private $departmentRepository;

    public function __construct(UserRepository $userRepository, DepartmentRepository $departmentRepository)
    {
        $this->userRepository = $userRepository;
        $this->departmentRepository = $departmentRepository;
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
