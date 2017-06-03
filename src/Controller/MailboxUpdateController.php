<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Exception\ValidationFailedException;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use jschreuder\SpotDesk\Value\MailTransportSecurityValue;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

class MailboxUpdateController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    /** @var  MailboxRepository */
    private $mailboxRepository;

    /** @var  DepartmentRepository */
    private $departmentRepository;

    public function __construct(MailboxRepository $mailboxRepository, DepartmentRepository $departmentRepository)
    {
        $this->mailboxRepository = $mailboxRepository;
        $this->departmentRepository = $departmentRepository;
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['mailbox_id'] = $request->getAttribute('mailbox_id');

        $filter = new Filter();
        $filter->value('name')->string()->trim();
        $filter->value('imap_server')->string()->trim();
        $filter->value('imap_port')->int();
        $filter->value('imap_user')->string();
        $filter->value('imap_pass')->string();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('mailbox_id')->uuid();
        $validator->required('name')->string()->lengthBetween(2, 63);
        $validator->optional('department_id')->uuid();
        $validator->required('imap_server')->string()->lengthBetween(2, 255);
        $validator->required('imap_port')->integer(true)->between(1, 65535);
        $validator->required('imap_security')->inArray(MailTransportSecurityValue::getValues());
        $validator->optional('imap_user')->string()->lengthBetween(0, 255);
        $validator->optional('imap_pass')->string()->lengthBetween(0, 255);

        $validationResult = $validator->validate((array) $request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $mailbox = $this->mailboxRepository->getMailbox(Uuid::fromString($body['mailbox_id']));
        $department = $body['department_id']
            ? $this->departmentRepository->getDepartment(Uuid::fromString($body['department_id']))
            : null;

        $mailbox->setName($body['name']);
        $mailbox->setDepartment($department);
        $mailbox->setImapServer($body['imap_server']);
        $mailbox->setImapPort($body['imap_port']);
        $mailbox->setImapSecurity(MailTransportSecurityValue::get($body['imap_security']));

        if ($mailbox->getImapUser() !== $body['imap_user']) {
            // When username changes the password changes as well
            $mailbox->setImapUser($body['imap_user']);
            $mailbox->setImapPass($body['imap_pass']);
        } elseif ($body['imap_pass']) {
            // Only update password otherwise when new password is given
            $mailbox->setImapPass($body['imap_pass']);
        }

        $this->mailboxRepository->updateMailbox($mailbox);

        return new JsonResponse([
            'mailbox' => [
                'mailbox_id' => $mailbox->getId()->toString(),
                'name' => $mailbox->getName(),
                'department_id' => $mailbox->getDepartment() ? $mailbox->getDepartment()->getId()->toString() : null,
                'department_name' => $mailbox->getDepartment() ? $mailbox->getDepartment()->getName() : null,
                'imap_server' => $mailbox->getImapServer(),
                'imap_port' => $mailbox->getImapPort(),
                'imap_security' => $mailbox->getImapSecurity()->toString(),
                'imap_user' => $mailbox->getImapUser(),
                'last_check' => $mailbox->getLastCheck()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
