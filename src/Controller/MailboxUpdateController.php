<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use jschreuder\SpotDesk\Service\FilterService;
use jschreuder\SpotDesk\Service\ValidationService;
use jschreuder\SpotDesk\Value\MailTransportSecurityValue;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Filter\FilterChain;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Validator\Between;
use Laminas\Validator\InArray;
use Laminas\Validator\StringLength;
use Laminas\Validator\Uuid as UuidValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class MailboxUpdateController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(
        private MailboxRepository $mailboxRepository, 
        private DepartmentRepository $departmentRepository
    )
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $body = (array) $request->getParsedBody();
        $body['mailbox_id'] = $request->getAttribute('mailbox_id');
        return FilterService::filter($request->withParsedBody($body), [
            'name' => (new FilterChain())->attach(new StripTags())->attach(new StringTrim()),
            'imap_server' => new StringTrim(),
            'imap_port' => intval(...),
            'imap_user' => strval(...),
            'imap_pass' => strval(...),
        ]);
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        ValidationService::validate($request, [
            'mailbox_id' => new UuidValidator(),
            'name' => new StringLength(['min' => 1]),
            'department_id' => new UuidValidator(),
            'imap_server' => new StringLength(['min' => 2]),
            'imap_port' => new Between(['min' => 1, 'max' => 65535]),
            'imap_security' => new InArray(['haystack' => MailTransportSecurityValue::getValues()]),
            'imap_user' => new StringLength(['min' => 0, 'max' => 255]),
            'imap_pass' => new StringLength(['min' => 0, 'max' => 255]),
        ], ['department_id', 'imap_user', 'imap_pass']);
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
