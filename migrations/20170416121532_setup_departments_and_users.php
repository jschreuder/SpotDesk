<?php

use Phinx\Migration\AbstractMigration;

class SetupDepartmentsAndUsers extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `departments` (
                `department_id` BINARY(16) NOT NULL,
                `name` VARCHAR(63) NOT NULL,
                `parent_id` BINARY(16) NULL,
                PRIMARY KEY (`department_id`)
            ) ENGINE = InnoDB
        ");
        $this->execute("
            ALTER TABLE `tickets`
                ADD COLUMN `department_id` BINARY(16) NULL,
                ADD INDEX `department_id_IDX` (`department_id` ASC),
                ADD CONSTRAINT `fk_tickets_departments`
                    FOREIGN KEY (`department_id`)
                    REFERENCES `departments` (`department_id`)
                    ON DELETE RESTRICT
                    ON UPDATE RESTRICT
        ");
        $this->execute("
            CREATE TABLE `users` (
                `email` VARCHAR(123) NOT NULL,
                `display_name` VARCHAR(63) NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                `totp_secret` VARCHAR(255) NULL,
                PRIMARY KEY (`email`)
            ) ENGINE = InnoDB
        ");
        $this->execute("
            CREATE TABLE IF NOT EXISTS `users_departments` (
                `email` VARCHAR(123) NOT NULL,
                `department_id` BINARY(16) NOT NULL,
                PRIMARY KEY (`email`, `department_id`),
                INDEX `department_id_IDX` (`department_id` ASC),
                CONSTRAINT `fk_users_departments_users`
                    FOREIGN KEY (`email`)
                    REFERENCES `users` (`email`)
                    ON DELETE CASCADE
                    ON UPDATE RESTRICT,
                CONSTRAINT `fk_users_departments_departments`
                    FOREIGN KEY (`department_id`)
                    REFERENCES `departments` (`department_id`)
                    ON DELETE CASCADE
                    ON UPDATE RESTRICT
            ) ENGINE = InnoDB
        ");
    }

    public function down()
    {
        $this->execute("DROP TABLE `users_departments`");
        $this->execute("DROP TABLE `users`");
        $this->execute("
            ALTER TABLE `tickets` 
                DROP FOREIGN KEY `fk_tickets_departments`,
                DROP INDEX `department_id_IDX`,
                DROP COLUMN `department_id`
        ");
        $this->execute("DROP TABLE `departments`");
    }
}
