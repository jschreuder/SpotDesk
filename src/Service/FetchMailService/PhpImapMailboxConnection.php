<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\FetchMailService;

use jschreuder\SpotDesk\Entity\Mailbox;
use PhpImap\Mailbox as ImapConnection;

final class PhpImapMailBoxConnection implements MailBoxConnectionInterface
{
    private ImapConnection $connection;

    public function __construct(private Mailbox $mailbox)
    {
    }

    private function getConnection()
    {
        if (!isset($this->connection)) {
            $path = '{' . $this->mailbox->getImapServer() . ':' . $this->mailbox->getImapPort() . '/imap';
            switch ($this->mailbox->getImapSecurity()->toString()) {
                case 'ssl':
                    $path .= '/ssl';
                    break;
                case 'tls':
                    $path .= '/tls';
                    break;
            }
            $path .= '}INBOX';
    
            $this->connection = new ImapConnection(
                $path, 
                $this->mailbox->getImapUser(), 
                $this->mailbox->getImapPass()
            );
        }
        return $this->connection;
    }

    public function fetchMailIds() : array
    {
        return $this->getConnection()->searchMailbox('UNSEEN');
    }

    public function fetchMailById(int $mailId) : FetchedMailInterface
    {
        return $this->getConnection()->getMail($mailId, false);
    }

    public function markMailAsRead(int $mailId) : void
    {
        $this->getConnection()->markMailAsRead($mailId);
    }
}
