<?php

namespace Hostinger\Reach\Blocks;

use Hostinger\Reach\Functions;
use Hostinger\Reach\Setup\Assets;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

abstract class Block {
    public Assets $assets;
    public Functions $functions;
    public string $name;

    public function __construct( Assets $assets, Functions $functions ) {
        $this->assets    = $assets;
        $this->functions = $functions;
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }


    public function get_block_name(): string {
        return "hostinger-reach-$this->name-block";
    }

    public function enqueue_scripts(): void {
        $this->enqueue_block_style();
        $this->enqueue_block_script();
    }

    public function register(): void {
        register_block_type(
            HOSTINGER_REACH_PLUGIN_DIR . "frontend/blocks/$this->name-block/block.json",
            array(
                'render_callback' => array( $this, 'render' ),
            )
        );
    }

    public function enqueue_block(): void {
        if ( $this->functions->block_file_exists( "$this->name.js" ) === false ) {
            return;
        }

        wp_enqueue_script(
            $this->get_block_name() . '-editor',
            $this->functions->get_blocks_url() . "$this->name.js",
            array( 'react', 'wp-api-fetch', 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-i18n' ),
            filemtime( $this->functions->get_block_file_name( "$this->name.js" ) ),
            true
        );

        $this->enqueue_block_style();
        wp_set_script_translations( $this->get_block_name(), 'hostinger-reach', HOSTINGER_REACH_PLUGIN_DIR . 'languages' );

        $this->autoloader();
    }

    public function enqueue_block_style(): void {
        if ( $this->functions->block_file_exists( "$this->name.css" ) === false ) {
            return;
        }

        wp_enqueue_style(
            $this->get_block_name(),
            $this->functions->get_blocks_url() . "$this->name.css",
            array(),
            filemtime( $this->functions->get_block_file_name( "$this->name.css" ) )
        );
    }

    public function enqueue_block_script(): void {
        if ( $this->functions->block_file_exists( "$this->name-view.js" ) === false ) {
            return;
        }

        $handler = $this->get_block_name() . '-view';

        wp_enqueue_script(
            $handler,
            $this->functions->get_blocks_url() . "$this->name-view.js",
            array(),
            filemtime( $this->functions->get_block_file_name( "$this->name-view.js" ) ),
            true
        );

        wp_localize_script(
            $handler,
            "hostinger_reach_{$this->name}_block_data",
            $this->data()
        );
    }

    abstract public function autoloader(): void;
    abstract public function data(): array;
    abstract public function render( array $attributes ): bool|string;
}
