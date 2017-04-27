<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

class UpdateUserDepartmentsController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
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

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['email'] = base64_decode($request->getAttribute('email'));

        $filter = new Filter();
        $filter->value('email')->string()->trim();

        return $request->withParsedBody($filter->filter($body));
    }


    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('email')->email();
        $validator->required('departments', null, true)->each(function (Validator $validator) {
            $validator->required('department_id')->uuid();
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
        $userDepartments = $this->departmentRepository->getDepartmentsForUser($user)->toArray();
        $departmentIds = array_map(function (array $department) {
            return $department['department_id'];
        }, $body['departments']);

        // Detect which departments are new and which are remaining
        $addedDepartmentIds = [];
        foreach ($departmentIds as $departmentId) {
            if (!isset($userDepartments[$departmentId])) {
                // It's new if it is in the $departmentIds array, but not in the $userDepartments array
                $addedDepartmentIds[] = Uuid::fromString($departmentId);
            } else {
                // If it's in both it will stay and there's no action to take
                unset($userDepartments[$departmentId]);
            }
        }
        // Whatever is left in the $userDepartments array array has been removed
        $removedDepartmentIds = array_map(function (Department $department) {
            return $department->getId();
        }, $userDepartments);

        // Process resulting lists for adding & removing departments
        foreach ($addedDepartmentIds as $addedDepartmentId) {
            $this->userRepository->assignUserToDepartment(
                $user,
                $this->departmentRepository->getDepartment($addedDepartmentId)
            );
        }
        foreach ($removedDepartmentIds as $removedDepartmentId) {
            $this->userRepository->removeUserFromDepartment(
                $user,
                $this->departmentRepository->getDepartment($removedDepartmentId)
            );
        }

        // Return the resulting DB state
        $newUserDepartments = $this->departmentRepository->getDepartmentsForUser($user);
        return new JsonResponse([
            'user_departments' => array_map(function (Department $department) {
                return [
                    'department_id' => $department->getId()->toString(),
                    'name' => $department->getName(),
                ];
            }, $newUserDepartments->toArray()),
        ], 200);
    }
}
