<?php
/**
 * Created by PhpStorm.
 * User: jschreuder
 * Date: 25-4-17
 * Time: 22:43
 */

namespace jschreuder\SpotDesk\Service\SendMailService;

use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketUpdate;

interface MailTemplateFactoryInterface
{
    public function getMail(Ticket $ticket, ?TicketUpdate $ticketUpdate, string $type): MailTemplateInterface;
}