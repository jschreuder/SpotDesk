=======================
Contribution Guidelines
=======================

By contributing you are releasing your contributions under the same license as
SpotDesk.

--------------
PHP guidelines
--------------

All code contributed should follow `PSR-1 <http://www.php-fig.org/psr/psr-1/>`_
and `PSR-2 <http://www.php-fig.org/psr/psr-2/>`_. The `PSR-4 <http://www.php-fig.org/psr/psr-4/>`_
standard must be followed for autoloading. While these lay the groundwork,
there are some additional project-specific guidelines:

**General**

* All contributions MUST be properly tested with `phpspec <http://www.phpspec.net/>`_.
* Third party dependencies MUST be required using Composer.
* All PHP files MUST start with ``<?php declare(strict_types = 1);`` on their
  first line.

**Classes, methods & properties**

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
* Methods SHOULD be either private or public, never protected.

**Data and types**

* All properties MUST have type declarations in docblock.
* All method arguments SHOULD have a type declaration. Nullable values MUST
  have their type prefixed with a ``?``, even if they default to ``null``.
* All methods SHOULD have return type declarations.
* Any complex string type SHOULD be encapsulated by a Value object and be
  immutable. Modifications should always cause a new instance to be created for
  the modified value.
* Anything stored in a database SHOULD be encapsulated Entity class.
* MUST use single quotes for all strings, except for SQL queries which MUST use
  double quotes.
* Working with a database MUST be encapsulated in a Repository class.
* All database structure modifications must be done in Phinx migrations.
* When returning multiple objects they MUST be in a collection class to allow
  proper type safety.
* String array keys MUST use ``snake_casing`` (all lowercase with underscores).
* Values MUST be type-cast when working with third party dependencies without
  strict typing or methods having mixed return types.

---------------------
Javascript guidelines
---------------------

* All contributions MUST be properly tested with `Jasmine <https://jasmine.github.io/>`_.
* Methods MUST use ``camelCasing``.
* Properties MUST use ``snake_casing``.
* Angular scope is not used, always use the ControllerAs functionality and use
  ``ctrl`` as the controller variable name. Any variables and methods should be
  defined as properties and methods on the controller.
* Controller names are suffixed with ``Controller``.
* Services are prefixed with ``$sd``.
* Always use double quotes for strings.

-------------
Documentation
-------------

All documentation MUST be written in `RST (ReStructuredText) <http://docutils.sourceforge.net/docs/user/rst/quickstart.html>`_.
