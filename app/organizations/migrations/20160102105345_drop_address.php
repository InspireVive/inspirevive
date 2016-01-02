<?php

use Phinx\Migration\AbstractMigration;

class DropAddress extends AbstractMigration
{
    public function change()
    {
        $this->table('Organizations')->removeColumn('address')->save();
    }
}