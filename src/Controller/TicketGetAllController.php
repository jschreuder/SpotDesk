<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Service\FilterService;
use jschreuder\SpotDesk\Service\ValidationService;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use jschreuder\SpotDesk\Value\StatusTypeValue;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Validator\Between;
use Laminas\Validator\InArray;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TicketGetAllController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(private TicketRepository $ticketRepository)
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        return FilterService::filterQuery($request, [
            'limit' => intval(...),
            'page' => intval(...),
        ]);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        ValidationService::validateQuery($request, [
            'status_type' => new InArray(['haystack' => StatusTypeValue::getValues()]),
            'limit' => new Between(['min' => 5, 'max' => 50]),
            'page' => new Between(['min' => 0]),
            'sort_by' => new InArray(['haystack' => ['subject', 'updates', 'last_update', 'status']]),
            'sort_direction' => new InArray(['haystack' => ['asc', 'desc']]),
        ], ['status_type', 'limit', 'page', 'sort_by', 'sort_direction']);
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
