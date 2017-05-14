<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Repository\StatusRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StatusRepositorySpec extends ObjectBehavior
{
    /** @var  \PDO */
    private $db;

    public function let(\PDO $db) : void
    {
        $this->beConstructedWith(
            $this->db = $db
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(StatusRepository::class);
    }

    public function it_can_get_a_status(\PDOStatement $statement) : void
    {
        $this->db->prepare(new Argument\Token\StringContainsToken('SELECT'))->willReturn($statement);
        $statement->execute()->shouldBeCalled();
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn(
            ['status' => 'Open', 'type' => 'open'],
            ['status' => 'Closed', 'type' => 'closed'],
            ['status' => 'Awaiting client', 'type' => 'paused'],
            null
        );

        $status = $this->getStatus('Open');
        $status->getName()->shouldBe('Open');
        $status->getType()->toString()->shouldBe('open');

        $status = $this->getStatus('Awaiting client');
        $status->getName()->shouldBe('Awaiting client');
        $status->getType()->toString()->shouldBe('paused');
    }

    public function it_throws_exception_on_invalid_status(\PDOStatement $statement) : void
    {
        $this->db->prepare(new Argument\Token\StringContainsToken('SELECT'))->willReturn($statement);
        $statement->execute()->shouldBeCalled();
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn(null);

        $this->shouldThrow(\OutOfBoundsException::class)->duringGetStatus('Open');
    }
}
