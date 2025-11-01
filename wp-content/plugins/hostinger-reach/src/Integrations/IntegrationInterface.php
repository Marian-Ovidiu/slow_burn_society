<?php

namespace Hostinger\Reach\Integrations;

use Hostinger\Reach\Dto\PluginData;

if ( DEFINED( 'ABSPATH' ) ) {
    return;
}

interface IntegrationInterface {

    public function init(): void;

    public static function get_name(): string;

    public function get_plugin_data(): PluginData;
}
