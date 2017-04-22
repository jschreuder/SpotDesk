<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DevCron extends Command
{
    public function configure()
    {
        $this->setName('dev:cron')
            ->addArgument(
                'check-timeout',
                InputArgument::OPTIONAL,
                'Number of seconds between execution',
                60
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $checkTimeout = intval($input->getArgument('check-timeout'));
        if ($checkTimeout <= 0) {
            throw new \InvalidArgumentException('Checking timeout must be greater then 0.');
        }

        while (true) {
            $output->writeln('[' . date('Y-m-d H:i:s') . '] checking mail');
            `./console mail:check`;
            $output->writeln('[' . date('Y-m-d H:i:s') . '] sending mail');
            `./console mail:send`;
            $output->writeln('[' . date('Y-m-d H:i:s') . '] done');
            $output->writeln('');

            sleep($checkTimeout);
        }
    }
}
