<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use Phinx\Migration\AbstractMigration;

class VolunteerHour extends AbstractMigration
{
    public function change()
    {
        if (!$this->hasTable('VolunteerHours')) {
            $table = $this->table('VolunteerHours');
            $table->addColumn('uid', 'integer', ['null' => true, 'default' => null])
              ->addColumn('timestamp', 'integer')
              ->addColumn('campaign', 'integer', ['null' => true, 'default' => null])
              ->addColumn('last_validated', 'integer', ['null' => true, 'default' => null])
              ->addColumn('organization', 'integer')
              ->addColumn('hours', 'integer')
              ->addColumn('place', 'integer', ['null' => true, 'default' => null])
              ->addColumn('event', 'integer', ['null' => true, 'default' => null])
              ->addColumn('approval_link', 'string', ['length' => 32])
              ->addColumn('approved', 'boolean')
              ->addColumn('verification_requested', 'boolean')
              ->addColumn('verification_requested_at', 'integer', ['null' => true, 'default' => null])
              ->addColumn('created_at', 'integer')
              ->addColumn('updated_at', 'integer', ['null' => true, 'default' => null])
              ->create();
        }
    }
}
