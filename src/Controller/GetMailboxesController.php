<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\SpotDesk\Entity\Mailbox;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class GetMailboxesController implements ControllerInterface
{
    /** @var  MailboxRepository */
    private $mailboxRepository;

    public function __construct(MailboxRepository $mailboxRepository)
    {
        $this->mailboxRepository = $mailboxRepository;
    }

    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $mailboxes = $this->mailboxRepository->getMailboxes();

        return new JsonResponse([
            'mailboxes' => array_map(function (Mailbox $mailbox) {
                return [
                    'mailbox_id' => $mailbox->getId()->toString(),
                    'name' => $mailbox->getName(),
                    'department_id' => $mailbox->getDepartment()
                        ? $mailbox->getDepartment()->getId()->toString()
                        : null,
                    'department_name' => $mailbox->getDepartment()
                        ? $mailbox->getDepartment()->getName()
                        : null,
                    'last_check' => $mailbox->getLastCheck()->format('Y-m-d H:i:s'),
                ];
            }, $mailboxes->toArray())
        ], 200);
    }
}
