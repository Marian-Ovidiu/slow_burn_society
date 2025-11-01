<?php

namespace Hostinger\Reach\Providers;
use Hostinger\Reach\Api\Handlers\IntegrationsApiHandler;
use Hostinger\Reach\Api\Handlers\ReachApiHandler;
use Hostinger\Reach\Api\Webhooks\Handlers\OrderPurchased;
use Hostinger\Reach\Container;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class WebhooksProvider implements ProviderInterface {

    public function register( Container $container ): void {

        $webhooks = array(
            OrderPurchased::class => array(
                $container->get( ReachApiHandler::class ),
                $container->get( IntegrationsApiHandler::class ),
            ),
        );

        foreach ( $webhooks as $class_name => $dependencies ) {
            $webhook = new $class_name( ...$dependencies );
            $container->set(
                $webhook::class,
                function () use ( $webhook ) {
                    return $webhook;
                }
            );

            $webhook = $container->get( $webhook::class );
            $webhook->init();
        }
    }
}
