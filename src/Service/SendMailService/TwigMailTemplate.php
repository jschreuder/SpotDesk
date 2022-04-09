<?php declare(strict_types = 1);


namespace jschreuder\SpotDesk\Service\SendMailService;

use Twig\Environment;

final class TwigMailTemplate implements MailTemplateInterface
{
    public function __construct(
        private Environment $twig, 
        private string $template
    )
    {
    }

    public function render(array $context) : string
    {
        return $this->twig->render($this->template, $context);
    }
}
