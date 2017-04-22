<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Service\SendMailService;

use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketMailing;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use jschreuder\SpotDesk\Repository\TicketMailingRepository;
use jschreuder\SpotDesk\Service\MailServiceInterface;
use jschreuder\SpotDesk\Value\EmailAddressValue;

class SmtpSendMailService implements MailServiceInterface
{
    /** @var  \Swift_Mailer */
    private $swiftMailer;

    /** @var  MailTemplateFactory */
    private $mailTemplateFactory;

    /** @var  TicketMailingRepository */
    private $ticketMailingRepository;

    /** @var  EmailAddressValue */
    private $defaultFrom;

    /** @var  string */
    private $siteName;

    public function __construct(
        TicketMailingRepository $ticketMailingRepository,
        \Swift_Mailer $swiftMailer,
        MailTemplateFactory $mailTemplateFactory,
        EmailAddressValue $defaultFrom,
        string $siteName
    )
    {
        $this->ticketMailingRepository = $ticketMailingRepository;
        $this->swiftMailer = $swiftMailer;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->defaultFrom = $defaultFrom;
        $this->siteName = $siteName;
    }

    public function addMailing(Ticket $ticket, string $type, ?TicketUpdate $ticketUpdate = null): void
    {
        $this->ticketMailingRepository->createTicketMailing($ticket, $type, $ticketUpdate);
    }

    public function send(TicketMailing $ticketMailing): void
    {
        $ticket = $ticketMailing->getTicket();
        $mail = $this->mailTemplateFactory->getMail(
            $ticket,
            $ticketMailing->getTicketUpdate(),
            $ticketMailing->getType()
        );

        $message = $this->createMessage($ticket, $mail);
        $sent = $this->swiftMailer->send($message);
        if ($sent === 0) {
            throw new \RuntimeException('Failed to send mail');
        }

        $this->ticketMailingRepository->setSent($ticketMailing);
    }

    private function createMessage(Ticket $ticket, MailTemplateInterface $mail): \Swift_Message
    {
        $fromMail = $ticket->getDepartment()
            ? $ticket->getDepartment()->getEmail()->toString()
            : $this->defaultFrom->toString();
        $fromName = $ticket->getDepartment()
            ? $ticket->getDepartment()->getName() . ' - ' . $this->siteName
            : $this->siteName;

        return \Swift_Message::newInstance($mail->getSubject())
            ->setFrom($fromMail, $fromName)
            ->setTo($ticket->getEmail()->toString())
            ->setBody($mail->render(), 'text/html');
    }
}
