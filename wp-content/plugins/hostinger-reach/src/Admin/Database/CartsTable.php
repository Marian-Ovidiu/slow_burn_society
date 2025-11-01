<?php

namespace Hostinger\Reach\Admin\Database;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class CartsTable extends DatabaseTable {
    protected string $table_name = 'carts';

    public function schema(): array {
        return array(
            'hash varchar(100)',
            'customer_id bigint(20) unsigned DEFAULT NULL',
            'customer_email varchar(100)',
            'items longtext NOT NULL',
            'totals text NOT NULL',
            'currency varchar(3) NOT NULL',
            'status varchar(100) NOT NULL',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'PRIMARY KEY  (hash)',
            'UNIQUE KEY hash (hash)',
            'KEY customer_id (customer_id)',
        );
    }
}
