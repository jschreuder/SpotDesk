<?php declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

class AddInternalColumnToTicketSubscriptions extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `ticket_subscriptions`
                ADD COLUMN `internal` BOOL NOT NULL DEFAULT FALSE,
                ADD COLUMN `send_notifications` BOOL NOT NULL DEFAULT TRUE,
                ADD UNIQUE `ticket_subscriber_UNQ` (`ticket_id` ASC, `email` ASC);
        ");
    }

    public function down()
    {
        $this->execute("
            ALTER TABLE `ticket_subscriptions`
                DROP COLUMN `internal`,
                DROP COLUMN `send_notifications`,
                DROP INDEX `ticket_subscriber_UNQ`;
        ");
    }
}
