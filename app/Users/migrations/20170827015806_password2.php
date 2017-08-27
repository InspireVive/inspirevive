<?php

use Phinx\Migration\AbstractMigration;

class Password2 extends AbstractMigration
{
    public function change()
    {
        $this->table('Users')
             ->addColumn('password2', 'string', ['null' => true, 'default' => null, 'after' => 'password'])
             ->update();
    }
}
