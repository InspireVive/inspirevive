<?php

use Phinx\Migration\AbstractMigration;

class FullName extends AbstractMigration
{
    public function change()
    {
        $this->table('Users')
             ->addColumn('full_name', 'string', ['null' => true, 'default' => null, 'after' => 'username'])
             ->update();
    }
}
