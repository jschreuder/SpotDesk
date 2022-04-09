<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Repository\StatusRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Service\FilterService;
use jschreuder\SpotDesk\Service\ValidationService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Validator\StringLength;
use Laminas\Validator\Uuid as UuidValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class TicketUpdateStatusController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(
        private TicketRepository $ticketRepository, 
        private StatusRepository $statusRepository
    )
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['ticket_id'] = $request->getAttribute('ticket_id');
        return FilterService::filter($request->withParsedBody($body), [
            'status' => strval(...),
        ]);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        ValidationService::validate($request, [
            'ticket_id' => new UuidValidator(),
            'status' => new StringLength(['min' => 1]),
        ]);
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();

        $ticket = $this->ticketRepository->getTicket(Uuid::fromString($body['ticket_id']));
        $status = $this->statusRepository->getStatus($body['status']);
        $this->ticketRepository->updateTicketStatus($ticket, $status);

        return new JsonResponse([
            'ticket' => [
                'ticket_id' => $ticket->getId()->toString(),
                'status' => $ticket->getStatus()->getName(),
            ]
        ], 200);
    }
}
