<?php declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

class AddActiveColumnToUser extends AbstractMigration
{
    public function up() : void
    {
        $this->execute("
            ALTER TABLE `users`
                DROP COLUMN `totp_secret`,
                ADD COLUMN `active` BOOL NOT NULL DEFAULT TRUE;
        ");
    }

    public function down() : void
    {
        $this->execute("
            ALTER TABLE `users`
                DROP COLUMN `active`,
                ADD COLUMN `totp_secret` VARCHAR(255) NULL;
        ");
    }
}
