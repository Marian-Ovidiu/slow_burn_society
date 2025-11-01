<?php

namespace Hostinger\Reach\Jobs;

defined( 'ABSPATH' ) || exit;

interface RecurringJobInterface {
    public function get_interval(): int;
}
