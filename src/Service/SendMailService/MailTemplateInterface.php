<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Service\SendMailService;

interface MailTemplateInterface
{
    public function render(array $context) : string;
}
