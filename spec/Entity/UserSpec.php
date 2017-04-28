<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Entity\User;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;

class UserSpec extends ObjectBehavior
{
    /** @var  EmailAddressValue */
    private $email;

    /** @var  string */
    private $displayName;

    /** @var  string */
    private $password;

    /** @var  bool */
    private $active;

    public function let() : void
    {
        $this->beConstructedWith(
            $this->email = EmailAddressValue::get('another@address.email'),
            $this->displayName = 'Display Moi',
            $this->password = password_hash('my-super-duper-secret-phrase', PASSWORD_DEFAULT),
            $this->active = true
        );
    }

    public function it_is_initializable() : void
    {
        $this->shouldHaveType(User::class);
    }

    public function it_can_access_its_properties() : void
    {
        $this->getEmail()->shouldReturn($this->email);
        $this->getDisplayName()->shouldReturn($this->displayName);
        $this->getPassword()->shouldReturn($this->password);
        $this->isActive()->shouldBe(true);
    }

    public function it_can_instantiate_without_setting_user_active() : void
    {
        $this->beConstructedWith($this->email, $this->displayName, $this->password);
        $this->isActive()->shouldBe(true);
    }

    public function it_can_change_some_properties() : void
    {
        $displayName = 'MyNickname';
        $this->setDisplayName($displayName);
        $this->getDisplayName()->shouldBe($displayName);

        $password = password_hash('new-pass', PASSWORD_DEFAULT);
        $this->setPassword($password);
        $this->getPassword()->shouldBe($password);

        $this->setActive(false);
        $this->isActive()->shouldBe(false);
    }
}
