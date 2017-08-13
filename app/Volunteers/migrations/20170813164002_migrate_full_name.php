<?php

use Phinx\Migration\AbstractMigration;

class MigrateFullName extends AbstractMigration
{
    public function up()
    {
        $this->execute('UPDATE Users JOIN VolunteerApplications a ON a.uid=id SET full_name=CONCAT(a.first_name," ",a.last_name)');
    }
}
