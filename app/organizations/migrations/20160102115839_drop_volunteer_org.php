<?php

use Phinx\Migration\AbstractMigration;

class DropVolunteerOrg extends AbstractMigration
{
    public function change()
    {
        $this->table('Organizations')->removeColumn('volunteer_organization')->save();
    }
}