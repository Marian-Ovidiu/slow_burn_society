<?php

namespace Hostinger\Reach\Api\Webhooks\Handlers;

use Hostinger\Reach\Api\Handlers\ReachApiHandler;

abstract class WebhookHandler {

    protected ReachApiHandler $reach_api_handler;

    public function __construct( ReachApiHandler $reach_api_handler ) {
        $this->reach_api_handler = $reach_api_handler;
    }

    public function init(): void {
        add_action( 'init', array( $this, 'init_hooks' ) );
    }

    public function is_enabled(): bool {
        return $this->reach_api_handler->is_connected();
    }

    public function handle( string $email, mixed $data ): bool {
        $webhook_payload = array(
            'name'     => $this->get_name(),
            'contact'  => array(
                'email' => $email,
            ),
            'metadata' => $this->get_metadata( $data ),
        );

        $response = $this->reach_api_handler->post_webhook_event( $webhook_payload );
        return ! $response->is_error();
    }

    abstract public function init_hooks(): void;
    abstract public function get_metadata( mixed $data ): array;
    abstract public function get_name(): string;
}
