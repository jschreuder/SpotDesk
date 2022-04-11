<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\FetchMailService;

use Generator;
use jschreuder\SpotDesk\Entity\Mailbox;
use jschreuder\SpotDesk\Value\EmailAddressValue;
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

    /** @return  Generator<FetchedMailInterface> */
    public function fetchMail() : Generator
    {
        $mailIds = $this->getConnection()->searchMailbox('UNSEEN');
        foreach ($mailIds as $mailId) {
            $email = $this->getConnection()->getMail($mailId, false);
            yield new FetchedMail(
                $mailId,
                EmailAddressValue::get($email->fromAddress),
                $email->subject,
                $email->textPlain,
                $email->textHtml,
                \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $email->date)
            );
        }
    }

    public function markMailAsRead(FetchedMailInterface $fetchedMail) : void
    {
        $this->getConnection()->markMailAsRead($fetchedMail->getId());
    }
}
