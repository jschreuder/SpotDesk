<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\FetchMailService;

use Generator;

interface MailBoxConnectionInterface
{
    public function fetchMail() : Generator;

    public function markMailAsRead(FetchedMailInterface $fetchedMail) : void;
}
