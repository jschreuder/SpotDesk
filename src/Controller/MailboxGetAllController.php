<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Controller;

use jschreuder\Middle\Controller\ControllerInterface;
use jschreuder\SpotDesk\Entity\Mailbox;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MailboxGetAllController implements ControllerInterface
{
    public function __construct(private MailboxRepository $mailboxRepository)
    {
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
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
                    'last_check' => $mailbox->getLastCheck()->format('Y-m-d H:i:s'),
                ];
            }, $mailboxes->toArray())
        ], 200);
    }
}
