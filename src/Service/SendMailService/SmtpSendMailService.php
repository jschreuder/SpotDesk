<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\SendMailService;

use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketMailing;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use jschreuder\SpotDesk\Repository\TicketMailingRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class SmtpSendMailService implements SendMailServiceInterface
{
    public function __construct(
        private TicketMailingRepository $ticketMailingRepository,
        private MailerInterface $mailer,
        private MailTemplateFactoryInterface $mailTemplateFactory,
        private EmailAddressValue $defaultFrom,
        private string $siteName
    )
    {
    }

    public function addTicketMailing(Ticket $ticket, string $type, ?TicketUpdate $ticketUpdate = null) : void
    {
        $this->ticketMailingRepository->createTicketMailing($ticket, $type, $ticketUpdate);
    }

    public function send(TicketMailing $ticketMailing) : void
    {
        $ticket = $ticketMailing->getTicket();
        $ticketUpdate = $ticketMailing->getTicketUpdate();

        $mail = $this->mailTemplateFactory->getMailTemplate($ticketMailing->getType());
        $subject = $this->createSubject($ticket, $ticketUpdate);
        $renderedMail = $mail->render(['ticket' => $ticket, 'ticket_update' => $ticketUpdate]);

        $message = $this->createMessage($ticket, $subject, $renderedMail);
        $this->mailer->send($message);

        $this->ticketMailingRepository->setSent($ticketMailing);
    }

    private function createSubject(Ticket $ticket, ?TicketUpdate $ticketUpdate) : string
    {
        return ($ticketUpdate ? 'Re: ' : '') . $ticket->getSubject() . ' [' . $ticket->getId()->toString() . ']';
    }

    private function createMessage(Ticket $ticket, string $subject, string $renderedMail) : Email
    {
        $fromMail = $ticket->getDepartment()
            ? $ticket->getDepartment()->getEmail()->toString()
            : $this->defaultFrom->toString();
        $fromName = $ticket->getDepartment()
            ? $ticket->getDepartment()->getName() . ' - ' . $this->siteName
            : $this->siteName;
        return (new Email())
            ->from(new Address($fromMail, $fromName))
            ->to($ticket->getEmail()->toString())
            ->subject($subject)
            ->html($renderedMail);
    }
}
