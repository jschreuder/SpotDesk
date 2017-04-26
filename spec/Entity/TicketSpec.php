<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\Status;
use jschreuder\SpotDesk\Entity\Ticket;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\UuidInterface;

class TicketSpec extends ObjectBehavior
{
    /** @var  UuidInterface */
    private $id;

    /** @var  string */
    private $secretKey;

    /** @var  EmailAddressValue */
    private $email;

    /** @var  string */
    private $subject;

    /** @var  string */
    private $message;

    /** @var  \DateTimeInterface */
    private $createdAt;

    /** @var  int */
    private $updates;

    /** @var  \DateTimeInterface */
    private $lastUpdate;

    /** @var  Status */
    private $status;

    /** @var  ?Department */
    private $department;

    public function let(UuidInterface $id, Status $status, Department $department)
    {
        $this->beConstructedWith(
            $this->id = $id,
            $this->secretKey = sha1(uniqid()),
            $this->email = EmailAddressValue::get('mail@test.dev'),
            $this->subject = 'Some subject',
            $this->message = 'Just a simple message, nothing special.',
            $this->createdAt = new \DateTimeImmutable('-2 months'),
            $this->updates = 2,
            $this->lastUpdate = new \DateTimeImmutable('-1 week'),
            $this->status = $status,
            $this->department = $department
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Ticket::class);
    }

    public function it_can_access_its_properties()
    {
        $this->getId()->shouldReturn($this->id);
        $this->getSecretKey()->shouldReturn($this->secretKey);
        $this->getEmail()->shouldReturn($this->email);
        $this->getSubject()->shouldReturn($this->subject);
        $this->getMessage()->shouldReturn($this->message);
        $this->getCreatedAt()->shouldReturn($this->createdAt);
        $this->getUpdates()->shouldReturn($this->updates);
        $this->getLastUpdate()->shouldReturn($this->lastUpdate);
        $this->getStatus()->shouldReturn($this->status);
        $this->getDepartment()->shouldReturn($this->department);
    }

    public function it_can_instantiate_without_department()
    {
        $this->beConstructedWith(
            $this->id,
            $this->secretKey,
            $this->email,
            $this->subject,
            $this->message,
            $this->createdAt,
            $this->updates,
            $this->lastUpdate,
            $this->status,
            null
        );
        $this->getDepartment()->shouldReturn(null);
    }

    public function it_can_change_some_properties(Status $status, Department $department)
    {
        $updates = 42;
        $this->setUpdates($updates);
        $this->getUpdates()->shouldBe($updates);

        $lastUpdate = new \DateTimeImmutable();
        $this->setLastUpdate($lastUpdate);
        $this->getLastUpdate()->shouldReturn($lastUpdate);

        $this->setStatus($status);
        $this->getStatus()->shouldReturn($status);

        $this->setDepartment($department);
        $this->getDepartment()->shouldReturn($department);

        $this->setDepartment(null);
        $this->getDepartment()->shouldReturn(null);
    }
}
