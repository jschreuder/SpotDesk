<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Service\SendMailService;

interface MailTemplateInterface
{
    public function setVariables(array $variables): void;

    public function getSubject(): string;

    public function render(): string;
}
