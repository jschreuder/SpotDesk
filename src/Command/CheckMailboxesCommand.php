<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Command;

use jschreuder\SpotDesk\Entity\Mailbox;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpImap\Mailbox as ImapConnection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckMailboxesCommand extends Command
{
    /** @var  MailboxRepository */
    private $mailboxRepository;

    /** @var  TicketRepository */
    private $ticketRepository;

    public function __construct(MailboxRepository $mailboxRepository, TicketRepository $ticketRepository)
    {
        $this->mailboxRepository = $mailboxRepository;
        $this->ticketRepository = $ticketRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('mailbox:check')
            ->addArgument('mailbox', InputArgument::OPTIONAL, 'Specific mailbox ID to check');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mailbox = $input->getArgument('mailbox');
        if ($mailbox) {
            $mailboxes = [$this->mailboxRepository->getMailbox(Uuid::fromString($mailbox))];
        } else {
            $mailboxes = $this->mailboxRepository->getMailboxes();
        }

        foreach ($mailboxes as $mailbox) {
            $this->checkMailbox($mailbox, $output);
        }
    }

    private function createConnection(Mailbox $mailbox): ImapConnection
    {
        $path = '{' . $mailbox->getImapServer() . ':' . $mailbox->getImapPort() . '/imap';
        switch ($mailbox->getImapSecurity()->toString()) {
            case 'ssl':
                $path .= '/ssl';
                break;
            case 'tls':
                $path .= '/tls';
                break;
        }
        $path .= '}INBOX';

        return new ImapConnection($path, $mailbox->getImapUser(), $mailbox->getImapPass());
    }

    private function checkMailbox(Mailbox $mailbox, OutputInterface $output)
    {
        $connection = $this->createConnection($mailbox);

        // Only retrieve unread mails
        $mailIds = $connection->searchMailbox('UNSEEN');
        foreach ($mailIds as $mailId) {
            try {
                // Retrieve mail by ID and fetch the relevant values from it
                $mail = $connection->getMail($mailId, false);
                $email = EmailAddressValue::get($mail->fromAddress);
                $subject = $mail->subject;
                $message = $mail->textHtml ?: $mail->textPlain;
                $createdAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $mail->date);
                $department = $mailbox->getDepartment();

                if ($this->ticketRepository->isDuplicate($email, $subject, $message, $createdAt, $department)) {
                    // Skip duplicate
                    $output->writeln(
                        'DUPLICATE: Exact same ticket has been created within 24 hours of this one'
                    );
                } else {
                    // Create new ticket
                    $this->ticketRepository->createTicket($email, $subject, $message, $createdAt, $department);
                }

                // Mark e-mail as read once the ticket has been created or duplicate was detected
                $connection->markMailAsRead($mailId);
            } catch (\Throwable $exception) {
                // Output errors to the output
                $output->writeln(
                    'ERROR: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' .
                    $exception->getLine()
                );
            }
        }

        // Update last check date-time
        $this->mailboxRepository->updateLastCheck($mailbox);
    }
}
