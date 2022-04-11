<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Command;

use DateTimeInterface;
use jschreuder\SpotDesk\Entity\Mailbox;
use jschreuder\SpotDesk\Entity\Status;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Repository\MailboxRepository;
use jschreuder\SpotDesk\Repository\StatusRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Service\FetchMailService\FetchedMailInterface;
use jschreuder\SpotDesk\Service\FetchMailService\FetchMailServiceInterface;
use jschreuder\SpotDesk\Service\SendMailService\SendMailServiceInterface;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use OutOfBoundsException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckMailboxesCommand extends Command
{
    public function __construct(
        private MailboxRepository $mailboxRepository,
        private TicketRepository $ticketRepository,
        private StatusRepository $statusRepository,
        private SendMailServiceInterface $mailService,
        private UserRepository $userRepository,
        private FetchMailServiceInterface $fetchMailService
    )
    {
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

    private function isUser(EmailAddressValue $emailAddress) : bool
    {
        static $userEmailAddresses = null;
        if (is_null($userEmailAddresses)) {
            $users = $this->userRepository->getUsers();
            foreach ($users as $user) {
                $userEmailAddresses[] = $user->getEmail()->toString();
            }
        }
        return in_array($emailAddress->toString(), $userEmailAddresses, true);
    }

    private function checkMailbox(Mailbox $mailbox, OutputInterface $output) : void
    {
        $connection = $this->fetchMailService->getMailboxConnection($mailbox);

        /** @var  FetchedMailInterface $fetchedMail */
        foreach ($connection->fetchMail() as $fetchedMail) {
            try {
                // Retrieve mail by ID and fetch the relevant values from it
                $email = $fetchedMail->getFromEmailAddres();
                $subject = $fetchedMail->getSubject();
                $message = $fetchedMail->getTextBody() ?: $this->stripHtml($fetchedMail->getHtmlBody());
                $createdAt = $fetchedMail->getSentDate();

                if ($ticket = $this->getTicketFromEmail($subject)) {
                    $this->processTicketUpdate($mailbox, $ticket, $email, $message, $createdAt);
                } else {
                    $this->processTicket($mailbox, $email, $subject, $message, $createdAt);
                }

                // Mark e-mail as read once the ticket has been created or duplicate was detected
                $connection->markMailAsRead($fetchedMail);
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

    private function getTicketFromEmail(string $subject) : ?Ticket
    {
        $pattern = '#\[(?P<uuid>' . trim(Uuid::VALID_PATTERN, '^$') . ')\]#';
        if (preg_match($pattern, $subject, $matches) < 1) {
            return null;
        }

        $id = Uuid::fromString($matches['uuid']);
        try {
            return $this->ticketRepository->getTicket($id);
        } catch (OutOfBoundsException $exception) {
            return null;
        }
    }

    private function processTicket(
        Mailbox $mailbox,
        EmailAddressValue $email,
        string $subject,
        string $message,
        DateTimeInterface $createdAt
    ) : void
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
        Mailbox $mailbox,
        Ticket $ticket,
        EmailAddressValue $email,
        string $message,
        DateTimeInterface $createdAt
    ) : void
    {
        // Count as reply when sender matches either the original poster or a known user
        if (
            $ticket->getEmail()->isEqual($email)
            || $mailbox->getDepartment()->getEmail()->isEqual($email)
            || $this->isUser($email)
        ) {
            // Ticket creator, department or user - will always be processed as non-internal
            $internal = false;
        } else {
            $subscriptions = $this->ticketRepository->getTicketSubscriptions($ticket);
            try {
                // Subscriber, use subscription "internal" setting
                $subscription = $subscriptions->getByEmailAddress($email);
                $internal = $subscription->isInternal();
            } catch (OutOfBoundsException $exception) {
                // No user, department, creator or subscriber. Thus can't update ticket, create a new one instead
                $this->processTicket($mailbox, $email, $ticket->getSubject(), $message, $createdAt);
                return;
            }
        }

        // Skip if it's a duplicate
        if ($this->ticketRepository->isDuplicateUpdate($ticket, $email, $message, $createdAt)) {
            return;
        }

        //  Create ticket-update and notification mailing
        $ticketUpdate = $this->ticketRepository->createTicketUpdate(
            $ticket, $email, $message, $internal, $createdAt
        );
        $this->mailService->addTicketMailing($ticket, SendMailServiceInterface::TYPE_UPDATE_TICKET, $ticketUpdate);

        // also reopen after processing reply
        $this->ticketRepository->updateTicketStatus(
            $ticket,
            $this->statusRepository->getStatus(Status::STATUS_OPEN)
        );
    }

    private function stripHtml(string $message) : string
    {
        $message = preg_replace('#<br\s*\/?>#i', "\n", $message);
        $message = preg_replace('#<p\s*[^>]*>#i', "\n\n", $message);
        $message = htmlspecialchars($message);
        return htmlentities($message, ENT_QUOTES, 'UTF-8');
    }
}
