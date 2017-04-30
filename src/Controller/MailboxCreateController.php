<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Controller\ValidationFailedException;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use jschreuder\SpotDesk\Value\MailTransportSecurityValue;
use Particle\Filter\Filter;
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

class MailboxCreateController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
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
        $filter = new Filter();
        $filter->value('name')->string()->stripHtml()->trim();
        $filter->value('department_id')->string()->trim();
        $filter->value('imap_server')->string()->trim();
        $filter->value('imap_port')->int();
        $filter->value('imap_security')->string()->trim();
        $filter->value('imap_user')->string()->trim();
        $filter->value('imap_pass')->string()->trim();

        return $request->withParsedBody($filter->filter($body));
    }

    public function validateRequest(ServerRequestInterface $request) : void
    {
        $validator = new Validator();
        $validator->required('name')->string();
        $validator->optional('department_id')->uuid();
        $validator->required('imap_server')->string();
        $validator->required('imap_port')->integer(true);
        $validator->required('imap_security')->inArray(MailTransportSecurityValue::getValues());
        $validator->required('imap_user')->string();
        $validator->required('imap_pass')->string();

        $validationResult = $validator->validate((array) $request->getParsedBody());
        if (!$validationResult->isValid()) {
            throw new ValidationFailedException($validationResult->getMessages());
        }
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $body = (array) $request->getParsedBody();

        $department = empty($body['department_id'])
            ? null
            : $this->departmentRepository->getDepartment(Uuid::fromString($body['department_id']));
        $mailbox = $this->mailboxRepository->createMailbox(
            $body['name'],
            $department,
            $body['imap_server'],
            $body['imap_port'],
            MailTransportSecurityValue::get($body['imap_security']),
            $body['imap_user'],
            $body['imap_pass']
        // @todo add last_check?
        );

        return new JsonResponse(['mailbox_id' => $mailbox->getId()->toString()], 201);
    }
}