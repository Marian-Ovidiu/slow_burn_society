<?php

namespace Hostinger\Reach\Providers;


use Hostinger\Reach\Admin\Notices\ConnectionNotice;
use Hostinger\Reach\Api\Handlers\ReachApiHandler;
use Hostinger\Reach\Container;
use Hostinger\Reach\Functions;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class NoticesProvider implements ProviderInterface {

    public const NOTICES = array(
        ConnectionNotice::class,
    );

    public function register( Container $container ): void {
        foreach ( self::NOTICES as $notice_class ) {
            $container->set(
                $notice_class,
                function () use ( $container, $notice_class ) {
                    return new $notice_class(
                        $container->get( ReachApiHandler::class ),
                        $container->get( Functions::class )
                    );
                }
            );

            $notice = $container->get( $notice_class );
            $notice->init();
        }
    }
}
