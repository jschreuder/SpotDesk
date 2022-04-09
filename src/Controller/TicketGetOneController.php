<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Service\ValidationService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Validator\Uuid as UuidValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class TicketGetOneController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(private TicketRepository $ticketRepository)
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
        ]);
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $ticketId = Uuid::fromString($request->getAttribute('ticket_id'));
        $ticket = $this->ticketRepository->getTicket($ticketId);
        $ticketUpdates = $this->ticketRepository->getTicketUpdates($ticket);

        return new JsonResponse([
            'ticket' => [
                'ticket_id' => $ticket->getId()->toString(),
                'subject' => $ticket->getSubject(),
                'message' => $ticket->getMessage(),
                'created_at' => $ticket->getCreatedAt()->format('Y-m-d H:i:s'),
                'created_by' => $ticket->getEmail()->toString(),
                'status' => $ticket->getStatus()->getName(),
                'department_id' => is_null($ticket->getDepartment())
                    ? null
                    : $ticket->getDepartment()->getId()->toString(),
                'department_name' => is_null($ticket->getDepartment())
                    ? null
                    : $ticket->getDepartment()->getName(),
                'updates' => $ticket->getUpdates(),
                'last_update' => $ticket->getLastUpdate()->format('Y-m-d H:i:s'),
            ],
            'ticket_updates' => array_map(function (TicketUpdate $ticketUpdate) : array {
                return [
                    'ticket_update_id' => $ticketUpdate->getId()->toString(),
                    'message' => $ticketUpdate->getMessage(),
                    'created_at' => $ticketUpdate->getCreatedAt()->format('Y-m-d H:i:s'),
                    'created_by' => $ticketUpdate->getEmail()->toString(),
                    'internal' => $ticketUpdate->isInternal(),
                ];
            }, $ticketUpdates->toArray()),
        ], 200);
    }
}
