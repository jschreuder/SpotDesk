<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\SendMailService;

interface MailTemplateFactoryInterface
{
    public function getMailTemplate(string $type) : MailTemplateInterface;
}