<?php

namespace Hostinger\Reach\Providers;

use Hostinger\Reach\Api\Handlers\IntegrationsApiHandler;
use Hostinger\Reach\Container;
use Hostinger\Reach\Functions;
use Hostinger\Reach\Integrations\ContactForm7\ContactForm7Integration;
use Hostinger\Reach\Integrations\Elementor\ElementorIntegration;
use Hostinger\Reach\Integrations\Reach\ReachFormIntegration;
use Hostinger\Reach\Integrations\WooCommerce\WooCommerceIntegration;
use Hostinger\Reach\Integrations\WPFormsLite\WpFormsLiteIntegration;
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
            ContactForm7Integration::class => array(),
            WpFormsLiteIntegration::class  => array(),
            ElementorIntegration::class    => array(
                $container->get( FormRepository::class ),
            ),
            WooCommerceIntegration::class  => array(
                $container->get( FormRepository::class ),

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
            $integration->init();
        }

        $container->get( IntegrationsApiHandler::class )->init_hooks();
    }
}
