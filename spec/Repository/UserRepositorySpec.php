<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\UserCollection;
use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\RoleInterface;

class UserRepositorySpec extends ObjectBehavior
{
    /** @var  \PDO */
    private $db;

    /** @var  Rbac */
    private $rbac;

    public function let(\PDO $db, Rbac $rbac)
    {
        $this->beConstructedWith(
            $this->db = $db,
            $this->rbac = $rbac
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(UserRepository::class);
    }

    public function it_can_create_a_user(User $user, \PDOStatement $statement, RoleInterface $role) : void
    {
        $email = 'user@domain.mail';
        $user->getEmail()->willReturn(EmailAddressValue::get($email));
        $displayName = 'Username';
        $user->getDisplayName()->willReturn($displayName);
        $password = password_hash('secret', PASSWORD_DEFAULT);
        $user->getPassword()->willReturn($password);
        $roleName = 'admin';
        $user->getRole()->willReturn($role);
        $role->getName()->willReturn($roleName);

        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute([
            'email' => $email,
            'display_name' => $displayName,
            'password' => $password,
            'role' => $roleName,
        ])->shouldBeCalled();

        $this->createUser($user);
    }

    public function it_can_get_all_users(\PDOStatement $statement, RoleInterface $role) : void
    {
        $this->db->query(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn(
            ['email' => 'one@d.com', 'display_name' => 'One', 'password' => '...', 'role' => 'user', 'active' => true],
            ['email' => 'two@d.com', 'display_name' => 'Two', 'password' => '...', 'role' => 'user', 'active' => false],
            null
        );

        $this->rbac->getRole('user')->willReturn($role);

        $users = $this->getUsers();
        $users->shouldBeAnInstanceOf(UserCollection::class);
        $users['one@d.com']->shouldBeAnInstanceOf(User::class);
        $users['one@d.com']->getDisplayName()->shouldBe('One');
        $users['one@d.com']->getPassword()->shouldBe('...');
        $users['one@d.com']->isActive()->shouldBe(true);
        $users['two@d.com']->shouldBeAnInstanceOf(User::class);
        $users['two@d.com']->getDisplayName()->shouldBe('Two');
        $users['two@d.com']->getPassword()->shouldBe('...');
        $users['two@d.com']->isActive()->shouldBe(false);
    }

    public function it_can_get_all_users_for_department(
        \PDOStatement $statement,
        RoleInterface $role,
        Department $department
    ) : void
    {
        $departmentId = Uuid::uuid4();
        $department->getId()->willReturn($departmentId);

        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute(['department_id' => $departmentId->getBytes()])->shouldBeCalled();
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn(
            ['email' => 'one@d.com', 'display_name' => 'One', 'password' => '...', 'role' => 'user', 'active' => true],
            ['email' => 'two@d.com', 'display_name' => 'Two', 'password' => '...', 'role' => 'user', 'active' => false],
            null
        );

        $this->rbac->getRole('user')->willReturn($role);

        $users = $this->getUsersForDepartment($department);
        $users->shouldBeAnInstanceOf(UserCollection::class);
        $users['one@d.com']->shouldBeAnInstanceOf(User::class);
        $users['one@d.com']->getDisplayName()->shouldBe('One');
        $users['one@d.com']->getPassword()->shouldBe('...');
        $users['one@d.com']->isActive()->shouldBe(true);
        $users['two@d.com']->shouldBeAnInstanceOf(User::class);
        $users['two@d.com']->getDisplayName()->shouldBe('Two');
        $users['two@d.com']->getPassword()->shouldBe('...');
        $users['two@d.com']->isActive()->shouldBe(false);
    }

    public function it_can_get_one_user(\PDOStatement $statement, RoleInterface $role) : void
    {
        $email = 'user@domain.mail';
        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute(['email' => $email])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn(
            ['email' => $email, 'display_name' => 'One', 'password' => '...', 'role' => 'user', 'active' => true],
            null
        );

        $this->rbac->getRole('user')->willReturn($role);

        $user = $this->getUserByEmail(EmailAddressValue::get($email));
        $user->shouldBeAnInstanceOf(User::class);
        $user->getEmail()->toString()->shouldBeLike($email);
        $user->getDisplayName()->shouldBe('One');
        $user->getPassword()->shouldBe('...');
        $user->isActive()->shouldBe(true);
    }

    public function it_errors_when_user_doesnt_exist(\PDOStatement $statement) : void
    {
        $email = 'user@domain.mail';
        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute(['email' => $email])->shouldBeCalled();
        $statement->rowCount()->willReturn(0);

        $this->shouldThrow(\OutOfBoundsException::class)->duringGetUserByEmail(EmailAddressValue::get($email));
    }

    public function it_can_update_a_user(\PDOStatement $statement, User $user, RoleInterface $role)
    {
        $user->getDisplayName()->willReturn($displayName = 'My Name');
        $user->getEmail()->willReturn($email = EmailAddressValue::get('my@name.email'));
        $user->getRole()->willReturn($role);
        $user->isActive()->willReturn($active = true);

        $role->getName()->willReturn($roleName = 'user');

        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute([
            'display_name' => $displayName,
            'email' => $email->toString(),
            'role' => $roleName,
            'active' => intval($active),
        ])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);

        $this->updateUser($user);
    }

    public function it_errors_when_updating_a_user_fails(\PDOStatement $statement, User $user, RoleInterface $role)
    {
        $user->getDisplayName()->willReturn($displayName = 'My Name');
        $user->getEmail()->willReturn($email = EmailAddressValue::get('my@name.email'));
        $user->getRole()->willReturn($role);
        $user->isActive()->willReturn($active = true);

        $role->getName()->willReturn($roleName = 'user');

        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute([
            'display_name' => $displayName,
            'email' => $email->toString(),
            'role' => $roleName,
            'active' => intval($active),
        ])->shouldBeCalled();
        $statement->rowCount()->willReturn(0);

        $this->shouldThrow(\RuntimeException::class)->duringUpdateUser($user);
    }

    public function it_can_update_a_users_password(\PDOStatement $statement, User $user)
    {
        $user->getEmail()->willReturn($email = EmailAddressValue::get('my@name.email'));
        $password = password_hash('some-password', PASSWORD_DEFAULT);

        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute([
            'email' => $email->toString(),
            'password' => $password,
        ])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);

        $user->setPassword($password)->shouldBeCalled();

        $this->updatePassword($user, $password);
    }

