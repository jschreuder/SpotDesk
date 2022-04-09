<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DepartmentGetAllController implements ControllerInterface
{
    public function __construct(private DepartmentRepository $departmentRepository)
    {
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $departments = $this->departmentRepository->getDepartments();

        return new JsonResponse([
            'departments' => array_map(function (Department $department) {
                return [
                    'department_id' => $department->getId()->toString(),
                    'name' => $department->getName(),
                    'parent_id' => $department->getParent() ? $department->getParent()->getId()->toString() : null,
                    'email' => $department->getEmail()->toString(),
                ];
            }, $departments->toArray())
        ], 200);
    }
}
