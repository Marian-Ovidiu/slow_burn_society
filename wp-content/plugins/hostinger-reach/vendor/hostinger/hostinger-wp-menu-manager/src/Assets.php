<?php

namespace Hostinger\WpMenuManager;

use Hostinger\WpMenuManager\Menus;
use Hostinger\WpHelper\Utils;

class Assets
{
    /**
     * @var Manager
     */
    private Manager $manager;
    private string $assetsPath;

    /**
     * @return void
     */
    public function init(): void
    {
        if (!$this->manager->checkCompatibility()) {
            add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
            add_action('admin_head', [$this, 'addMenuHidingCss']);
        }
        $this->assetsPath = '/vendor/hostinger/hostinger-wp-menu-manager/assets';
    }

    /**
     * @param Manager $manager
     *
     * @return void
     */
    public function setManager(Manager $manager): void
    {
        $this->manager = $manager;
    }

    /**
     * @return void
     */
    public function enqueueAdminAssets(): void {
        $defaultVersion = '1.0.0';

        if ( $this->isHostingerMenuPage() ) {
            $pluginInfo   = $this->manager->getPluginInfo();
            $jsScriptPath = __DIR__ . '/../assets/js/menus.min.js';
            $cssStylePath = __DIR__ . '/../assets/css/style.min.css';

            $jsVersion = $defaultVersion;
            if (file_exists($jsScriptPath)) {
                $jsVersion = filemtime($jsScriptPath) ?: $jsVersion;
            }

            wp_enqueue_script( 'hostinger_menu_scripts',
                $pluginInfo . $this->assetsPath . '/js/menus.min.js',
                [ 'jquery' ],
                $jsVersion,
                false
            );

            $cssVersion = $defaultVersion;
            if (file_exists($cssStylePath)) {
                $cssVersion = filemtime($cssStylePath) ?: $cssVersion;
            }

            wp_enqueue_style( 'hostinger_menu_styles',
                $pluginInfo . $this->assetsPath . '/css/style.min.css',
                [],
                $cssVersion
            );

            // Hide notices and badges in Hostinger menu pages.
            $hide_notices = '.notice { display: none !important; } .hostinger-notice { display: block !important; }';
            wp_add_inline_style( 'hostinger_menu_styles', $hide_notices );

            if ( Utils::isPluginActive( 'wpforms' ) ) {
                $hide_wp_forms_counter = '.wp-admin #wpadminbar .wpforms-menu-notification-counter { display: none !important; }';
                wp_add_inline_style( 'hostinger_menu_styles', $hide_wp_forms_counter );
            }
            if ( Utils::isPluginActive( 'googleanalytics' ) ) {
                $hide_monsterinsights_notification = '.wp-admin .monsterinsights-menu-notification-indicator { display: none !important; }';
                wp_add_inline_style( 'hostinger_menu_styles', $hide_monsterinsights_notification );
            }
        }
    }

    /**
     * @return void
     */
    public function addMenuHidingCss(): void
    {
        // These CSS rules should be loaded on every page in WordPress admin.
        ?>
        <style type="text/css">
            body.hostinger-hide-main-menu-item #toplevel_page_hostinger .wp-submenu > .wp-first-item {
                display: none;
            }

            #wpadminbar #wp-admin-bar-hostinger_admin_bar .ab-item {
                align-items: center;
                display: flex;
            }

            #wpadminbar #wp-admin-bar-hostinger_admin_bar .ab-sub-wrapper .ab-item svg {
                fill: #9ca1a7;
                margin-left: 3px;
                max-height: 18px;
            }

            body.hostinger-hide-all-menu-items #toplevel_page_hostinger .wp-submenu {
                display: none !important;
            }

            body.hostinger-hide-all-menu-items .hsr-onboarding-navbar__wrapper {
                justify-content: center;
            }

            body.hostinger-hide-all-menu-items .hsr-onboarding-navbar .hsr-mobile-menu-icon,
            body.hostinger-hide-all-menu-items .hsr-onboarding-navbar .hsr-wrapper__list {
                display: none !important;
            }
        </style>
        <?php
    }

    /**
     * @return bool
     */
    private function isHostingerMenuPage(): bool
    {
        $admin_path = parse_url(admin_url(), PHP_URL_PATH);

        $pages = [
            $admin_path . 'admin.php?page=' . Menus::MENU_SLUG
        ];

        $subpages = Menus::getMenuSubpages();

        foreach ($subpages as $page) {
            if (isset($page['menu_slug'])) {
                $pages[] = $admin_path . 'admin.php?page=' . $page['menu_slug'];
            }
        }

        $utils = new Utils();

        foreach ($pages as $page) {
            if ($utils->isThisPage($page)) {
                return true;
            }
        }

        return false;
    }
}
