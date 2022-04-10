<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\FetchMailService;

use jschreuder\SpotDesk\Entity\Mailbox;

interface FetchMailServiceInterface
{
    public function getMailboxConnection(Mailbox $mailbox) : MailBoxConnectionInterface;
}
