<?php

namespace Hostinger\Reach\Admin;

use Hostinger\WpMenuManager\Menus as WpMenu;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class Menus {

    public function init(): void {
        add_filter( 'hostinger_menu_subpages', array( $this, 'add_sub_menu_page' ), 20 );
        add_filter( 'hostinger_admin_menu_bar_items', array( $this, 'add_admin_bar_items' ), 110 );
    }

    public function add_sub_menu_page( array $submenus ): array {
        $submenus[] = array(
            'page_title' => __( 'Email Marketing', 'hostinger-reach' ),
            'menu_title' => __( 'Email Marketing', 'hostinger-reach' ),
            'capability' => 'manage_options',
            'menu_slug'  => 'hostinger-reach',
            'callback'   => array( $this, 'render_plugin_content' ),
            'menu_order' => 10,
        );

        return $submenus;
    }

    public function add_admin_bar_items( array $menu_items ): array {
        $menu_items[] = array(
            'id'    => 'hostinger-reach',
            'title' => esc_html__( 'Email Marketing', 'hostinger-reach' ),
            'href'  => admin_url( 'admin.php?page=hostinger-reach' ),
        );

        return $menu_items;
    }

    public function render_plugin_content(): void {
        echo wp_kses_post( WpMenu::renderMenuNavigation() );

        ?>
        <div id="hostinger-reach-app" class="hostinger-reach-app"></div>
        <?php
    }

    public static function get_reach_admin_url(): string {
        return admin_url( 'admin.php?page=hostinger-reach' );
    }
}
