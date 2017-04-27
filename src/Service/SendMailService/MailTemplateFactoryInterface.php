<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\SendMailService;

use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketUpdate;

interface MailTemplateFactoryInterface
{
    public function getMailTemplate(string $type) : MailTemplateInterface;
}