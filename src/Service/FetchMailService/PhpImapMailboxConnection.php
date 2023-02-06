<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\FetchMailService;

use Generator;
use jschreuder\SpotDesk\Entity\Mailbox;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Webklex\PHPIMAP\Client as ImapClient;
use Webklex\PHPIMAP\ClientManager;

final class PhpImapMailBoxConnection implements MailBoxConnectionInterface
{
    private ImapClient $client;

    public function __construct(private Mailbox $mailbox)
    {
        $this->client = (new ClientManager())->make([
            'host'          => $mailbox->getImapServer(),
            'port'          => $mailbox->getImapPort(),
            'encryption'    => $mailbox->getImapSecurity(),
            'validate_cert' => true,
            'username'      => $mailbox->getImapUser(),
            'password'      => $mailbox->getImapPass(),
            'protocol'      => 'imap'
        ]);
        $this->client->connect();
    }

    /** @return  Generator<FetchedMailInterface> */
    public function fetchMail() : Generator
    {
        $folder = $this->client->getFolder('INBOX');
        $messages = $folder->query()->unseen()->get();

        /** @var \Webklex\PHPIMAP\Message $message */
        foreach ($messages as $message) {
            yield new FetchedMail(
                $message->uid,
                EmailAddressValue::get(strval($message->from)),
                strval($message->subject),
                $message->getTextBody(),
                $message->getHTMLBody(),
                \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', strval($message->date))
            );
        }
    }

    public function markMailAsRead(FetchedMailInterface $fetchedMail) : void
    {
        $this->client->getFolder('INBOX')->query()->getMessage($fetchedMail->getId())->setFlag('SEEN');
    }
}
