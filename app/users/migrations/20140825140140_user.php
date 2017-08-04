<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use Phinx\Migration\AbstractMigration;

class user extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('Users');
        $table->addColumn('username', 'string')
              ->addColumn('about', 'text')
              ->addColumn('invited_by', 'integer', ['null' => true, 'default' => null])
              ->addColumn('volunteer_hours', 'integer')
              ->removeColumn('first_name')
              ->removeColumn('last_name')
              ->update();
    }
}
