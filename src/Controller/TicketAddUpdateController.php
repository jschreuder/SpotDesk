<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Entity\Status;
use jschreuder\SpotDesk\Repository\StatusRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Service\FilterService;
use jschreuder\SpotDesk\Service\SendMailService\SendMailServiceInterface;
use jschreuder\SpotDesk\Service\ValidationService;
use jschreuder\SpotDesk\Service\Validator\TypeValidator;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Validator\EmailAddress as EmailAddressValidator;
use Laminas\Validator\StringLength;
use Laminas\Validator\Uuid as UuidValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class TicketAddUpdateController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private StatusRepository $statusRepository,
        private SendMailServiceInterface $mailService
    )
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['ticket_id'] = $request->getAttribute('ticket_id');
        $body['email'] = $request->getAttribute('session')->get('user');
        return FilterService::filter($request->withParsedBody($body), [
            'email' => strval(...),
            'message' => strval(...),
            'internal' => boolval(...),
            'status_update' => strval(...),
        ]);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        ValidationService::validate($request, [
            'ticket_id' => new UuidValidator(),
            'email' => new EmailAddressValidator(),
            'message' => new StringLength(['min' => 1]),
            'internal' => new TypeValidator(['type' => boolean::class]),
            'status_update' => new StringLength(['min' => 1]),
        ], ['status_update']);
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();

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
            $this->mailService->addTicketMailing($ticket, SendMailServiceInterface::TYPE_UPDATE_TICKET, $ticketUpdate);
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
