<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Command;

use jschreuder\SpotDesk\Entity\Mailbox;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Service\SendMailService\SendMailServiceInterface;
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

    /** @var  SendMailServiceInterface */
    private $mailService;

    public function __construct(
        MailboxRepository $mailboxRepository,
        TicketRepository $ticketRepository,
        SendMailServiceInterface $mailService
    ) {
        $this->mailboxRepository = $mailboxRepository;
        $this->ticketRepository = $ticketRepository;
        $this->mailService = $mailService;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('mail:check')
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

    private function createConnection(Mailbox $mailbox) : ImapConnection
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
                $message = $mail->textPlain ?: $this->stripHtml($mail->textHtml);
                $createdAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $mail->date);

                if ($ticket = $this->getTicketFromSubject($subject)) {
                    $this->processTicketUpdate($ticket, $email, $message, $createdAt);
                } else {
                    $this->processTicket($mailbox, $email, $subject, $message, $createdAt);
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

    private function getTicketFromSubject(string $subject) : ?Ticket
    {
        $pattern = '#\[(?P<uuid>' . trim(Uuid::VALID_PATTERN, '^$') . ')\]#';
        if (preg_match($pattern, $subject, $matches) < 1) {
            return null;
        }

        $id = Uuid::fromString($matches['uuid']);
        try {
            return $this->ticketRepository->getTicket($id);
        } catch (\OutOfBoundsException $exception) {
            return null;
        }
    }

    private function processTicket(
        Mailbox $mailbox,
        EmailAddressValue $email,
        string $subject,
        string $message,
        \DateTimeInterface $createdAt
    )
    {
        $department = $mailbox->getDepartment();
        if ($this->ticketRepository->isDuplicate($email, $subject, $message, $createdAt, $department)) {
            return;
        }

        $ticket = $this->ticketRepository->createTicket(
            $email, $subject, $message, $department, $createdAt
        );
        $this->mailService->addTicketMailing($ticket, SendMailServiceInterface::TYPE_NEW_TICKET);
    }

    private function processTicketUpdate(
        Ticket $ticket,
        EmailAddressValue $email,
        string $message,
        \DateTimeInterface $createdAt
    )
    {
        if ($this->ticketRepository->isDuplicateUpdate($ticket, $email, $message, $createdAt)) {
            return;
        }
        $ticketUpdate = $this->ticketRepository->createTicketUpdate(
            $ticket, $email, $message, false, $createdAt
        );
        $this->mailService->addTicketMailing($ticket, SendMailServiceInterface::TYPE_UPDATE_TICKET, $ticketUpdate);
    }

    private function stripHtml(string $message) : string
    {
        $message = preg_replace('#<br\s*\/?>#i', "\n", $message);
        $message = preg_replace('#<p\s*[^>]*>#i', "\n\n", $message);
        $message = filter_var($message, FILTER_SANITIZE_STRING);
        return htmlentities($message, ENT_QUOTES, 'UTF-8');
    }
}
