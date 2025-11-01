<?php
declare( strict_types=1 );

namespace Hostinger\Reach\Jobs;

use Hostinger\Reach\Api\Handlers\ReachApiHandler;

defined( 'ABSPATH' ) || exit;

abstract class AbstractJob implements JobInterface {

    protected ActionScheduler $action_scheduler;
    protected ReachApiHandler $reach_api_handler;

    public function __construct( ActionScheduler $action_scheduler, ReachApiHandler $reach_api_handler ) {
        $this->action_scheduler  = $action_scheduler;
        $this->reach_api_handler = $reach_api_handler;
    }

    public function init(): void {
        add_action( $this->get_process_item_hook(), array( $this, 'handle_process_items_action' ) );
        add_action( $this->get_start_hook(), array( $this, 'handle_sart_hook_action' ) );

        if ( $this instanceof RecurringJobInterface && $this->can_schedule_recurrent() ) {
            $this->action_scheduler->schedule_recurring( $this->get_interval(), $this->get_start_hook() );
        }
    }

    public function can_schedule_recurrent(): bool {
        return ! $this->action_scheduler->has_scheduled_action( $this->get_start_hook() );
    }

    public function can_schedule( array $args = array() ): bool {
        return $this->reach_api_handler->is_connected() && ! $this->is_running( $args );
    }

    public function handle_sart_hook_action( array $args = array() ): void {
        $this->schedule( $args );
    }

    public function handle_process_items_action( array $args = array() ): void {
        $this->process_items( $args );
    }

    public function get_process_item_hook(): string {
        return "{$this->get_hook_base_name()}/process_item";
    }

    public function get_start_hook(): string {
        return "{$this->get_hook_base_name()}/start";
    }

    protected function is_running( ?array $args = array() ): bool {
        return $this->action_scheduler->has_scheduled_action( $this->get_process_item_hook(), array( $args ) );
    }

    protected function get_hook_base_name(): string {
        return "{$this->action_scheduler->get_group()}/jobs/{$this->get_name()}";
    }

    abstract public function get_name(): string;

    abstract protected function process_items( array $args ): void;
}
