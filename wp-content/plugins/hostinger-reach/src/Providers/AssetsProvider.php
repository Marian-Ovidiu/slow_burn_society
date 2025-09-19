<?php

namespace Hostinger\Reach\Providers;

use Hostinger\Reach\Api\Handlers\ReachApiHandler;
use Hostinger\Reach\Container;
use Hostinger\Reach\Functions;
use Hostinger\Reach\Setup\Assets;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class AssetsProvider implements ProviderInterface {
    public function register( Container $container ): void {
        $container->set(
            Assets::class,
            function () use ( $container ) {
                return new Assets( $container->get( Functions::class ), $container->get( ReachApiHandler::class ) );
            }
        );

        $assets = $container->get( Assets::class );
        $assets->init();
    }
}