    public function it_errors_when_updating_a_users_password_fails(\PDOStatement $statement, User $user)
    {
        $user->getEmail()->willReturn($email = EmailAddressValue::get('my@name.email'));
        $password = password_hash('some-password', PASSWORD_DEFAULT);

        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute([
            'email' => $email->toString(),
            'password' => $password,
        ])->shouldBeCalled();
        $statement->rowCount()->willReturn(0);

        $user->setPassword($password)->shouldNotBeCalled();

        $this->shouldThrow(\RuntimeException::class)->duringUpdatePassword($user, $password);
    }

    public function it_can_assign_a_user_to_a_department(
        User $user,
        Department $department,
        \PDOStatement $statement
    ) : void
    {
        $departmentId = Uuid::uuid4();
        $department->getId()->willReturn($departmentId);
        $user->getEmail()->willReturn($email = EmailAddressValue::get('my@name.email'));

        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute([
            'email' => $email->toString(),
            'department_id' => $departmentId->getBytes(),
        ])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);

        $this->assignUserToDepartment($user, $department);
    }

    public function it_will_error_when_assigning_user_to_department_failed(
        User $user,
        Department $department,
        \PDOStatement $statement
    ) : void
    {
        $departmentId = Uuid::uuid4();
        $department->getId()->willReturn($departmentId);
        $user->getEmail()->willReturn($email = EmailAddressValue::get('my@name.email'));

        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute([
            'email' => $email->toString(),
            'department_id' => $departmentId->getBytes(),
        ])->shouldBeCalled();
        $statement->rowCount()->willReturn(0);

        $this->shouldThrow(\RuntimeException::class)->duringAssignUserToDepartment($user, $department);
    }

    public function it_can_remove_a_user_from_a_department(
        User $user,
        Department $department,
        \PDOStatement $statement
    ) : void
    {
        $departmentId = Uuid::uuid4();
        $department->getId()->willReturn($departmentId);
        $user->getEmail()->willReturn($email = EmailAddressValue::get('my@name.email'));

        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute([
            'email' => $email->toString(),
            'department_id' => $departmentId->getBytes(),
        ])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);

        $this->removeUserFromDepartment($user, $department);
    }

    public function it_will_error_when_removing_user_from_department_failed(
        User $user,
        Department $department,
        \PDOStatement $statement
    ) : void
    {
        $departmentId = Uuid::uuid4();
        $department->getId()->willReturn($departmentId);
        $user->getEmail()->willReturn($email = EmailAddressValue::get('my@name.email'));

        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute([
            'email' => $email->toString(),
            'department_id' => $departmentId->getBytes(),
        ])->shouldBeCalled();
        $statement->rowCount()->willReturn(0);

        $this->shouldThrow(\RuntimeException::class)->duringRemoveUserFromDepartment($user, $department);
    }

    public function it_can_delete_a_user(User $user, \PDOStatement $statement) : void
    {
        $user->getEmail()->willReturn($email = EmailAddressValue::get('my@name.email'));

        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute(['email' => $email->toString()])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);

        $this->deleteUser($user);
    }

    public function it_will_error_when_deleting_user_failed(User $user, \PDOStatement $statement) : void
    {
        $user->getEmail()->willReturn($email = EmailAddressValue::get('my@name.email'));

        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute(['email' => $email->toString()])->shouldBeCalled();
        $statement->rowCount()->willReturn(0);

        $this->shouldThrow(\RuntimeException::class)->duringDeleteUser($user);
    }
}
