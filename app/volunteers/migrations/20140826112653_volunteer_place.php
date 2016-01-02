<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use Phinx\Migration\AbstractMigration;

class VolunteerPlace extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('VolunteerPlaces');
        $table->addColumn('organization', 'integer')
          ->addColumn('name', 'string')
          ->addColumn('place_type', 'integer', ['length' => 2])
          ->addColumn('address', 'string', ['length' => 1000])
          ->addColumn('coordinates', 'string')
          ->addColumn('verify_name', 'string', ['null' => true, 'default' => null])
          ->addColumn('verify_email', 'string', ['null' => true, 'default' => null])
          ->addColumn('verify_approved', 'boolean')
          ->addColumn('created_at', 'integer')
          ->addColumn('updated_at', 'integer', ['null' => true, 'default' => null])
          ->create();
    }
}
