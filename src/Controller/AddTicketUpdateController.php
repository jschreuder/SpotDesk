<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\SpotDesk\Entity\Status;
use jschreuder\SpotDesk\Repository\StatusRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Service\SendMailService\SendMailServiceInterface;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

class AddTicketUpdateController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    /** @var  TicketRepository */
    private $ticketRepository;

    /** @var  StatusRepository */
    private $statusRepository;

    /** @var  SendMailServiceInterface */
    private $mailService;

    public function __construct(
        TicketRepository $ticketRepository,
        StatusRepository $statusRepository,
        SendMailServiceInterface $mailService
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->statusRepository = $statusRepository;
        $this->mailService = $mailService;
    }

    public function filterRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $body = (array)$request->getParsedBody();
        $body['ticket_id'] = $request->getAttribute('ticket_id');
        $body['email'] = $request->getAttribute('session')->get('user');
        $filter = new Filter();
        $filter->value('ticket_id')->string()->trim();
        $filter->value('email')->string();
        $filter->value('message')->string();
        $filter->value('internal')->bool();
        $filter->value('status_update')->string();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request): void
    {
        $validator = new Validator();
        $validator->required('ticket_id')->uuid();
        $validator->required('email')->email();
        $validator->required('message')->string();
        $validator->required('internal')->bool();
        $validator->optional('status_update')->string();

        $validationResult = $validator->validate((array)$request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
    }

    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $body = (array)$request->getParsedBody();

        $ticket = $this->ticketRepository->getTicket(Uuid::fromString($body['ticket_id']));
        $ticketUpdate = $this->ticketRepository->createTicketUpdate(
            $ticket,
            EmailAddressValue::get($body['email']),
            $body['message'],
            $body['internal']
        );

        if (!empty($body['status_update'])) {
            $status = $this->statusRepository->getStatus($body['status_update']);
            $this->ticketRepository->updateTicketStatus($ticket, $status);
        } elseif ($ticket->getStatus()->getName() === Status::STATUS_NEW) {
            // Automatically upgrade status from new to open after first update
            $openStatus = $this->statusRepository->getStatus(Status::STATUS_OPEN);
            $this->ticketRepository->updateTicketStatus($ticket, $openStatus);
        }

        if (!$ticketUpdate->isInternal()) {
            $this->mailService->addMailing($ticket, SendMailServiceInterface::TYPE_UPDATE_TICKET, $ticketUpdate);
        }

        return new JsonResponse([
            'ticket_update' => [
                'ticket_update_id' => $ticketUpdate->getId()->toString(),
                'message' => $ticketUpdate->getMessage(),
                'created_at' => $ticketUpdate->getCreatedAt()->format('Y-m-d H:i:s'),
                'created_by' => $ticketUpdate->getEmail()->toString(),
                'internal' => $ticketUpdate->isInternal(),
            ],
        ], 201);
    }
}
