<?php

namespace Hostinger\Reach\Admin\Database;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class FormsTable extends DatabaseTable {
    protected string $table_name = 'forms';

    public function schema(): array {
        return array(
            'id mediumint(9) NOT NULL AUTO_INCREMENT',
            'form_id varchar(255) NOT NULL',
            'post_id int(11)',
            'contact_list_id int(11)',
            'type varchar(255) NOT NULL',
            'is_active tinyint(1) NOT NULL DEFAULT 1',
            'submissions INT UNSIGNED DEFAULT 0',
            'PRIMARY KEY  (id)',
            'UNIQUE KEY form_id (form_id)',
            'KEY contact_list_id (contact_list_id)',
        );
    }
}
