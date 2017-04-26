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

    /** @var  ?string */
    private $totpSecret;

    public function let()
    {
        $this->beConstructedWith(
            $this->email = EmailAddressValue::get('another@address.email'),
            $this->displayName = 'Display Moi',
            $this->password = password_hash('my-super-duper-secret-phrase', PASSWORD_DEFAULT),
            $this->totpSecret = sha1(uniqid())
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(User::class);
    }

    public function it_can_access_its_properties()
    {
        $this->getEmail()->shouldReturn($this->email);
        $this->getDisplayName()->shouldReturn($this->displayName);
        $this->getPassword()->shouldReturn($this->password);
        $this->hasTotpSecret()->shouldBe(true);
        $this->getTotpSecret()->shouldReturn($this->totpSecret);
    }

    public function it_can_instantiate_without_totp_secret()
    {
        $this->beConstructedWith($this->email, $this->displayName, $this->password, null);
        $this->hasTotpSecret()->shouldBe(false);
        $this->getTotpSecret()->shouldBe(null);
    }

    public function it_can_change_some_properties()
    {
        $displayName = 'MyNickname';
        $this->setDisplayName($displayName);
        $this->getDisplayName()->shouldBe($displayName);

        $password = password_hash('new-pass', PASSWORD_DEFAULT);
        $this->setPassword($password);
        $this->getPassword()->shouldBe($password);

        $totpSecret = sha1(uniqid());
        $this->setTotpSecret($totpSecret);
        $this->getTotpSecret()->shouldBe($totpSecret);

        $this->setTotpSecret(null);
        $this->getTotpSecret()->shouldBe(null);
    }
}
