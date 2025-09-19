<?php

namespace Hostinger\Reach\Providers;

use Hostinger\Reach\Container;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class ContainerProvider implements ProviderInterface {
    public function register( Container $container ): void {
        $container->set( Container::class, fn() => $container );
    }
}
