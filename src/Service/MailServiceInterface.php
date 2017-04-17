<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Service;

use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketMailing;

interface MailServiceInterface
{
    const TYPE_NEW_TICKET = 'ticket.new';
    const TYPE_UPDATE_TICKET = 'ticket.update';

    public function addMailing(Ticket $ticket, string $type): void;

    public function send(TicketMailing $ticketMailing): void;
}
