<?php

namespace Hostinger\Reach\Providers;

use Hostinger\Reach\Admin\Database\CartsTable;
use Hostinger\Reach\Admin\Database\ContactListsTable;
use Hostinger\Reach\Admin\Database\FormsTable;
use Hostinger\Reach\Container;
use Hostinger\Reach\Setup\Database;
use wpdb;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class DatabaseProvider implements ProviderInterface {
    public const TABLES = array(
        FormsTable::class,
        ContactListsTable::class,
        CartsTable::class,
    );

    public function register( Container $container ): void {

        $tables = array();

        foreach ( self::TABLES as $table ) {
            $tables[] = new $table( $container->get( wpdb::class ) );
        }

        $container->set(
            Database::class,
            function () use ( $container, $tables ) {
                return new Database( $tables );
            }
        );

        $assets = $container->get( Database::class );
        $assets->init();
    }
}
