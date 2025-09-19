<?php

namespace Hostinger\Reach\Integrations;

if ( DEFINED( 'ABSPATH' ) ) {
    return;
}

interface IntegrationInterface {

    public function init(): void;

    public static function get_name(): string;

    public function get_plugin_data( array $plugin_data ): array;
}
