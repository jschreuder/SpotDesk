<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\DepartmentCollection;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\DepartmentRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DepartmentRepositorySpec extends ObjectBehavior
{
    /** @var  \PDO */
    private $db;

    public function let(\PDO $db)
    {
        $this->beConstructedWith(
            $this->db = $db
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(DepartmentRepository::class);
    }

    public function it_can_create_a_department(Department $parent, \PDOStatement $statement) : void
    {
        $parentId = Uuid::uuid4();
        $parent->getId()->willReturn($parentId);
        $this->db->prepare(new Argument\Token\StringContainsToken('INSERT'))->willReturn($statement);
        $statement->execute(new Argument\Token\TypeToken('array'));

        $department = $this->createDepartment(
            $name = 'Department',
            $parent,
            $email = EmailAddressValue::get('one@two.three')
        );
        $department->shouldHaveType(Department::class);
        $department->getId()->shouldHaveType(UuidInterface::class);
        $department->getName()->shouldBe($name);
        $department->getParent()->shouldBe($parent);
        $department->getEmail()->shouldBe($email);
    }

    public function it_can_get_all_departments(\PDOStatement $statement) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();

        $this->db->query(new Argument\Token\StringContainsToken('SELECT'))->willReturn($statement);
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn(
            ['department_id' => $uuid1->getBytes(), 'name' => 'One', 'parent_id' => null, 'email' => 'one@dom.dev'],
            ['department_id' => $uuid2->getBytes(), 'name' => 'Two', 'parent_id' => $uuid1->getBytes(), 'email' => 'two@dom.dev'],
            ['department_id' => $uuid3->getBytes(), 'name' => 'Three', 'parent_id' => null, 'email' => 'three@dom.dev'],
            null
        );

        $departments = $this->getDepartments();
        $departments->shouldHaveType(DepartmentCollection::class);
        $departments[$uuid1->toString()]->getName()->shouldBe('One');
        $departments[$uuid1->toString()]->getParent()->shouldBe(null);
        $departments[$uuid1->toString()]->getEmail()->toString()->shouldBe('one@dom.dev');
        $departments[$uuid2->toString()]->getName()->shouldBe('Two');
        $departments[$uuid2->toString()]->getParent()->getId()->equals($uuid1)->shouldBe(true);
        $departments[$uuid2->toString()]->getEmail()->toString()->shouldBe('two@dom.dev');
        $departments[$uuid3->toString()]->getName()->shouldBe('Three');
        $departments[$uuid3->toString()]->getParent()->shouldBe(null);
        $departments[$uuid3->toString()]->getEmail()->toString()->shouldBe('three@dom.dev');
    }

    public function it_can_get_one_department(\PDOStatement $statement) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();

        $this->db->query(new Argument\Token\StringContainsToken('SELECT'))->willReturn($statement);
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn(
            ['department_id' => $uuid1->getBytes(), 'name' => 'One', 'parent_id' => null, 'email' => 'one@dom.dev'],
            ['department_id' => $uuid2->getBytes(), 'name' => 'Two', 'parent_id' => $uuid1->getBytes(), 'email' => 'two@dom.dev'],
            ['department_id' => $uuid3->getBytes(), 'name' => 'Three', 'parent_id' => null, 'email' => 'three@dom.dev'],
            null
        );

        $department = $this->getDepartment($uuid1);
        $department->shouldHaveType(Department::class);
        $department->getId()->equals($uuid1)->shouldBe(true);
    }

    public function it_errors_when_getting_non_existent_department(\PDOStatement $statement) : void
    {
        $this->db->query(new Argument\Token\StringContainsToken('SELECT'))->willReturn($statement);
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn(null);

        $this->shouldThrow(\OutOfBoundsException::class)->duringGetDepartment(Uuid::uuid4());
    }

    public function it_can_get_a_users_departments(
        \PDOStatement $statement1,
        \PDOStatement $statement2,
        User $user
    ) : void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $uuid3 = Uuid::uuid4();
        $userMail = EmailAddressValue::get('user@mail.dev');
        $user->getEmail()->willReturn($userMail);

        $this->db->query(new Argument\Token\StringContainsToken('SELECT'))->willReturn($statement1);
        $statement1->fetch(\PDO::FETCH_ASSOC)->willReturn(
            ['department_id' => $uuid1->getBytes(), 'name' => 'One', 'parent_id' => null, 'email' => 'one@dom.dev'],
            ['department_id' => $uuid2->getBytes(), 'name' => 'Two', 'parent_id' => $uuid1->getBytes(), 'email' => 'two@dom.dev'],
            ['department_id' => $uuid3->getBytes(), 'name' => 'Three', 'parent_id' => null, 'email' => 'three@dom.dev'],
            null
        );

        $this->db->prepare(new Argument\Token\StringContainsToken('SELECT'))->willReturn($statement2);
        $statement2->execute(['email' => $userMail->toString()])->shouldBeCalled();
        $statement2->fetch(\PDO::FETCH_ASSOC)->willReturn(
            ['department_id' => $uuid2->getBytes(), 'name' => 'Two', 'parent_id' => $uuid1->getBytes(), 'email' => 'two@dom.dev'],
            ['department_id' => $uuid3->getBytes(), 'name' => 'Three', 'parent_id' => null, 'email' => 'three@dom.dev'],
            null
        );

        $departments = $this->getDepartmentsForUser($user);
        $departments->shouldHaveType(DepartmentCollection::class);
        $departments->offsetExists($uuid1->toString())->shouldBe(false);
        $departments[$uuid2->toString()]->getName()->shouldBe('Two');
        $departments[$uuid2->toString()]->getParent()->getId()->equals($uuid1)->shouldBe(true);
        $departments[$uuid2->toString()]->getEmail()->toString()->shouldBe('two@dom.dev');
        $departments[$uuid3->toString()]->getName()->shouldBe('Three');
        $departments[$uuid3->toString()]->getParent()->shouldBe(null);
        $departments[$uuid3->toString()]->getEmail()->toString()->shouldBe('three@dom.dev');
    }

    public function it_can_update_a_department(\PDOStatement $statement, Department $department) : void
    {
        $department->getId()->willReturn($departmentId = Uuid::uuid4());
        $department->getName()->willReturn($name = 'DepartmentName');
        $department->getParent()->willReturn(null);
        $department->getEmail()->willReturn($email = EmailAddressValue::get('dep@art.ment'));

        $this->db->prepare(new Argument\Token\StringContainsToken('UPDATE'))->willReturn($statement);
        $statement->execute([
            'name' => $name,
            'email' => $email->toString(),
            'parent_id' => null,
            'department_id' => $departmentId->getBytes(),
        ])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);

        $this->updateDepartment($department);
    }

    public function it_will_error_when_updating_department_failed(
        \PDOStatement $statement,
        Department $department
    ) : void
    {
        $department->getId()->willReturn($departmentId = Uuid::uuid4());
        $department->getName()->willReturn($name = 'DepartmentName');
        $department->getParent()->willReturn(null);
        $department->getEmail()->willReturn($email = EmailAddressValue::get('dep@art.ment'));

        $this->db->prepare(new Argument\Token\StringContainsToken('UPDATE'))->willReturn($statement);
        $statement->execute([
            'name' => $name,
            'email' => $email->toString(),
            'parent_id' => null,
            'department_id' => $departmentId->getBytes(),
        ])->shouldBeCalled();
        $statement->rowCount()->willReturn(0);

        $this->shouldThrow(\RuntimeException::class)->duringUpdateDepartment($department);
    }

    public function it_can_delete_a_department(\PDOStatement $statement, Department $department) : void
    {
        $department->getId()->willReturn($uuid = Uuid::uuid4());
        $this->db->prepare(new Argument\Token\StringContainsToken('DELETE'))->willReturn($statement);
        $statement->execute(['department_id' => $uuid->getBytes()])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);

        $this->deleteDepartment($department);
    }

    public function it_will_error_when_deleting_department_failed(
        \PDOStatement $statement,
        Department $department
    ) : void
    {
        $department->getId()->willReturn($uuid = Uuid::uuid4());
        $this->db->prepare(new Argument\Token\StringContainsToken('DELETE'))->willReturn($statement);
        $statement->execute(['department_id' => $uuid->getBytes()])->shouldBeCalled();
        $statement->rowCount()->willReturn(0);

        $this->shouldThrow(\RuntimeException::class)->duringDeleteDepartment($department);
    }
}
