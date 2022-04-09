<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Service\ValidationService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Validator\InArray;
use Laminas\Validator\Uuid as UuidValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class DepartmentDeleteController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(
        private DepartmentRepository $departmentRepository, 
        private TicketRepository $ticketRepository
    )
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['department_id'] = $request->getAttribute('department_id');
        return $request->withParsedBody($body);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        ValidationService::validate($request, [
            'department_id' => new UuidValidator(),
            'ticket_action' => new InArray(['haystack' => ['delete', 'move']]),
            'ticket_department_id' => new UuidValidator(),
        ], ['ticket_department_id']);
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $department = $this->departmentRepository->getDepartment(Uuid::fromString($body['department_id']));

        if ($this->hasChildDepartments($department)) {
            return new JsonResponse(['message' => 'Cannot remove department with child departments'], 400);
        }

        if ($body['ticket_action'] === 'delete') {
            $this->ticketRepository->deleteTicketsFromDepartment($department);
        } else {
            $newDepartment = !empty($body['ticket_department_id'])
                ? $this->departmentRepository->getDepartment(Uuid::fromString($body['ticket_department_id']))
                : null;
            $this->ticketRepository->moveTicketsFromDepartmentToDepartment($department, $newDepartment);
        }
        $this->departmentRepository->deleteDepartment($department);

        return new JsonResponse(['message' => 'Success'], 200);
    }

    private function hasChildDepartments(Department $department)
    {
        $allDepartments = $this->departmentRepository->getDepartments();
        foreach ($allDepartments as $possibleChild) {
            if ($possibleChild->getParent() && $possibleChild->getParent()->getId()->equals($department->getId())) {
                return true;
            }
        }
        return false;
    }
}
