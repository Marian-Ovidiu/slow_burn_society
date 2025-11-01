<?php

namespace Hostinger\Reach\Jobs;

defined( 'ABSPATH' ) || exit;

interface JobInterface {
    public function get_name(): string;
    public function get_process_item_hook(): string;
    public function get_start_hook(): string;
    public function can_schedule( array $args = array() ): bool;
    public function schedule( array $args = array() ): void;
    public function init(): void;
}
