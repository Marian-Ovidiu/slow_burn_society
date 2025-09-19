<?php
namespace Hostinger\Reach\Setup;

use Hostinger\Reach\Blocks\SubscriptionFormBlock;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Blocks {

    private SubscriptionFormBlock $subscription_form_block;

    public function __construct( SubscriptionFormBlock $subscription_form_block ) {
        $this->subscription_form_block = $subscription_form_block;
    }

    public function init(): void {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        add_action( 'init', array( $this, 'register_blocks' ) );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_assets' ) );
    }

    public function register_blocks(): void {
        $this->subscription_form_block->register();
    }

    public function enqueue_block_assets(): void {
        $this->subscription_form_block->enqueue_block();
    }
}
