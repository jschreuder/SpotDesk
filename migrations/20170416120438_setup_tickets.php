<?php

use Phinx\Migration\AbstractMigration;

class SetupTickets extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `statuses` (
                `status` VARCHAR(31) NOT NULL,
                `type` ENUM('open', 'paused', 'closed'),
                PRIMARY KEY (`status`)
            ) ENGINE = InnoDB
        ");
        $this->execute("
            INSERT INTO `statuses` (`status`, `type`)
            VALUES ('new', 'open'), ('open', 'open'), ('awaiting-client', 'paused'), ('closed', 'closed')"
        );

        $this->execute("
            CREATE TABLE `tickets` (
                `ticket_id` BINARY(16) NOT NULL,
                `secret_key` CHAR(127) NOT NULL,
                `email` VARCHAR(123) NOT NULL,
                `subject` VARCHAR(127) NOT NULL,
                `message` TEXT NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updates` INT UNSIGNED NOT NULL DEFAULT 0,
                `last_update` DATETIME NOT NULL,
                `status` VARCHAR(31) NOT NULL DEFAULT 'new',
                `department` BINARY(16) NULL DEFAULT NULL,
                PRIMARY KEY (`ticket_id`),
                INDEX `status_IDX` (`status` ASC),
                INDEX `department_IDX` (`department` ASC),
                INDEX `email_IDX` (`email` ASC),
                CONSTRAINT `fk_tickets_statuses`
                    FOREIGN KEY (`status`)
                    REFERENCES `statuses` (`status`)
                    ON DELETE RESTRICT
                    ON UPDATE RESTRICT
            ) ENGINE = InnoDB
        ");

        $this->execute("
            CREATE TABLE `ticket_updates` (
                `ticket_update_id` BINARY(16) NOT NULL,
                `ticket_id` BINARY(16) NOT NULL,
                `email` VARCHAR(123) NOT NULL,
                `message` TEXT NOT NULL COMMENT '	',
                `created_at` DATETIME NOT NULL,
                `internal` BOOLEAN NOT NULL DEFAULT 0,
                PRIMARY KEY (`ticket_update_id`),
                INDEX `ticket_id_IDX` (`ticket_id` ASC),
                CONSTRAINT `fk_ticket_updates_tickets`
                    FOREIGN KEY (`ticket_id`)
                    REFERENCES `tickets` (`ticket_id`)
                    ON DELETE CASCADE
                    ON UPDATE RESTRICT
            ) ENGINE = InnoDB
        ");

        $this->execute("
            CREATE TABLE `ticket_subscriptions` (
                `ticket_subscription_id` BINARY(16) NOT NULL,
                `ticket_id` BINARY(16) NOT NULL,
                `email` VARCHAR(123) NOT NULL,
                PRIMARY KEY (`ticket_subscription_id`),
                INDEX `ticket_id_IDX` (`ticket_id` ASC),
                CONSTRAINT `fk_ticket_subscriptions_tickets`
                    FOREIGN KEY (`ticket_id`)
                    REFERENCES `tickets` (`ticket_id`)
                    ON DELETE CASCADE
                    ON UPDATE RESTRICT
            ) ENGINE = InnoDB
        ");
    }

    public function down()
    {
        $this->execute("DROP TABLE `ticket_subscriptions`");
        $this->execute("DROP TABLE `ticket_updates`");
        $this->execute("DROP TABLE `tickets`");
        $this->execute("DROP TABLE `statuses`");
    }
}
