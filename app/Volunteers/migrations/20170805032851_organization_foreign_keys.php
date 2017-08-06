<?php

use Phinx\Migration\AbstractMigration;

class OrganizationForeignKeys extends AbstractMigration
{
    public function change()
    {
        $tables = [
            'Volunteers',
            'VolunteerHours',
            'VolunteerHourTags',
            'VolunteerPlaces',
        ];

        foreach ($tables as $table) {
            $this->table($table)
                 ->addForeignKey('organization', 'Organizations', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                 ->update();
        }
    }
}
