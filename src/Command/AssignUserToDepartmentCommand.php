<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Command;

use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignUserToDepartmentCommand extends Command
{
    /** @var  UserRepository */
    private $userRepository;

    /** @var  DepartmentRepository */
    private $departmentRepository;

    public function __construct(UserRepository $userRepository, DepartmentRepository $departmentRepository)
    {
        $this->userRepository = $userRepository;
        $this->departmentRepository = $departmentRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('user:assign-department')
            ->addArgument('user', InputArgument::REQUIRED, 'User\'s e-mail address')
            ->addArgument('department_id', InputArgument::REQUIRED, 'Department UUID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->userRepository->getUserByEmail(EmailAddressValue::get($input->getArgument('user')));
        $department = $this->departmentRepository->getDepartment(
            Uuid::fromString($input->getArgument('department_id'))
        );

        $this->userRepository->assignUserToDepartment($user, $department);
        $output->writeln($user->getEmail()->toString() . ' has been assigned to ' . $department->getName());
    }
}
