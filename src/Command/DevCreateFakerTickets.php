<?php declare(strict_types=1);

namespace jschreuder\SpotDesk\Command;

use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use jschreuder\SpotDesk\Collection\TicketUpdateCollection;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\Status;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Entity\TicketUpdate;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\StatusRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DevCreateFakerTickets extends Command
{
    /** @var  TicketRepository */
    private $ticketRepository;

    /** @var  DepartmentRepository */
    private $departmentRepository;

    /** @var  StatusRepository */
    private $statusRepository;

    /** @var  FakerGenerator */
    private $faker;

    public function __construct(
        TicketRepository $ticketRepository,
        DepartmentRepository $departmentRepository,
        StatusRepository $statusRepository,
        FakerGenerator $faker = null
    )
    {
        $this->ticketRepository = $ticketRepository;
        $this->departmentRepository = $departmentRepository;
        $this->statusRepository = $statusRepository;
        $this->faker = $faker ?? FakerFactory::create();
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('dev:create-faker-tickets')
            ->addArgument(
                'admin-email',
                InputArgument::REQUIRED,
                'E-mailaddress of the responding admin'
            )
            ->addArgument(
                'number',
                InputArgument::OPTIONAL,
                'The number of tickets to create',
                1
            )
            ->addArgument(
                'department',
                InputArgument::OPTIONAL,
                'UUID of the department to add tickets to',
                null
            );
    }

    private function getFaker(): FakerGenerator
    {
        return $this->faker;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $admin = EmailAddressValue::get($input->getArgument('admin-email'));
        $department = $input->getArgument('department')
            ? $this->departmentRepository->getDepartment(Uuid::fromString($input->getArgument('department')))
            : null;
        $repeat = intval($input->getArgument('number'));

        for ($idx = 0; $idx < $repeat; $idx++) {
            $ticket = $this->createTicket($department);
            $replies = random_int(0, 10);
            if ($replies > 0) {
                $ticketUpdates = $this->createReplies($ticket, $admin, $replies)->toArray();
                /** @var  TicketUpdate $lastUpdate */
                $lastUpdate = end($ticketUpdates);

                if ($lastUpdate->getEmail() === $admin) {
                    $this->ticketRepository->updateTicketStatus(
                        $ticket,
                        $this->statusRepository->getStatus(Status::STATUS_AWAITING_CLIENT)
                    );
                } else {
                    $this->ticketRepository->updateTicketStatus(
                        $ticket,
                        $this->statusRepository->getStatus(Status::STATUS_OPEN)
                    );
                }
            }
        }
    }

    private function createTicket(?Department $department): Ticket
    {
        $faker = $this->getFaker();
        return $this->ticketRepository->createTicket(
            EmailAddressValue::get($faker->email),
            $faker->sentence(random_int(2, 8)),
            $faker->paragraph(random_int(1, 5)),
            $department,
            $faker->dateTimeThisMonth
        );
    }

    private function createReplies(Ticket $ticket, EmailAddressValue $admin, int $repeat): TicketUpdateCollection
    {
        $faker = $this->getFaker();
        $ticketUpdates = new TicketUpdateCollection();

        for ($idx = 0; $idx < $repeat; $idx++) {
            $from = random_int(0, 1) === 1 ? $admin : $ticket->getEmail();
            $update = $this->ticketRepository->createTicketUpdate(
                $ticket,
                $from,
                $faker->paragraph(random_int(1, 3)),
                ($from === $admin && random_int(0, 2) === 2),
                new \DateTimeImmutable('@' . ($ticket->getCreatedAt()->getTimestamp() + random_int(300, 259200)))
            );
            $ticketUpdates->push($update);
        }
        return $ticketUpdates;
    }
}
