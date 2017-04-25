<?php declare(strict_types = 1);

namespace spec\jschreuder\SpotDesk\Entity;

use jschreuder\SpotDesk\Entity\Department;
use jschreuder\SpotDesk\Value\EmailAddressValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\UuidInterface;

class DepartmentSpec extends ObjectBehavior
{
    /** @var  UuidInterface */
    private $id;

    /** @var  string */
    private $name;

    /** @var  ?Department */
    private $parent;

    /** @var  EmailAddressValue */
    private $email;

    public function let(UuidInterface $id, Department $parent)
    {
        $this->id = $id;
        $this->name = 'name';
        $this->parent = $parent;
        $this->email = EmailAddressValue::get('dep@art.ment');
        $this->beConstructedWith($id, $this->name, $parent, $this->email);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Department::class);
    }

    public function it_can_access_its_properties()
    {
        $this->getId()->shouldReturn($this->id);
        $this->getName()->shouldReturn($this->name);
        $this->getParent()->shouldReturn($this->parent);
        $this->getEmail()->shouldReturn($this->email);
    }

    public function it_can_instantiate_without_parent()
    {
        $this->beConstructedWith($this->id, $this->name, null, $this->email);
        $this->getParent()->shouldReturn(null);
    }

    public function it_can_change_some_properties(Department $parent)
    {
        $name2 = 'two';
        $email = EmailAddressValue::get('some@thing.else');

        $this->setName($name2);
        $this->getName()->shouldReturn($name2);

        $this->setParent($parent);
        $this->getParent()->shouldReturn($parent);

        $this->setParent(null);
        $this->getParent()->shouldReturn(null);

        $this->setEmail($email);
        $this->getEmail()->shouldReturn($email);
    }
}
