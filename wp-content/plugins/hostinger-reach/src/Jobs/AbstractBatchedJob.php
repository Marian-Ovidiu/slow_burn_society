<?php

namespace Hostinger\Reach\Jobs;


defined( 'ABSPATH' ) || exit;

abstract class AbstractBatchedJob extends AbstractJob {

    public function init(): void {
        add_action( $this->get_create_batch_hook(), array( $this, 'handle_create_batch_action' ), 10, 2 );
        parent::init();
    }

    protected function get_create_batch_hook(): string {
        return "{$this->get_hook_base_name()}/create_batch";
    }

    public function schedule( array $args = array() ): void {
        $this->schedule_create_batch_action( 1, $args );
    }

    public function handle_create_batch_action( int $batch_number, array $args ): void {
        $items = $this->get_batch( $batch_number, $args );

        // Stop the job in the next cases:
        // Reach is not connected in the middle of the job, we need to stop the job.
        // No more carts found.
        // Job is running for 10 times (that is 1000 carts).
        if ( empty( $items ) || ! $this->reach_api_handler->is_connected() || $batch_number >= 10 ) {
            $this->handle_complete( $batch_number, $args );
        } else {
            $this->schedule_process_action( $items, $args );
            $this->schedule_create_batch_action( $batch_number + 1, $args );
        }
    }

    protected function get_batch_size(): int {
        return apply_filters( 'hostinger_reach_batch_item_limit', 100 );
    }


    protected function schedule_create_batch_action( int $batch_number, array $args ): void {
        if ( $this->can_schedule( array( $batch_number ) ) ) {
            $this->action_scheduler->schedule_immediate(
                $this->get_create_batch_hook(),
                array(
                    $batch_number,
                    $args,
                )
            );
        }
    }

    protected function schedule_process_action( array $items = array(), array $args = array() ): void {
        $job_data = array(
            'items' => $items,
            'args'  => $args,
        );
        if ( ! $this->is_processing( $job_data ) ) {
            $this->action_scheduler->schedule_immediate( $this->get_process_item_hook(), array( $job_data ) );
        }
    }

    protected function is_processing( array $args = array() ): bool {
        return $this->action_scheduler->has_scheduled_action( $this->get_process_item_hook(), array( $args ) );
    }

    protected function handle_complete( int $final_batch_number, array $args ): void {
        return;
    }

    abstract protected function get_batch( int $batch_number, array $args ): array;
}
