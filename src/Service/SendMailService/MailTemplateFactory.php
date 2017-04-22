<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Service\SendMailService;

use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use jschreuder\SpotDesk\Service\MailServiceInterface;

class MailTemplateFactory
{
    /** @var  MailTemplateInterface */
    private $newTicket;

    /** @var  MailTemplateInterface */
    private $updateTicket;

    public function __construct(MailTemplateInterface $newTicket, MailTemplateInterface $updateTicket)
    {
        $this->newTicket = $newTicket;
        $this->updateTicket = $updateTicket;
    }

    public function getMail(Ticket $ticket, ?TicketUpdate $ticketUpdate, string $type): MailTemplateInterface
    {
        switch ($type) {
            case MailServiceInterface::TYPE_NEW_TICKET:
                $mail = clone $this->newTicket;
                break;
            case MailServiceInterface::TYPE_UPDATE_TICKET:
                $mail = clone $this->updateTicket;
                break;
            default:
                throw new \InvalidArgumentException('Invalid mail type: ' . $type);
        }
        $mail->setVariables(['ticket' => $ticket, 'ticketUpdate' => $ticketUpdate]);
        return $mail;
    }
}
