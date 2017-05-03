<?php declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

class AddRoleToUser extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `users`
                ADD COLUMN `role` VARCHAR(63) NOT NULL;
            UPDATE `users` SET `role` = 'admin' WHERE 1 = 1;
        ");
    }

    public function down()
    {
        $this->execute("
            ALTER TABLE `users`
                DROP COLUMN `role`;
        ");
    }
}
