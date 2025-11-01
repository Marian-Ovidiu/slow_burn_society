<?php

namespace Hostinger\Reach\Providers;

use Hostinger\Reach\Admin\Surveys\SatisfactionSurvey;
use Hostinger\Reach\Admin\Surveys\Survey;
use Hostinger\Reach\Container;
use Hostinger\Surveys\SurveyManager;
use Hostinger\WpHelper\Config;
use Hostinger\WpHelper\Constants;
use Hostinger\WpHelper\Requests\Client;
use Hostinger\WpHelper\Utils as Helper;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class SurveysProvider implements ProviderInterface {

    public const SURVEY_CLASSES = array(
        SatisfactionSurvey::class,
    );

    public function register( Container $container ): void {
        $container->set(
            Client::class,
            function () use ( $container ) {
                return new Client(
                    $container->get( Config::class )->getConfigValue(
                        'base_rest_uri',
                        Constants::HOSTINGER_REST_URI
                    ),
                    array(
                        Config::TOKEN_HEADER  => $container->get( Helper::class )->getApiToken(),
                        Config::DOMAIN_HEADER => $container->get( Helper::class )->getHostInfo(),
                    )
                );
            }
        );
        foreach ( self::SURVEY_CLASSES as $survey_class ) {
            $container->set(
                $survey_class,
                function () use ( $container, $survey_class ) {
                    return new $survey_class( $container->get( SurveyManager::class ) );
                }
            );

            /** @var Survey $survey */
            $survey = $container->get( $survey_class );
            $survey->init();
        }
    }
}
