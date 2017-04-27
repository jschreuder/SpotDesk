<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\SendMailService;

use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketMailing;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use jschreuder\SpotDesk\Repository\TicketMailingRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;

final class SmtpSendMailService implements SendMailServiceInterface
{
    /** @var  TicketMailingRepository */
    private $ticketMailingRepository;

    /** @var  \Swift_Mailer */
    private $swiftMailer;

    /** @var  MailTemplateFactoryInterface */
    private $mailTemplateFactory;

    /** @var  EmailAddressValue */
    private $defaultFrom;

    /** @var  string */
    private $siteName;

    public function __construct(
        TicketMailingRepository $ticketMailingRepository,
        \Swift_Mailer $swiftMailer,
        MailTemplateFactoryInterface $mailTemplateFactory,
        EmailAddressValue $defaultFrom,
        string $siteName
    ) {
        $this->ticketMailingRepository = $ticketMailingRepository;
        $this->swiftMailer = $swiftMailer;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->defaultFrom = $defaultFrom;
        $this->siteName = $siteName;
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
        $renderedMail = $mail->render(['ticket' => $ticket, 'ticketUpdate' => $ticketUpdate]);

        $message = $this->createMessage($ticket, $subject, $renderedMail);
        $sent = $this->swiftMailer->send($message);
        if ($sent === 0) {
            throw new \RuntimeException('Failed to send mail');
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
