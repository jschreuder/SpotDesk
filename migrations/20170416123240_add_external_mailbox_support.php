<?php

use Phinx\Migration\AbstractMigration;

class AddExternalMailboxSupport extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `mailboxes` (
                `mailbox_id` BINARY(16) NOT NULL,
                `name` VARCHAR(63) NOT NULL,
                `department_id` BINARY(16) NULL,
                `smtp_server` VARCHAR(255) NOT NULL,
                `smtp_port` SMALLINT UNSIGNED NOT NULL,
                `smtp_security` ENUM('none', 'ssl', 'starttls') NOT NULL DEFAULT 'none',
                `smtp_user` VARCHAR(255) NOT NULL,
                `smtp_pass` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`mailbox_id`),
                INDEX `department_id_IDX` (`department_id` ASC),
                CONSTRAINT `fk_mailboxes_departments`
                    FOREIGN KEY (`department_id`)
                    REFERENCES `departments` (`department_id`)
                    ON DELETE CASCADE
                    ON UPDATE RESTRICT
            ) ENGINE = InnoDB
        ");
    }

    public function down()
    {
        $this->execute("DROP TABLE `mailboxes`");
    }
}
