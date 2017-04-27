<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Service\SendMailService;

use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketMailing;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use jschreuder\SpotDesk\Repository\TicketMailingRepository;
use jschreuder\SpotDesk\Service\SendMailService\MailTemplateFactory;
use jschreuder\SpotDesk\Service\SendMailService\MailTemplateFactoryInterface;
use jschreuder\SpotDesk\Service\SendMailService\MailTemplateInterface;
use jschreuder\SpotDesk\Service\SendMailService\SmtpSendMailService;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class SmtpSendMailServiceSpec extends ObjectBehavior
{
    /** @var  TicketMailingRepository */
    private $ticketMailingRepository;

    /** @var  \Swift_Mailer */
    private $swiftMailer;

    /** @var  MailTemplateFactory */
    private $mailTemplateFactory;

    /** @var  EmailAddressValue */
    private $defaultFrom;

    /** @var  string */
    private $siteName;

    public function let(
        \Swift_Mailer $swiftMailer,
        MailTemplateFactoryInterface $templateFactory,
        TicketMailingRepository $repository
    ) : void {
        $this->beConstructedWith(
            $this->ticketMailingRepository = $repository,
            $this->swiftMailer = $swiftMailer,
            $this->mailTemplateFactory = $templateFactory,
            $this->defaultFrom = EmailAddressValue::get('mail@test.dev'),
            $this->siteName = 'SiteName'
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(SmtpSendMailService::class);
    }

    public function it_can_create_a_mailing(Ticket $ticket, TicketUpdate $ticketUpdate) : void
    {
        $type = 'mailing.type';
        $this->ticketMailingRepository->createTicketMailing($ticket, $type, $ticketUpdate);
        $this->addTicketMailing($ticket, $type, $ticketUpdate);
    }

    public function it_can_create_a_mailing_without_update(Ticket $ticket) : void
    {
        $type = 'mailing.type';
        $this->ticketMailingRepository->createTicketMailing($ticket, $type, null);
        $this->addTicketMailing($ticket, $type);
    }

    public function it_can_send_mailings(
        TicketMailing $ticketMailing,
        Ticket $ticket,
        TicketUpdate $ticketUpdate,
        MailTemplateInterface $mailTemplate
    ) : void
    {
        $type = 'mailing.type';
        $ticketMailing->getTicket()->willReturn($ticket);
        $ticketMailing->getTicketUpdate()->willReturn($ticketUpdate);
        $ticketMailing->getType()->willReturn($type);

        $ticketId = Uuid::uuid4();
        $ticketEmail = 'ticker@user.email';
        $ticketSubject = 'Some subject';
        $ticket->getId()->willReturn($ticketId);
        $ticket->getSubject()->willReturn($ticketSubject);
        $ticket->getDepartment()->willReturn(null);
        $ticket->getEmail()->willReturn(EmailAddressValue::get($ticketEmail));

        $this->mailTemplateFactory->getMailTemplate($type)->willReturn($mailTemplate);
        $mailTemplate->render(['ticket' => $ticket, 'ticket_update' => $ticketUpdate])->willReturn('mail contents');

        $this->swiftMailer->send(new Argument\Token\TypeToken(\Swift_Message::class))->willReturn(1);

        $this->ticketMailingRepository->setSent($ticketMailing)->shouldBeCalled();

        $this->send($ticketMailing);
    }

    public function it_can_send_mailings_with_department(
        TicketMailing $ticketMailing,
        Ticket $ticket,
        Department $department,
        TicketUpdate $ticketUpdate,
        MailTemplateInterface $mailTemplate
    ) : void
    {
        $type = 'mailing.type';
        $ticketMailing->getTicket()->willReturn($ticket);
        $ticketMailing->getTicketUpdate()->willReturn($ticketUpdate);
        $ticketMailing->getType()->willReturn($type);

        $ticketId = Uuid::uuid4();
        $ticketEmail = 'ticker@user.email';
        $ticketSubject = 'Some subject';
        $ticket->getId()->willReturn($ticketId);
        $ticket->getSubject()->willReturn($ticketSubject);
        $ticket->getDepartment()->willReturn($department);
        $ticket->getEmail()->willReturn(EmailAddressValue::get($ticketEmail));

        $departmentName = 'Dep.Art.Ment';
        $departmentMail = 'department@company.biz';
        $department->getName()->willReturn($departmentName);
        $department->getEmail()->willReturn(EmailAddressValue::get($departmentMail));

        $this->mailTemplateFactory->getMailTemplate($type)->willReturn($mailTemplate);
        $mailTemplate->render(['ticket' => $ticket, 'ticket_update' => $ticketUpdate])->willReturn('mail contents');

        $this->swiftMailer->send(new Argument\Token\TypeToken(\Swift_Message::class))->willReturn(1);

        $this->ticketMailingRepository->setSent($ticketMailing)->shouldBeCalled();

        $this->send($ticketMailing);
    }

    public function it_errors_on_failure_to_send_mail(
        TicketMailing $ticketMailing,
        Ticket $ticket,
        TicketUpdate $ticketUpdate,
        MailTemplateInterface $mailTemplate
    ) : void
    {
        $type = 'mailing.type';
        $ticketMailing->getTicket()->willReturn($ticket);
        $ticketMailing->getTicketUpdate()->willReturn($ticketUpdate);
        $ticketMailing->getType()->willReturn($type);

        $ticketId = Uuid::uuid4();
        $ticketEmail = 'ticker@user.email';
        $ticketSubject = 'Some subject';
        $ticket->getId()->willReturn($ticketId);
        $ticket->getSubject()->willReturn($ticketSubject);
        $ticket->getDepartment()->willReturn(null);
        $ticket->getEmail()->willReturn(EmailAddressValue::get($ticketEmail));

        $this->mailTemplateFactory->getMailTemplate($type)->willReturn($mailTemplate);
        $mailTemplate->render(['ticket' => $ticket, 'ticket_update' => $ticketUpdate])->willReturn('mail contents');

        $this->swiftMailer->send(new Argument\Token\TypeToken(\Swift_Message::class))->willReturn(0);

        $this->ticketMailingRepository->setSent($ticketMailing)->shouldNotBeCalled();

        $this->shouldThrow(\RuntimeException::class)->duringSend($ticketMailing);
    }
}
