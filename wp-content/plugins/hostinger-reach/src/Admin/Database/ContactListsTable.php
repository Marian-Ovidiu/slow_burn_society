<?php

namespace Hostinger\Reach\Admin\Database;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class ContactListsTable extends DatabaseTable {
    protected string $table_name = 'contact_lists';

    public function schema(): array {
        return array(
            'id mediumint(9) NOT NULL AUTO_INCREMENT',
            'name varchar(255) NOT NULL',
            'PRIMARY KEY (id)',
            'UNIQUE KEY name (name)',
        );
    }
}
