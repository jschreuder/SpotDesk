<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Service\ValidationService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Validator\Uuid as UuidValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class TicketUpdateDepartmentController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(
        private TicketRepository $ticketRepository, 
        private DepartmentRepository $departmentRepository
    )
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['ticket_id'] = $request->getAttribute('ticket_id');
        return $request->withParsedBody($body);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        ValidationService::validate($request, [
            'ticket_id' => new UuidValidator(),
            'department_id' => new UuidValidator(),
        ], ['department_id']);
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();

        $ticket = $this->ticketRepository->getTicket(Uuid::fromString($body['ticket_id']));
        $department = $body['department_id']
            ? $this->departmentRepository->getDepartment(Uuid::fromString($body['department_id']))
            : null;
        $this->ticketRepository->updateTicketDepartment($ticket, $department);

        return new JsonResponse([
            'ticket' => [
                'ticket_id' => $ticket->getId()->toString(),
                'department_id' => $ticket->getDepartment() ? $ticket->getDepartment()->getId() : null,
            ]
        ], 200);
    }
}
