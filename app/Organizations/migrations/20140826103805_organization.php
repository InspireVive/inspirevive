<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use Phinx\Migration\AbstractMigration;

class organization extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('Organizations');
        $table->addColumn('volunteer_organization', 'integer', ['null' => true, 'default' => null])
          ->addColumn('name', 'string')
          ->addColumn('slug', 'string')
          ->addColumn('email', 'string')
          ->addColumn('address', 'string', ['length' => 1000])
          ->addTimestamps()
          ->create();
    }
}
