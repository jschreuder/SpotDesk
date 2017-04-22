=================
SpotDesk Helpdesk
=================

Very basic e-mail based Helpdesk software. It reads from one or more mailboxes
and converts the e-mails into tickets. Those tickets can be sorted into
departments and can be responded to by SpotDesk users.

This project is based on my own `Middle framework <https://github.com/jschreuder/Middle>`_
and requires PHP 7.1. The frontend of the project is based on
`Angular Material <https://material.angularjs.org/>`_.

--------------
Project status
--------------

Early alpha or even more unfinished/unstable.

-----------
Screenshots
-----------

.. image:: docs/assets/tickets-list.png
   :alt: List of open tickets

.. image:: docs/assets/view-ticket.png
   :alt: View a single ticket

--------------------
Features implemented
--------------------

* API based backend
* Angular Material based frontend
* Basic service worker setup to turn this into a progressive webapp
* E-mails from mailbox automatically turned into tickets
* Users can only see e-mails from departments to which they belong and those
  that do not belong to a department
* Tickets can get "internal" updates that are not send or visible to the client
* Development tooling like ``./console dev:cron`` to simulate running cronjobs
  and ``./console dev:create-faker-tickets`` to create fake tickets

------------------------
Installation & dev usage
------------------------

To install the application:

* Clone repository: ``git clone https://github.com/jschreuder/SpotDesk.git``
* Install dependencies: ``composer install`` in project root and
  ``npm install`` in ``/web``
* Setup config by copying ``/etc/dev.php.dist`` to ``/etc/dev.php`` and
  ``/etc/env.php.dist`` to ``/etc/env.php``
* Have a database ready and fill out the settings in ``/etc/dev.php``
* Fill out a random string in the ``session.secret_key`` setting in
  ``/etc/dev.php``
* It is recommended to use a tool like
  `MailHog <https://github.com/mailhog/MailHog>`_ and fill out its settings
  in ``/etc/dev.php`` - **the application sends mails** so make sure you don't
  have a live mailserver set up for testing
* Create database tables by running ``bin/phinx mig``

To run it:

* Go to ``/web`` and run PHP's build-in server ``php -S localhost:8080``
* Run ``./console dev:cron 300`` in an open terminal window to check mailboxes
  for e-mail and send mail notifications every 300 seconds (5 minutes)
* Run ``./console user:create test@helpdesk.dev password "Testuser"`` to create
  a user with e-mail ``test@helpdesk.dev``, with password ``password`` and with
  display name ``Testuser`` (modify as you please)
* Run ``./console dev:create-faker-tickets test@helpdesk.dev 25`` (change
  e-mailaddress to whatever you used in the previous step) to set up 25 tickets
  with fake content and replies by the given user
* Go to ``http://localhost:8080/index.html`` in your browser to view SpotDesk

TODO in this section: setting up departments.

------------
Todo for 1.0
------------

* Figure out why sessions seem to expire too early
* Some more
* UI user management
* UI department management
* UI status management
* UI mailbox management
* Move e-mail templates to database and make them manageable
* Allow e-mail responses to be turned into ticket updates
* Allow users to see tickets from child-departments
* Get all PHP tested using `phpspec <http://www.phpspec.net/>`_
* Add client views for tickets, these should use e-mail based logins: go to
  the ticket URL, the user gets a one-time usable time-limited login URL sent
  to their e-mail address and can view the ticket in its entirety with that
* Backend caching for departments and statuses, these may be fixed when
  deployed

-----------------------------
Feature wishlist (1.1 or 2.0)
-----------------------------

* Make it completely usable as progressive webapp, even when the connection
  is bad or dead
* Add role based authorization
* Add support for TOTP when logging in
* Add attachments to tickets and support e-mail attachments
* Add per department required response time to allow for detecting "overdue"
  tickets
* Implement ticket forwarding to send individual parts of a discussion to an
  outside source that may respond via e-mail and be processed as interal
  ticket update
* Get all JS sources tested using `Jasmine <https://jasmine.github.io/>`_
* Bulk-delete and bulk-status changing for tickets based on filters
