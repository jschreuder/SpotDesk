<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\SpotDesk\Repository\StatusRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

class TicketUpdateStatusController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    /** @var  TicketRepository */
    private $ticketRepository;

    /** @var  StatusRepository */
    private $statusRepository;

    public function __construct(TicketRepository $ticketRepository, StatusRepository $statusRepository)
    {
        $this->ticketRepository = $ticketRepository;
        $this->statusRepository = $statusRepository;
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['ticket_id'] = $request->getAttribute('ticket_id');
        $filter = new Filter();
        $filter->value('status')->string();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('ticket_id')->uuid();
        $validator->required('status')->string();

        $validationResult = $validator->validate((array) $request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
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
