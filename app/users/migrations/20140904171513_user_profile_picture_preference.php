<?php

/**
 * @package InspireVive
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @copyright 2015 Jared King
 * @license GNU GPLv3
 */

use Phinx\Migration\AbstractMigration;

class UserProfilePicturePreference extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('Users');
        $table->addColumn('profile_picture_preference', 'string', ['length' => 9, 'default' => 'gravatar'])
              ->save();
    }
}
