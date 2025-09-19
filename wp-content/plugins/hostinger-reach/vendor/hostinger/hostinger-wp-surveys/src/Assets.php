<?php

namespace Hostinger\Surveys;

class Assets
{
    public Loader $surveys;
    private string $assetsPath;
    public function init(): void
    {
        add_action('admin_enqueue_scripts', [ $this, 'enqueueAdminAssets' ]);
        $this->assetsPath = '/vendor/hostinger/hostinger-wp-surveys/assets';
    }

    /**
     * @return void
     */
    public function enqueueAdminAssets(): void {
        $pluginInfo     = $this->surveys->getPluginInfo();
        $defaultVersion = '1.0.0';
        $jsScriptPath   = __DIR__ . '/../assets/js/hostinger-surveys.min.js';
        $cssStylePath   = __DIR__ . '/../assets/css/style.min.css';
        $jsVersion      = $defaultVersion;

        if ( file_exists( $jsScriptPath ) ) {
            $jsVersion = filemtime( $jsScriptPath ) ?: $defaultVersion;
        }

        wp_enqueue_script( 'hostinger_surveys_scripts', $pluginInfo . $this->assetsPath .'/js/hostinger-surveys.min.js', [ 'jquery' ], $jsVersion, [ 'strategy' => 'defer' ] );

        wp_localize_script( 'hostinger_surveys_scripts', 'hostingerContainer', [
            'url'   => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'hts-ajax-nonce' ),
        ] );

        $cssVersion  = $defaultVersion;

        if ( file_exists( $cssStylePath ) ) {
            $cssVersion = filemtime( $cssStylePath ) ?: $defaultVersion;
        }

        wp_enqueue_style( 'hostinger_surveys_styles', $pluginInfo . $this->assetsPath . '/css/style.min.css', [], $cssVersion );
    }
}
