<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddFromAddressToDepartment extends AbstractMigration
{
    public function up() : void
    {
        $this->execute("
            ALTER TABLE `departments`
                ADD COLUMN `email` VARCHAR(123) NULL,
                ADD UNIQUE `email_UNQ` (`email`)
        ");
    }

    public function down() : void
    {
        $this->execute("
            ALTER TABLE `departments`
                DROP INDEX `email_UNQ`,
                DROP COLUMN `email`
        ");
    }
}
