<?php

namespace Hostinger\Reach\Admin\Database;

use wpdb;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

abstract class DatabaseTable implements DatabaseInterface {
    private wpdb $wpdb;
    private string $prefix = 'hostinger_reach_';
    protected string $table_name;

    public function __construct( wpdb $wpdb ) {
        $this->wpdb = $wpdb;
    }

    public function create(): void {
        $charset_collate = $this->wpdb->get_charset_collate();
        $schema          = implode( ",\n", $this->schema() );
        $table           = $this->table_name();

        $sql = "CREATE TABLE $table (\n$schema\n) $charset_collate;";

        $this->execute_db_delta( $sql );
    }

    public function table_name(): string {
        return $this->wpdb->prefix . $this->prefix . $this->table_name;
    }

    abstract public function schema(): array;

    public function execute_db_delta( string $sql ): void {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
}
