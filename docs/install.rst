========================
Installation & dev usage
========================

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
* Go to ``http://localhost:8080/`` in your browser to view SpotDesk

TODO in this section: setting up departments.
