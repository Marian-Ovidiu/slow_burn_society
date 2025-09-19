<?php

namespace Hostinger\Reach\Admin\Surveys;

use Hostinger\Reach\Api\ApiKeyManager;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class SatisfactionSurvey extends Survey {
    protected function should_load_survey(): bool {
        if ( ! parent::should_load_survey() ) {
            return false;
        }

        return $this->is_hostinger_page() && $this->has_been_five_days_since_connection();
    }

    protected function get_score_question(): string {
        return __( 'How would you rate your experience with Hostinger Reach so far?', 'hostinger-reach' );
    }

    protected function get_comment_question(): string {
        return __( 'Any ideas or feedback to help us improve Hostinger Reach?', 'hostinger-reach' );
    }

    protected function get_id(): string {
        return 'hostinger_reach_survey_satisfaction';
    }

    protected function get_location(): string {
        return 'wordpress_hostinger_reach';
    }

    protected function has_been_five_days_since_connection(): bool {
        $connection_time = get_option( ApiKeyManager::API_CONNECTION_TIME_NAME, null );
        if ( is_null( $connection_time ) ) {
            return false;
        }

        return ( time() - $connection_time ) >= ( 5 * self::DAY_IN_SECONDS );
    }

    protected function is_hostinger_page(): bool {
        return str_contains( $_SERVER['REQUEST_URI'], 'hostinger' );
    }
}
