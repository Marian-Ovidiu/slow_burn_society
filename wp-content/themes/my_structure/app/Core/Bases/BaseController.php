<?php

namespace Core\Bases;

use Core\App;

abstract class BaseController
{
    public $postType  = 'post';
    public function __construct() {}

    public static function call($method, $params = [])
    {
        $instance = new static;
        if (method_exists($instance, $method)) {
            call_user_func_array([$instance, $method], $params);
        } else {
            throw new \Exception("Method $method not found in " . static::class);
        }
    }

    // Core\Bases\BasePostType.php
    public static function findBySlug(string $slug)
    {
        // 1) tentativo veloce
        $post = get_page_by_path($slug, OBJECT, static::$postType);
        if ($post) return new static($post);

        // 2) fallback robusto (gestisce anche traduzioni/permalink “strani”)
        $q = new \WP_Query([
            'post_type'      => static::$postType,
            'name'           => $slug,
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'no_found_rows'  => true,
        ]);
        if (!empty($q->posts[0])) {
            $post = $q->posts[0];
            wp_reset_postdata();
            return new static($post);
        }
        wp_reset_postdata();
        return null;
    }


    protected function addCss($handle, $src, $deps = [], $ver = false)
    {
        if (filter_var($src, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//', $src)) {
            $fullSrc = $src;
        } else {
            $fullSrc = get_template_directory_uri() . '/source/assets/css/' . ltrim($src, '/');
        }
        wp_enqueue_style($handle, $fullSrc, $deps, $ver);
    }

    protected function addJs($handle, $src, $deps = [], $in_footer = false, $ver = false)
    {
        if (filter_var($src, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//', $src)) {
            $fullSrc = $src;
        } else {
            $relativePath = '/source/assets/js/' . ltrim($src, '/');
            $fullSrc = get_template_directory_uri() . $relativePath;
            if (!$ver) {
                $filePath = get_template_directory() . $relativePath;
                if (file_exists($filePath)) {
                    $ver = filemtime($filePath);
                }
            }
        }

        add_action('wp_enqueue_scripts', function () use ($handle, $fullSrc, $deps, $ver, $in_footer) {
            wp_enqueue_script($handle, $fullSrc, $deps, $ver, $in_footer);
        });
    }


    protected function addVarJs($handle, $var_name, $data, $in_footer = false, $ver = false)
    {
        add_action('wp_enqueue_scripts', function () use ($handle, $var_name, $data, $ver, $in_footer) {
            // Enqueue lo script (deve essere registrato o già enqueued altrove)
            wp_enqueue_script($handle, false, [], $ver, $in_footer);

            // Localizzazione delle variabili
            wp_localize_script($handle, $var_name, $data);
        });
    }


    protected function render($view, $data = [])
    {
        echo App::blade()->make($view, $data)->render();
    }
}
