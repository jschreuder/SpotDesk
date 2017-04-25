<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\SendMailService;

use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketMailing;
use jschreuder\SpotDesk\Entity\TicketUpdate;

interface SendMailServiceInterface
{
    const TYPE_NEW_TICKET = 'ticket.new';
    const TYPE_UPDATE_TICKET = 'ticket.update';

    public function addMailing(Ticket $ticket, string $type, ?TicketUpdate $ticketUpdate = null): void;

    public function send(TicketMailing $ticketMailing): void;
}
