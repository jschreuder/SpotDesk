<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Command;

use jschreuder\SpotDesk\Repository\TicketMailingRepository;
use jschreuder\SpotDesk\Service\SendMailService\SendMailServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendMailingsCommand extends Command
{
    /** @var  TicketMailingRepository */
    private $ticketMailingsRepository;

    /** @var  SendMailServiceInterface */
    private $mailService;

    public function __construct(
        TicketMailingRepository $ticketMailingsRepository,
        SendMailServiceInterface $mailService
    ) {
        $this->ticketMailingsRepository = $ticketMailingsRepository;
        $this->mailService = $mailService;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('mail:send');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ticketMailingCollection = $this->ticketMailingsRepository->getUnsent();

        foreach ($ticketMailingCollection as $ticketMailing) {
            $this->mailService->send($ticketMailing);
        }
    }
}
