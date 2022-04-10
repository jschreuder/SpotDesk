<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\FetchMailService;

use jschreuder\SpotDesk\Entity\Mailbox;

final class PhpImapFetchMailService implements FetchMailServiceInterface
{
    public function getMailboxConnection(Mailbox $mailbox) : MailBoxConnectionInterface
    {
        return new PhpImapMailBoxConnection($mailbox);
    }
}
