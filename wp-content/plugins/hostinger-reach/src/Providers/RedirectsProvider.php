<?php

namespace Hostinger\Reach\Providers;

use Hostinger\Reach\Admin\Redirects;
use Hostinger\Reach\Container;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class RedirectsProvider implements ProviderInterface {
    public function register( Container $container ): void {
        $container->set(
            Redirects::class,
            function () use ( $container ) {
                return new Redirects();
            }
        );

        $object = $container->get( Redirects::class );
        $object->init();
    }
}
