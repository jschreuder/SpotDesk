<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class GetOpenTicketsController implements ControllerInterface
{
    /** @var  TicketRepository */
    private $ticketRepository;

    public function __construct(TicketRepository $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        /** @var  SessionInterface $session */
        $session = $request->getAttribute('session');
        $tickets = $this->ticketRepository->getOpenTicketsForUser(EmailAddressValue::get($session->get('user')));

        return new JsonResponse([
            'tickets' => array_map(function (Ticket $ticket) {
                return [
                    'ticket_id' => $ticket->getId()->toString(),
                    'subject' => $ticket->getSubject(),
                    'created_at' => $ticket->getCreatedAt()->format('Y-m-d H:i:s'),
                    'created_by' => $ticket->getEmail()->toString(),
                    'status' => $ticket->getStatus()->getStatus(),
                    'department_id' => is_null($ticket->getDepartment())
                        ? null
                        : $ticket->getDepartment()->getId()->toString(),
                    'updates' => $ticket->getUpdates(),
                    'last_update' => $ticket->getLastUpdate()->format('Y-m-d H:i:s'),
                ];
            }, $tickets->toArray())
        ], 200);
    }
}
