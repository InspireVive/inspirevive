<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use Phinx\Migration\AbstractMigration;

class volunteer extends AbstractMigration
{
    public function change()
    {
        if (!$this->hasTable('Volunteers')) {
            $table = $this->table('Volunteers', ['id' => false, 'primary_key' => ['uid', 'organization']]);
            $table->addColumn('uid', 'integer')
              ->addColumn('organization', 'integer')
              ->addColumn('application_shared', 'boolean')
              ->addColumn('active', 'boolean', ['default' => true])
              ->addColumn('approval_link', 'string', ['length' => 32, 'null' => true, 'default' => null])
              ->addColumn('role', 'integer', ['length' => 2])
              ->addColumn('last_email_sent_about_hours', 'integer', ['null' => true, 'default' => null])
              ->addColumn('metadata', 'text', ['null' => true, 'default' => null])
              ->addColumn('created_at', 'integer')
              ->addColumn('updated_at', 'integer', ['null' => true, 'default' => null])
              ->create();
        }
    }
}
