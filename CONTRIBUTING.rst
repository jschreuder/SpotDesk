=======================
Contribution Guidelines
=======================

By contributing you are releasing your contributions under the same license as
SpotDesk.

----------------
PHP coding style
----------------

All code contributed should follow `PSR-1 <http://www.php-fig.org/psr/psr-1/>`_
and `PSR-2 <http://www.php-fig.org/psr/psr-2/>`_. The `PSR-4 <http://www.php-fig.org/psr/psr-4/>`_
standard must be followed for autoloading. While these lay the groundwork,
there are some additional project-specific guidelines:

* All contributions MUST be properly tested with `phpspec <http://www.phpspec.net/en/stable/>`_.
* Third party dependencies MUST be required using Composer.
* All PHP files MUST start with ``<?php declare(strict_types = 1);`` on their
  first line.
* All classnames MUST be suffixed with their type, for example
  ``GetUsersController``. Entity classes are the only exception. Classes for
  working with entities should use singular forms, like ``UserRepository`` and
  ``UserCollection``.
* Interfaces and traits MUST be suffixed with ``Interface`` and ``Trait``.
  Abstract classes SHOULD be avoided. Methods from traits MUST be tested in
  each class in which the trait is used.
* Static methods SHOULD be avoided. Notable exceptions are use-cases that are
  truly static, like Value classes.
* Any class that might be extended MUST implement an interface and be
  ``final`` itself. Allowing extension by replacement or composition, but not
  by inheritance.
* All properties MUST have type declarations in docblock.
* All method arguments SHOULD have a type declaration. Nullable values MUST
  have their type prefixed with a ``?``, even if they default to ``null``.
* All methods SHOULD have return type declarations.
* Methods SHOULD be either private or public, never protected.
* Any complex string type SHOULD be encapsulated by a Value object and be
  immutable. Modifications should always cause a new instance to be created for
  the modified value.
* Anything stored in a database SHOULD be encapsulated Entity class.
* Working with a database MUST be encapsulated in a Repository class.
* When returning multiple objects they MUST be in a collection class to allow
  proper type safety.
* String array keys MUST use ``snake_casing`` (all lowercase with underscores).
* Values MUST be type-cast when working with third party dependencies without
  strict typing or methods having mixed return types.

-----------------------
Javascript coding style
-----------------------

* Methods MUST use ``camelCasing``.
* Properties MUST use ``snake_casing``.
* Angular scope is not used, always use the ControllerAs functionality and use
  ``ctrl`` as the controller variable name. Any variables and methods should be
  defined as properties and methods on the controller.
* Controller names are suffixed with ``Controller``.
* Services are prefixed with ``$``.
