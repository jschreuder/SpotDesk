<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use jschreuder\SpotDesk\Service\ValidationService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Validator\Uuid as UuidValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class MailboxDeleteController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(private MailboxRepository $mailboxRepository)
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['mailbox_id'] = $request->getAttribute('mailbox_id');
        return $request->withParsedBody($body);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        ValidationService::validate($request, [
            'mailbox_id' => new UuidValidator(),
        ]);
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $mailbox = $this->mailboxRepository->getMailbox(Uuid::fromString($body['mailbox_id']));
        $this->mailboxRepository->deleteMailbox($mailbox);

        return new JsonResponse([
            'mailbox' => [
                'mailbox_id' => $mailbox->getId()->toString(),
            ]
        ], 200);
    }
}
