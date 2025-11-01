<?php

namespace Hostinger\Reach\Jobs;

use Hostinger\Reach\Api\Handlers\ReachApiHandler;
use Hostinger\Reach\Api\Webhooks\Handlers\CartAbandoned;
use Hostinger\Reach\Repositories\CartRepository;

class AbandonedCartsJob extends AbstractBatchedJob implements RecurringJobInterface {

    public const JOB_NAME             = 'abandoned_carts';
    public const JOB_INTERVAL_SECONDS = MINUTE_IN_SECONDS * 15;

    protected CartAbandoned $cart_abandoned_webhook;
    protected CartRepository $cart_repository;

    public function __construct( ActionScheduler $action_scheduler, ReachApiHandler $reach_api_handler, CartRepository $cart_repository, CartAbandoned $cart_abandoned_webhook ) {
        parent::__construct( $action_scheduler, $reach_api_handler );
        $this->cart_repository        = $cart_repository;
        $this->cart_abandoned_webhook = $cart_abandoned_webhook;
    }

    public function can_schedule_recurrent(): bool {
        return $this->cart_abandoned_webhook->is_enabled() && count( $this->cart_repository->active_abandoned_carts( 1 ) ) && parent::can_schedule_recurrent();
    }

    protected function get_batch( int $batch_number, array $args ): array {
        $limit = $this->get_batch_size();
        return $this->cart_repository->active_abandoned_carts( $limit, array( 'hash' ) );
    }

    public function get_name(): string {
        return self::JOB_NAME;
    }

    protected function process_items( array $args = array() ): void {
        if ( ! $this->cart_abandoned_webhook->is_enabled() ) {
            return;
        }

        $items = $args['items'] ?? array();

        if ( empty( $items ) ) {
            return;
        }

        foreach ( $items as $item ) {
            $this->cart_abandoned_webhook->send( $item['hash'] );
        }
    }

    public function schedule( array $args = array() ): void {
        $this->schedule_create_batch_action( 1, $args );
    }

    public function can_schedule( array $args = array() ): bool {
        return parent::can_schedule( $args ) && $this->cart_abandoned_webhook->is_enabled();
    }

    public function get_interval(): int {
        return self::JOB_INTERVAL_SECONDS;
    }
}
