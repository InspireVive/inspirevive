<?php

use Phinx\Migration\AbstractMigration;

class DropVolunteerHourEvent extends AbstractMigration
{
    public function change()
    {
        $this->table('VolunteerHours')
        	 ->removeColumn('event')
        	 ->removeColumn('campaign')
        	 ->removeColumn('last_validated')
        	 ->save();
    }
}