<?php

namespace Hostinger\Reach\Setup;

use Hostinger\Reach\Admin\Database\DatabaseTable;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Database {

    /** @var DatabaseTable[] */
    private array $tables;

    public function __construct( array $tables ) {
        $this->tables = $tables;
    }

    public function init(): void {
        $db_version_option = HOSTINGER_REACH_PLUGIN_SLUG . '-db-version';
        $database_version  = get_option(
            $db_version_option,
            ''
        );

        if ( $database_version !== HOSTINGER_REACH_DB_VERSION ) {
            $this->create_tables();
            update_option( $db_version_option, HOSTINGER_REACH_DB_VERSION, false );
        }
    }

    private function create_tables(): void {
        foreach ( $this->tables as $table ) {
            $table->create();
        }
    }
}
