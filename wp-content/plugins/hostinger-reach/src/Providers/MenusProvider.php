<?php

namespace Hostinger\Reach\Providers;

use Hostinger\Reach\Admin\Menus;
use Hostinger\Reach\Container;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class MenusProvider implements ProviderInterface {
    public function register( Container $container ): void {
        $container->set(
            Menus::class,
            function () use ( $container ) {
                return new Menus();
            }
        );

        $menus = $container->get( Menus::class );
        $menus->init();
    }
}
