<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\UserCollection;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
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
}
