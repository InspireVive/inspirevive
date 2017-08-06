<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use Phinx\Migration\AbstractMigration;

class VolunteerApplication extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('VolunteerApplications', ['id' => 'uid']);
        $table->addColumn('first_name', 'string')
          ->addColumn('middle_name', 'string')
          ->addColumn('last_name', 'string')
          ->addColumn('address', 'string')
          ->addColumn('city', 'string')
          ->addColumn('state', 'string', ['length' => 25])
          ->addColumn('zip_code', 'string', ['length' => 15])
          ->addColumn('phone', 'string', ['length' => 25])
          ->addColumn('alternate_phone', 'string', ['length' => 25])
          ->addColumn('has_sms', 'boolean')
          ->addColumn('birth_date', 'integer')
          ->addColumn('first_time_volunteer', 'boolean')
          ->addTimestamps()
          ->create();
    }
}
