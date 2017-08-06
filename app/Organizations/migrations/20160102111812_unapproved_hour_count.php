<?php

use Phinx\Migration\AbstractMigration;

class UnapprovedHourCount extends AbstractMigration
{
    public function change()
    {
        $this->table('Organizations')->addColumn('unapproved_hours_notify_count', 'integer')->save();
    }
}