<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Service\SendMailService;

use jschreuder\SpotDesk\Service\SendMailService\MailTemplateFactory;
use jschreuder\SpotDesk\Service\SendMailService\MailTemplateInterface;
use jschreuder\SpotDesk\Service\SendMailService\SendMailServiceInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MailTemplateFactorySpec extends ObjectBehavior
{
    /** @var  MailTemplateInterface */
    private $newTicketMailTemplate;

    /** @var  MailTemplateInterface */
    private $ticketUpdateMailTemplate;

    public function let(MailTemplateInterface $newTicket, MailTemplateInterface $ticketUpdate)
    {
        $this->beConstructedWith(
            $this->newTicketMailTemplate = $newTicket,
            $this->ticketUpdateMailTemplate = $ticketUpdate
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MailTemplateFactory::class);
    }

    public function it_can_create_a_mail_template()
    {
        $this->getMailTemplate(SendMailServiceInterface::TYPE_NEW_TICKET)
            ->shouldBeLike($this->newTicketMailTemplate);
        $this->getMailTemplate(SendMailServiceInterface::TYPE_UPDATE_TICKET)
            ->shouldBeLike($this->ticketUpdateMailTemplate);
    }

    public function it_errors_on_unknown_template()
    {
        $this->shouldThrow(\OutOfBoundsException::class)->duringGetMailTemplate('nonsense');
    }
}
