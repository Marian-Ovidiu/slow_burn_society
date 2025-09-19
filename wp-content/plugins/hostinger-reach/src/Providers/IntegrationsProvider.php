<?php

namespace Hostinger\Reach\Providers;

use Hostinger\Reach\Api\Handlers\IntegrationsApiHandler;
use Hostinger\Reach\Api\Handlers\ReachApiHandler;
use Hostinger\Reach\Container;
use Hostinger\Reach\Functions;
use Hostinger\Reach\Integrations\ContactForm7Integration;
use Hostinger\Reach\Integrations\Elementor\ElementorIntegration;
use Hostinger\Reach\Integrations\ReachFormIntegration;
use Hostinger\Reach\Integrations\WooCommerce\WooCommerceIntegration;
use Hostinger\Reach\Integrations\WpFormsLiteIntegration;
use Hostinger\Reach\Repositories\ContactListRepository;
use Hostinger\Reach\Repositories\FormRepository;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class IntegrationsProvider implements ProviderInterface {

    public const INTEGRATIONS = array(
        ReachFormIntegration::INTEGRATION_NAME    => ReachFormIntegration::class,
        ContactForm7Integration::INTEGRATION_NAME => ContactForm7Integration::class,
        WpFormsLiteIntegration::INTEGRATION_NAME  => WpFormsLiteIntegration::class,
        ElementorIntegration::INTEGRATION_NAME    => ElementorIntegration::class,
        WooCommerceIntegration::INTEGRATION_NAME  => WooCommerceIntegration::class,
    );

    public function register( Container $container ): void {

        $integrations = array(
            ReachFormIntegration::class    => array(
                $container->get( FormRepository::class ),
                $container->get( ContactListRepository::class ),
                $container->get( Functions::class ),
            ),
            ContactForm7Integration::class => array(
                $container->get( ReachApiHandler::class ),
                $container->get( IntegrationsApiHandler::class ),
            ),
            WpFormsLiteIntegration::class  => array(
                $container->get( ReachApiHandler::class ),
                $container->get( IntegrationsApiHandler::class ),
            ),
            ElementorIntegration::class    => array(
                $container->get( ReachApiHandler::class ),
                $container->get( IntegrationsApiHandler::class ),
                $container->get( FormRepository::class ),
            ),
            WooCommerceIntegration::class  => array(
                $container->get( FormRepository::class ),
                $container->get( IntegrationsApiHandler::class ),
                $container->get( ReachApiHandler::class ),

            ),
        );

        foreach ( $integrations as $class_name => $dependencies ) {
            $integration = new $class_name( ...$dependencies );
            $container->set(
                $integration::class,
                function () use ( $integration ) {
                    return $integration;
                }
            );

            $integration = $container->get( $integration::class );

            add_action( 'init', array( $integration, 'init' ) );
        }
    }
}
