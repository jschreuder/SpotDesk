<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\SendMailService;

use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketUpdate;

final class MailTemplateFactory implements MailTemplateFactoryInterface
{
    /** @var  MailTemplateInterface[] */
    private $templates;

    public function __construct(MailTemplateInterface $newTicket, MailTemplateInterface $updateTicket)
    {
        $this->templates = [
            SendMailServiceInterface::TYPE_NEW_TICKET => $newTicket,
            SendMailServiceInterface::TYPE_UPDATE_TICKET => $updateTicket,
        ];
    }

    public function getMailTemplate(string $type) : MailTemplateInterface
    {
        if (!isset($this->templates[$type])) {
            throw new \OutOfBoundsException('No such mail template: ' . $type);
        }
        return clone $this->templates[$type];
    }
}
