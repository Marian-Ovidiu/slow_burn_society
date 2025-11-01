<?php

namespace Hostinger\Reach;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

use Hostinger\Reach\Providers\AssetsProvider;
use Hostinger\Reach\Providers\BlocksProvider;
use Hostinger\Reach\Providers\ContainerProvider;
use Hostinger\Reach\Providers\DatabaseProvider;
use Hostinger\Reach\Providers\IntegrationsProvider;
use Hostinger\Reach\Providers\JobsProvider;
use Hostinger\Reach\Providers\MenusProvider;
use Hostinger\Reach\Providers\NoticesProvider;
use Hostinger\Reach\Providers\ProviderInterface;
use Hostinger\Reach\Providers\RedirectsProvider;
use Hostinger\Reach\Providers\RoutesProvider;
use Hostinger\Reach\Providers\SurveysProvider;
use Hostinger\Reach\Providers\TrackingProvider;
use Hostinger\Reach\Providers\WebhooksProvider;
use Hostinger\Reach\Providers\WpdbProvider;

class Boot {
    private Container $container;

    private array $all_providers = array();

    private array $providers = array(
        WpdbProvider::class,
        ContainerProvider::class,
        DatabaseProvider::class,
        AssetsProvider::class,
        MenusProvider::class,
        RoutesProvider::class,
        BlocksProvider::class,
        IntegrationsProvider::class,
        RedirectsProvider::class,
        WebhooksProvider::class,
        TrackingProvider::class,
        JobsProvider::class,
        NoticesProvider::class,
    );

    private array $hostinger_providers = array(
        SurveysProvider::class,
    );

    private static ?Boot $instance = null;

    private function __construct() {
        $this->container = new Container();
    }

    public static function get_instance(): self {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function plugins_loaded(): void {
        $this->set_providers();
        $this->register_providers();
    }

    public function set_providers(): void {
        $this->all_providers = $this->providers;

        if ( ! empty( $_SERVER['H_PLATFORM'] ) ) {
            $this->all_providers = array_merge( $this->all_providers, $this->hostinger_providers );
        }
    }

    private function register_providers(): void {
        foreach ( $this->all_providers as $provider_class ) {
            $provider = new $provider_class();
            if ( $provider instanceof ProviderInterface ) {
                $provider->register( $this->container );
            }
        }
    }
}
