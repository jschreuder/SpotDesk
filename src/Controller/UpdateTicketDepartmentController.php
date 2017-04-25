<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

class UpdateTicketDepartmentController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    /** @var  TicketRepository */
    private $ticketRepository;

    /** @var  DepartmentRepository */
    private $departmentRepository;

    public function __construct(TicketRepository $ticketRepository, DepartmentRepository $departmentRepository)
    {
        $this->ticketRepository = $ticketRepository;
        $this->departmentRepository = $departmentRepository;
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array)$request->getParsedBody();
        $body['ticket_id'] = $request->getAttribute('ticket_id');
        $filter = new Filter();
        $filter->value('ticket_id')->string()->trim();
        $filter->value('department_id')->string()->trim();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('ticket_id')->uuid();
        $validator->optional('department_id')->uuid();

        $validationResult = $validator->validate((array)$request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array)$request->getParsedBody();

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
