<?php declare(strict_types = 1);


namespace jschreuder\SpotDesk\Service\SendMailService;

final class TwigMailTemplate implements MailTemplateInterface
{
    /** @var  \Twig_Environment */
    private $twig;

    /** @var  string */
    private $template;

    public function __construct(\Twig_Environment $twig, string $template)
    {
        $this->twig = $twig;
        $this->template = $template;
    }

    public function render(array $context) : string
    {
        return $this->twig->render($this->template, $context);
    }
}
