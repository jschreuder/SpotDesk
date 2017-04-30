<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use jschreuder\SpotDesk\Value\StatusTypeValue;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class TicketGetAllController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    /** @var  TicketRepository */
    private $ticketRepository;

    public function __construct(TicketRepository $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $filter = new Filter();
        $filter->values(['limit', 'page'])->int();
        return $request->withQueryParams($filter->filter($request->getQueryParams()));
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->optional('status_type')->string()->inArray(StatusTypeValue::getValues(), true);
        $validator->optional('limit')->integer(true)->between(5, 50);
        $validator->optional('page')->integer(true)->greaterThan(0);
        $validator->optional('sort_by')->string()->inArray(['subject', 'updates', 'last_update', 'status']);
        $validator->optional('sort_direction')->string()->inArray(['asc', 'desc']);

        $validationResult = $validator->validate($request->getQueryParams());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        /** @var  SessionInterface $session */
        $session = $request->getAttribute('session');
        $query = $request->getQueryParams();

        $type = !empty($query['status_type'])
            ? StatusTypeValue::get($query['status_type'])
            : StatusTypeValue::get(StatusTypeValue::TYPE_OPEN);
        $tickets = $this->ticketRepository->getTicketsForUser(
            EmailAddressValue::get($session->get('user')),
            $type,
            $query['limit'] ?? 15,
            $query['page'] ?? 1,
            $query['sort_by'] ?? 'last_update',
            $query['sort_direction'] ?? 'desc'
        );

        return new JsonResponse([
            'tickets' => array_map(function (Ticket $ticket) {
                return [
                    'ticket_id' => $ticket->getId()->toString(),
                    'subject' => $ticket->getSubject(),
                    'created_at' => $ticket->getCreatedAt()->format('Y-m-d H:i:s'),
                    'created_by' => $ticket->getEmail()->toString(),
                    'status' => $ticket->getStatus()->getName(),
                    'department_id' => is_null($ticket->getDepartment())
                        ? null
                        : $ticket->getDepartment()->getId()->toString(),
                    'updates' => $ticket->getUpdates(),
                    'last_update' => $ticket->getLastUpdate()->format('Y-m-d H:i:s'),
                ];
            }, $tickets->toArray()),
            'total_count' => $tickets->getTotalCount(),
        ], 200);
    }
}
