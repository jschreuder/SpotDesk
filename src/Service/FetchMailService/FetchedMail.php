<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\FetchMailService;

use jschreuder\SpotDesk\Value\EmailAddressValue;

final class FetchedMail extends FetchedMailInterface
{
    public function __construct(
        private mixed $id,
        private EmailAddressValue $fromEmailAddress,
        private string $subject,
        private ?string $textBody,
        private ?string $htmlBody,
        private \DateTimeInterface $sentDate
    )
    {
    }

    public function getId() : mixed
    {
        return $this->id;
    }

    public function getFromEmailAddres() : EmailAddressValue
    {
        return $this->fromEmailAddress;
    }

    public function getSubject() : string
    {
        return $this->subject;
    }

    public function getTextBody() : ?string
    {
        return $this->textBody;
    }

    public function getHtmlBody() : ?string
    {
        return $this->htmlBody;
    }

    public function getSentDate() : \DateTimeInterface
    {
        return $this->sentDate;
    }
}