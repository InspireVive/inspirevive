<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use Phinx\Migration\AbstractMigration;

class VolunteerOrganization extends AbstractMigration
{
    public function change()
    {
        if (!$this->hasTable('VolunteerOrganizations')) {
            $table = $this->table('VolunteerOrganizations');
            $table->addColumn('organization', 'integer')
              ->addColumn('unapproved_hours_notify_count', 'integer')
              ->addColumn('volunteer_coordinator_email', 'string')
              ->addColumn('city', 'string', ['length' => 30])
              ->addColumn('created_at', 'integer')
              ->addColumn('updated_at', 'integer', ['null' => true, 'default' => null])
              ->create();
        }
    }
}
