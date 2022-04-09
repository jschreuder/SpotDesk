<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\Middle\Controller\RequestFilterInterface;
use jschreuder\Middle\Controller\RequestValidatorInterface;
use jschreuder\Middle\Exception\ValidationFailedException;
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
use Particle\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class MailboxCreateController implements ControllerInterface, RequestFilterInterface, RequestValidatorInterface
{
    public function __construct(
        private MailboxRepository $mailboxRepository, 
        private DepartmentRepository $departmentRepository
    )
    {
    }

    public function filterRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        return FilterService::filter($request, [
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
            'name' => new StringLength(['min' => 1]),
            'department_id' => new UuidValidator(),
            'imap_server' => new StringLength(['min' => 1]),
            'imap_port' => new Between(['min' => 1, 'max' => 65535]),
            'imap_security' => new InArray(['haystack' => MailTransportSecurityValue::getValues()]),
            'imap_user' => new StringLength(['min' => 0, 'max' => 255]),
            'imap_pass' => new StringLength(['min' => 0, 'max' => 255]),
        ], ['department_id']);
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
        );

        return new JsonResponse(['mailbox_id' => $mailbox->getId()->toString()], 201);
    }
}