<?php

namespace Hostinger\Reach\Jobs;

defined( 'ABSPATH' ) || exit;

class ActionScheduler {

    public const STATUS_PENDING  = 'pending';
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_FAILED   = 'failed';

    public function get_group(): string {
        return defined( 'HOSTINGER_REACH_PLUGIN_SLUG' ) ? HOSTINGER_REACH_PLUGIN_SLUG : 'hostinger-reach';
    }

    public function schedule_recurring( int $interval, string $hook, array $args = array() ): int {
        if ( ! function_exists( 'as_schedule_recurring_action' ) ) {
            return 0;
        }

        $group = $this->get_group();
        return as_schedule_recurring_action( gmdate( 'U' ) - 1, $interval, $hook, $args, $group, true );
    }

    public function schedule_single( int $timestamp, string $hook, array $args = array() ): int {
        if ( ! function_exists( 'as_schedule_single_action' ) ) {
            return 0;
        }

        return as_schedule_single_action( $timestamp, $hook, $args, $this->get_group() );
    }

    public function schedule_immediate( string $hook, array $args = array() ): int {
        if ( ! function_exists( 'as_schedule_single_action' ) ) {
            return 0;
        }

        return as_schedule_single_action( gmdate( 'U' ) - 1, $hook, $args, $this->get_group() );
    }

    public function has_scheduled_action( string $hook, array $args = array() ): bool {
        if ( ! function_exists( 'as_next_scheduled_action' ) ) {
            return false;
        }

        return as_next_scheduled_action( $hook, $args, $this->get_group() ) !== false;
    }
}
