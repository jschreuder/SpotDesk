<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

class DepartmentDeleteController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    /** @var  DepartmentRepository */
    private $departmentRepository;

    /** @var  TicketRepository */
    private $ticketRepository;

    public function __construct(DepartmentRepository $departmentRepository, TicketRepository $ticketRepository)
    {
        $this->departmentRepository = $departmentRepository;
        $this->ticketRepository = $ticketRepository;
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['department_id'] = $request->getAttribute('department_id');
        $filter = new Filter();
        $filter->value('ticket_action')->string()->trim();
        $filter->value('ticket_department_id')->string()->trim();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('department_id')->uuid();
        $validator->required('ticket_action')->inArray(['delete', 'move']);
        $validator->optional('ticket_department_id')->uuid();

        $validationResult = $validator->validate((array) $request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
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
