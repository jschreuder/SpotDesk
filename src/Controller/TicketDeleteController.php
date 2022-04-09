<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Service\ValidationService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Validator\Uuid as UuidValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class TicketDeleteController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
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
        $body = (array) $request->getParsedBody();
        $ticket = $this->ticketRepository->getTicket(Uuid::fromString($body['ticket_id']));
        $this->ticketRepository->deleteTicket($ticket);

        return new JsonResponse([
            'ticket' => [
                'ticket_id' => $ticket->getId()->toString(),
            ]
        ], 200);
    }
}
