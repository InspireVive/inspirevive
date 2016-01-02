<?php

use Phinx\Migration\AbstractMigration;

class RenameSlug extends AbstractMigration
{
    public function change()
    {
        $this->table('Organizations')->renameColumn('slug', 'username')->save();
    }
}