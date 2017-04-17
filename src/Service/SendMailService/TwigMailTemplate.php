<?php declare(strict_types=1);


namespace jschreuder\SpotDesk\Service\SendMailService;

class TwigMailTemplate implements MailTemplateInterface
{
    /** @var  \Twig_Environment */
    private $twig;

    /** @var  string */
    private $template;

    /** @var  string */
    private $subject;

    /** @var  array */
    private $variables;

    public function __construct(\Twig_Environment $twig, string $template, string $subject, array $variables = [])
    {
        $this->twig = $twig;
        $this->template = $template;
        $this->subject = $subject;
        $this->variables = $variables;
    }

    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function render(): string
    {
        return $this->twig->render($this->template, $this->variables);
    }
}
