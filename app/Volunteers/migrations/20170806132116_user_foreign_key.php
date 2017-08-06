<?php

use Phinx\Migration\AbstractMigration;

class UserForeignKey extends AbstractMigration
{
    public function change()
    {
        $tables = [
            'Volunteers',
            'VolunteerHours',
            'VolunteerApplications',
        ];

        foreach ($tables as $table) {
            $this->table($table)
                ->addForeignKey('uid', 'Users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                ->update();
        }
    }
}
