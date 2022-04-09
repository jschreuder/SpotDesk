<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\SendMailService;

use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketMailing;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use jschreuder\SpotDesk\Exception\SpotDeskException;
use jschreuder\SpotDesk\Repository\TicketMailingRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;

final class SmtpSendMailService implements SendMailServiceInterface
{
    public function __construct(
        private TicketMailingRepository $ticketMailingRepository,
        private \Swift_Mailer $swiftMailer,
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
        $sent = $this->swiftMailer->send($message);
        if ($sent === 0) {
            throw new SpotDeskException('Failed to send mail');
        }

        $this->ticketMailingRepository->setSent($ticketMailing);
    }

    private function createSubject(Ticket $ticket, ?TicketUpdate $ticketUpdate) : string
    {
        return ($ticketUpdate ? 'Re: ' : '') . $ticket->getSubject() . ' [' . $ticket->getId()->toString() . ']';
    }

    private function createMessage(Ticket $ticket, string $subject, string $renderedMail) : \Swift_Message
    {
        $fromMail = $ticket->getDepartment()
            ? $ticket->getDepartment()->getEmail()->toString()
            : $this->defaultFrom->toString();
        $fromName = $ticket->getDepartment()
            ? $ticket->getDepartment()->getName() . ' - ' . $this->siteName
            : $this->siteName;

        return \Swift_Message::newInstance($subject)
            ->setFrom($fromMail, $fromName)
            ->setTo($ticket->getEmail()->toString())
            ->setBody($renderedMail, 'text/html');
    }
}
