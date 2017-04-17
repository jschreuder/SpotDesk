<?php

use Phinx\Migration\AbstractMigration;

class AddMailingTables extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `ticket_mailings` (
                `ticket_mailing_id` BINARY(16) NOT NULL,
                `ticket_id` BINARY(16) NOT NULL,
                `type` VARCHAR(31) NOT NULL,
                `sent_at` DATETIME NULL,
                PRIMARY KEY (`ticket_mailing_id`),
                INDEX `sent_at_IDX` (`sent_at` ASC),
                CONSTRAINT `fk_ticket_mailings_tickets`
                    FOREIGN KEY (`ticket_id`)
                    REFERENCES `tickets` (`ticket_id`)
                    ON DELETE CASCADE
                    ON UPDATE RESTRICT
            ) ENGINE = InnoDB
        ");
    }

    public function down()
    {
        $this->execute("DROP TABLE `ticket_mailings`");
    }
}
