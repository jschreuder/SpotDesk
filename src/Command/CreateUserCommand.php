<?php declare(strict_types = 1);

namespace jschreuder\SpotDesk\Command;

use jschreuder\SpotDesk\Service\AuthenticationService\AuthenticationServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends Command
{
    /** @var  AuthenticationServiceInterface */
    private $authenticationService;

    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('user:create')
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'The user\'s e-mail address as its username'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED
            )
            ->addArgument(
                'display_name',
                InputArgument::REQUIRED,
                'The name for the user which is displayed on the frontend'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->authenticationService->createUser(
            $input->getArgument('email'),
            $input->getArgument('display_name'),
            $input->getArgument('password')
        );
    }
}