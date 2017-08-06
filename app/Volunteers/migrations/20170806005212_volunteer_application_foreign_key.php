<?php

use Phinx\Migration\AbstractMigration;

class VolunteerApplicationForeignKey extends AbstractMigration
{
    public function change()
    {
        $this->table('VolunteerApplications')
            ->addForeignKey('uid', 'Users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->update();
    }
}
