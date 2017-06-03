<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Exception\ValidationFailedException;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

class MailboxGetOneController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    /** @var  MailboxRepository */
    private $mailboxRepository;

    public function __construct(MailboxRepository $mailboxRepository)
    {
        $this->mailboxRepository = $mailboxRepository;
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['mailbox_id'] = $request->getAttribute('mailbox_id');
        return $request->withParsedBody($body);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('mailbox_id')->uuid();

        $validationResult = $validator->validate((array) $request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $mailbox = $this->mailboxRepository->getMailbox(Uuid::fromString($body['mailbox_id']));

        return new JsonResponse([
            'mailbox' => [
                'mailbox_id' => $mailbox->getId()->toString(),
                'name' => $mailbox->getName(),
                'department_id' => $mailbox->getDepartment() ? $mailbox->getDepartment()->getId()->toString() : null,
                'imap_server' => $mailbox->getImapServer(),
                'imap_port' => $mailbox->getImapPort(),
                'imap_security' => $mailbox->getImapSecurity()->toString(),
                'imap_user' => $mailbox->getImapUser(),
                'last_check' => $mailbox->getLastCheck()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
