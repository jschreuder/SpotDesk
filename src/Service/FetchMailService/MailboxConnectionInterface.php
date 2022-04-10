<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\FetchMailService;

interface MailBoxConnectionInterface
{
    public function fetchMailIds() : array;

    public function fetchMailById(int $mailId) : FetchedMailInterface;

    public function markMailAsRead(int $mailId) : void;
}
