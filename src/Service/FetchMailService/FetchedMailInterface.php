<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\FetchMailService;

use jschreuder\SpotDesk\Value\EmailAddressValue;

interface FetchedMailInterface
{
    public function getId() : mixed;
    public function getFromEmailAddres() : EmailAddressValue;
    public function getSubject() : string;
    public function getTextBody() : ?string;
    public function getHtmlBody() : ?string;
    public function getSentDate() : \DateTimeInterface;
}