<?php

namespace Hostinger\Reach\Providers;

use Hostinger\Reach\Container;

interface ProviderInterface {
    public function register( Container $container ): void;
}
