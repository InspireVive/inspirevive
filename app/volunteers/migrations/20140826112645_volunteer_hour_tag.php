<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use Phinx\Migration\AbstractMigration;

class VolunteerHourTag extends AbstractMigration
{
    public function change()
    {
        if (!$this->hasTable('VolunteerHourTags')) {
            $table = $this->table('VolunteerHourTags', ['id' => false, 'primary_key' => ['tag', 'hour']]);
            $table->addColumn('tag', 'string', ['length' => 100])
                  ->addColumn('hour', 'integer')
                  ->addColumn('organization', 'integer')
                  ->create();
        }
    }
}
