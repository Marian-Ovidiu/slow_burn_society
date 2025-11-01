<?php

namespace Hostinger\Reach\Providers;

use Hostinger\Reach\Api\ApiKeyManager;
use Hostinger\Reach\Api\Handlers\IntegrationsApiHandler;
use Hostinger\Reach\Api\Handlers\ReachApiHandler;
use Hostinger\Reach\Api\Handlers\FormsApiHandler;
use Hostinger\Reach\Api\Routes\FormsRoutes;
use Hostinger\Reach\Api\Routes\IntegrationsRoutes;
use Hostinger\Reach\Api\Routes\ReachRoutes;
use Hostinger\Reach\Container;
use Hostinger\Reach\Repositories\ContactListRepository;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class RoutesProvider implements ProviderInterface {
    public function register( Container $container ): void {
        $routes = array(
            ReachRoutes::class        => array(
                $container->get( ReachApiHandler::class ),
                $container->get( ApiKeyManager::class ),
                $container->get( ContactListRepository::class ),
            ),
            FormsRoutes::class        => array(
                $container->get( FormsApiHandler::class ),
                $container->get( ApiKeyManager::class ),
            ),
            IntegrationsRoutes::class => array(
                $container->get( IntegrationsApiHandler::class ),
            ),
        );

        foreach ( $routes as $class_name => $dependencies ) {
            $router = new $class_name( ...$dependencies );
            $container->set(
                $router::class,
                function () use ( $router ) {
                    return $router;
                }
            );

            $route = $container->get( $router::class );
            $route->init();
        }
    }
}
