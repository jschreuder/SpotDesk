<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Service\SendMailService;

use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketMailing;
use jschreuder\SpotDesk\Repository\TicketMailingRepository;
use jschreuder\SpotDesk\Service\MailServiceInterface;

class SmtpSendMailService implements MailServiceInterface
{
    /** @var  \Swift_Mailer */
    private $swiftMailer;

    /** @var  MailTemplateFactory */
    private $mailTemplateFactory;

    /** @var  TicketMailingRepository */
    private $ticketMailingRepository;

    public function __construct(
        TicketMailingRepository $ticketMailingRepository,
        \Swift_Mailer $swiftMailer,
        MailTemplateFactory $mailTemplateFactory
    )
    {
        $this->ticketMailingRepository = $ticketMailingRepository;
        $this->swiftMailer = $swiftMailer;
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    public function addMailing(Ticket $ticket, string $type): void
    {
        $this->ticketMailingRepository->createTicketMailing($ticket, $type);
    }

    public function send(TicketMailing $ticketMailing): void
    {
        $ticket = $ticketMailing->getTicket();
        $mail = $this->mailTemplateFactory->getMail($ticket, $ticketMailing->getType());

        $message = \Swift_Message::newInstance($mail->getSubject())
            ->setFrom([$ticket->getDepartment()->getName() => $ticket->getDepartment()->getEmail()->toString()])
            ->setTo([$ticket->getEmail()->toString()])
            ->setBody($mail->render());

        $sent = $this->swiftMailer->send($message);
        if ($sent === 0) {
            throw new \RuntimeException('Failed to send mail');
        }
        $this->ticketMailingRepository->setSent($ticketMailing);
    }
}
