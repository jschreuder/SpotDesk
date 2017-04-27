<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Repository;

use jschreuder\SpotDesk\Collection\UserCollection;
use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Repository\UserRepository;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserRepositorySpec extends ObjectBehavior
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
        $this->shouldHaveType(UserRepository::class);
    }

    public function it_can_create_a_user(User $user, \PDOStatement $statement) : void
    {
        $email = 'user@domain.mail';
        $user->getEmail()->willReturn(EmailAddressValue::get($email));
        $displayName = 'Username';
        $user->getDisplayName()->willReturn($displayName);
        $password = password_hash('secret', PASSWORD_DEFAULT);
        $user->getPassword()->willReturn($password);

        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute([
            'email' => $email,
            'display_name' => $displayName,
            'password' => $password,
        ])->shouldBeCalled();

        $this->createUser($user);
    }

    public function it_can_get_all_users(\PDOStatement $statement) : void
    {
        $this->db->query(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn(
            ['email' => 'one@domain.com', 'display_name' => 'One', 'password' => '...', 'totp_secret' => null],
            ['email' => 'two@domain.com', 'display_name' => 'Two', 'password' => '...', 'totp_secret' => null],
            null
        );

        $users = $this->getUsers();
        $users->shouldBeAnInstanceOf(UserCollection::class);
        $users['one@domain.com']->shouldBeAnInstanceOf(User::class);
        $users['one@domain.com']->getDisplayName()->shouldBe('One');
        $users['one@domain.com']->getPassword()->shouldBe('...');
        $users['two@domain.com']->shouldBeAnInstanceOf(User::class);
        $users['two@domain.com']->getDisplayName()->shouldBe('Two');
        $users['two@domain.com']->getPassword()->shouldBe('...');
    }

    public function it_can_get_one_user(\PDOStatement $statement) : void
    {
        $email = 'user@domain.mail';
        $this->db->prepare(new Argument\Token\TypeToken('string'))->willReturn($statement);
        $statement->execute(['email' => $email])->shouldBeCalled();
        $statement->rowCount()->willReturn(1);
        $statement->fetch(\PDO::FETCH_ASSOC)->willReturn(
            ['email' => $email, 'display_name' => 'One', 'password' => '...', 'totp_secret' => null],
            null
        );

        $user = $this->getUserByEmail(EmailAddressValue::get($email));
        $user->shouldBeAnInstanceOf(User::class);
        $user->getEmail()->toString()->shouldBeLike($email);
        $user->getDisplayName()->shouldBe('One');
        $user->getPassword()->shouldBe('...');
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
