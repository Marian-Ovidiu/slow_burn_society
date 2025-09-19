<?php

namespace Hostinger\Reach\Providers;

use Hostinger\Reach\Blocks\SubscriptionFormBlock;
use Hostinger\Reach\Container;
use Hostinger\Reach\Setup\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class BlocksProvider implements ProviderInterface {
    public function register( Container $container ): void {
        $container->set(
            Blocks::class,
            function () use ( $container ) {
                return new Blocks( $container->get( SubscriptionFormBlock::class ) );
            }
        );

        $blocks = $container->get( Blocks::class );
        $blocks->init();
    }
}
