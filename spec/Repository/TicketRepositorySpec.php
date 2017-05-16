<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\Status;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Repository\StatusRepository;
use jschreuder\SpotDesk\Repository\TicketRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class TicketRepositorySpec extends ObjectBehavior
{
    /** @var  \PDO */
    private $db;

    /** @var  StatusRepository */
    private $statusRepository;

    /** @var  DepartmentRepository */
    private $departmentRepository;

    public function let(\PDO $db, StatusRepository $statusRepository, DepartmentRepository $departmentRepository) : void
    {
        $this->beConstructedWith(
            $this->db = $db,
            $this->statusRepository = $statusRepository,
            $this->departmentRepository = $departmentRepository
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(TicketRepository::class);
    }

    public function it_can_create_a_ticket(\PDOStatement $statement, Department $department, Status $status) : void
    {
        $department->getId()->willReturn($departmentId = Uuid::uuid4());
        $this->statusRepository->getStatus('new')->willReturn($status);
        $status->getName()->willReturn('new');

        $this->db->prepare(new Argument\Token\StringContainsToken('INSERT'))->willReturn($statement);
        $statement->execute(new Argument\Token\TypeToken('array'))->shouldBeCalled();

        $ticket = $this->createTicket(
            $email = EmailAddressValue::get('mail@test.dev'),
            $subject = 'Subject',
            $message = 'Message',
            $department,
            $createdAt = new \DateTimeImmutable()
        );
        $ticket->getId()->shouldHaveType(UuidInterface::class);
        $ticket->getSecretKey()->shouldBeString();
        $ticket->getEmail()->shouldBe($email);
        $ticket->getSubject()->shouldBe($subject);
        $ticket->getMessage()->shouldBe($message);
        $ticket->getCreatedAt()->shouldBe($createdAt);
        $ticket->getUpdates()->shouldBe(0);
        $ticket->getLastUpdate()->shouldHaveType(\DateTimeInterface::class);
        $ticket->getStatus()->shouldBe($status);
        $ticket->getDepartment()->shouldBe($department);
    }

    public function it_can_get_a_ticket(\PDOStatement $statement, Department $department, Status $status) : void
    {
        $ticketId = Uuid::uuid4();
        $secretKey = random_bytes(127);
        $email = EmailAddressValue::get('mail@test.dev');
        $subject = 'Subject';
        $message = 'Message';
        $createdAt = new \DateTimeImmutable('-1 week');
        $updates = 5;
        $lastUpdate = new \DateTimeImmutable('-1 day');
        $status->getName()->willReturn($statusName = 'open');
        $department->getId()->willReturn($departmentId = Uuid::uuid4());

        $this->statusRepository->getStatus($statusName)->willReturn($status);
        $this->departmentRepository->getDepartment($departmentId)->willReturn($department);

        $this->db->prepare(new Argument\Token\StringContainsToken('SELECT'))->willReturn($statement);
        $statement->execute(['ticket_id' => $ticketId->getBytes()])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn([
            'ticket_id' => $ticketId->getBytes(),
            'secret_key' => $secretKey,
            'email' => $email->toString(),
            'subject' => $subject,
            'message' => $message,
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
            'updates' => $updates,
            'last_update' => $lastUpdate->format('Y-m-d H:i:s'),
            'status' => $statusName,
            'department_id' => $departmentId->getBytes(),
        ]);

        $ticket = $this->getTicket($ticketId);
        $ticket->shouldHaveType(Ticket::class);
        $ticket->getId()->equals($ticketId)->shouldBe(true);
        $ticket->getSecretKey()->shouldBe($secretKey);
        $ticket->getEmail()->shouldBeLike($email);
        $ticket->getSubject()->shouldBe($subject);
        $ticket->getMessage()->shouldBe($message);
        $ticket->getCreatedAt()->format('Y-m-d H:i:s')->shouldBe($createdAt->format('Y-m-d H:i:s'));
        $ticket->getUpdates()->shouldBe($updates);
        $ticket->getLastUpdate()->format('Y-m-d H:i:s')->shouldBe($lastUpdate->format('Y-m-d H:i:s'));
        $ticket->getStatus()->shouldBe($status);
        $ticket->getDepartment()->shouldBe($department);
    }

    public function it_will_error_when_getting_ticket_failed(\PDOStatement $statement) : void
    {
        $ticketId = Uuid::uuid4();
        $this->db->prepare(new Argument\Token\StringContainsToken('SELECT'))->willReturn($statement);
        $statement->execute(['ticket_id' => $ticketId->getBytes()])->shouldBeCalled();
        $statement->rowCount()->willReturn(0);
        $this->shouldThrow(\RuntimeException::class)->duringGetTicket($ticketId);
    }
}
