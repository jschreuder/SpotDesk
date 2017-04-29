<?php declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

class RemoveUnnecessaryDepartmentColumn extends AbstractMigration
{
    public function up()
    {
        $this->execute("ALTER TABLE `tickets` DROP COLUMN `department`;");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `tickets` ADD COLUMN `department` BINARY(16) NULL DEFAULT NULL AFTER `status`;");
    }
}
