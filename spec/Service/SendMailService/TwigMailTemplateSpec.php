<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Service\SendMailService;

use jschreuder\SpotDesk\Service\SendMailService\TwigMailTemplate;
use PhpSpec\ObjectBehavior;
use Twig\Environment;

class TwigMailTemplateSpec extends ObjectBehavior
{
    /** @var  Environment */
    private $twig;

    /** @var  string */
    private $template;

    /** @var  string */
    private $subject;

    public function let(Environment $twig) : void
    {
        $this->beConstructedWith(
            $this->twig = $twig,
            $this->template = 'template.twig',
            $this->subject = 'Some subject'
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(TwigMailTemplate::class);
    }

    public function it_can_render() : void
    {
        $rendered = 'rendered content';
        $context = ['var' => 'value'];
        $this->twig->render($this->template, $context)->willReturn($rendered);

        $this->render($context)->shouldReturn($rendered);
    }
}
