<?php

namespace Hostinger\Reach\Admin\Surveys;

use Hostinger\Surveys\SurveyManager;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

abstract class Survey {

    protected const DAY_IN_SECONDS = 86400;

    private SurveyManager $survey_manager;

    public function __construct( SurveyManager $survey_manager ) {
        $this->survey_manager = $survey_manager;
    }

    public function init(): void {
        add_filter( 'hostinger_add_surveys', array( $this, 'add_survey' ) );
    }

    public function add_survey( array $surveys ): array {
        if ( $this->should_load_survey() ) {
            $surveys[] = $this->get_survey();
        }

        return $surveys;
    }

    protected function get_priority(): int {
        return 100;
    }

    protected function should_load_survey(): bool {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return false;
        }

        $is_client_eligible = $this->survey_manager->isClientEligible();
        $is_not_completed   = $this->survey_manager->isSurveyNotCompleted( $this->get_id() );
        $is_hidden          = $this->survey_manager->isSurveyHidden();

        return $is_client_eligible && $is_not_completed && ! $is_hidden;
    }

    protected function get_survey(): array {
        return $this->survey_manager->addSurvey(
            $this->get_id(),
            $this->get_score_question(),
            $this->get_comment_question(),
            $this->get_location(),
            $this->get_priority(),
            $this->get_review_url(),
            $this->get_review_min_required_score()
        );
    }

    abstract protected function get_score_question(): string;

    abstract protected function get_comment_question(): string;

    abstract protected function get_id(): string;

    abstract protected function get_location(): string;
    abstract protected function get_review_url(): string;
    abstract protected function get_review_min_required_score(): int;
}
