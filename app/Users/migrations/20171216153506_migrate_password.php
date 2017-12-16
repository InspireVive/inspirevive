<?php

use Phinx\Migration\AbstractMigration;

class MigratePassword extends AbstractMigration
{
    public function change()
    {
        $this->table('Users')
             ->renameColumn('password', 'password3')
             ->renameColumn('password2', 'password')
             ->update();
    }
}
