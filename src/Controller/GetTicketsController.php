<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\Middle\Session\SessionInterface;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use jschreuder\SpotDesk\Value\StatusTypeValue;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class GetTicketsController implements ControllerInterface, RequestValidatorInterface
{
    /** @var  TicketRepository */
    private $ticketRepository;

    public function __construct(TicketRepository $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function validateRequest(ServerRequestInterface $request): void
    {
        $validator = new Validator();
        $validator->optional('status_type')->string()->inArray(StatusTypeValue::getValues(), true);

        $validationResult = $validator->validate($request->getQueryParams());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
    }

    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        /** @var  SessionInterface $session */
        $session = $request->getAttribute('session');
        $query = $request->getQueryParams();

        $type = !empty($query['status_type'])
            ? StatusTypeValue::get($query['status_type'])
            : StatusTypeValue::get(StatusTypeValue::TYPE_OPEN);
        $tickets = $this->ticketRepository->getOpenTicketsForUser(EmailAddressValue::get($session->get('user')), $type);

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
            }, $tickets->toArray())
        ], 200);
    }
}
