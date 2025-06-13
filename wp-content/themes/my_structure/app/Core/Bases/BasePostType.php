<?php
namespace Core\Bases;

use WP_Query;

abstract class BasePostType
{
    public static $postType = 'post';

    public $id;
    public $title;
    public $content;
    public $featured_image;
    public $url;
    protected $post;

    public function __construct($post = null)
    {
        if ($post) {
            if (is_array($post)) {
                $post = (object) $post;
            }
            $this->setAttribute($post);
            $this->post = $post;
        }
    }

    protected function setAttribute($post)
    {
        $this->id             = $post->ID ?? '';
        $this->title          = $post->post_title ?? '';
        $this->content        = $post->post_content ?? '';
        $this->featured_image = has_post_thumbnail($post) ? get_the_post_thumbnail_url($post) : $this->getDefaultImage();
        $this->url            = get_permalink($post);

        $this->defineOtherAttributes($post);
    }

    abstract function defineOtherAttributes($post);

    public static function all($args = [])
    {
        $defaults = [
            'post_type'      => static::$postType,
            'posts_per_page' => -1,
        ];

        return static::where(wp_parse_args($args, $defaults));
    }
    public static function find($id)
    {
        $post = get_post($id);
        return $post ? new static($post) : null;
    }

    public static function where($args = [])
    {
        $defaults = [
            'post_type'   => static::$postType,
            'post_status' => 'publish',
        ];

        if (function_exists('pll_current_language')) {
            $defaults['lang'] = pll_current_language();
        } else {
            $defaults['lang'] = 'it';
        }

        $queryArgs = wp_parse_args($args, $defaults);
        $query     = new WP_Query($queryArgs);

        return static::mapPosts($query);
    }
    public static function mapPosts($query)
    {
        if ($query instanceof WP_Query) {
            $posts = $query->posts;
        } elseif (is_array($query)) {
            $posts = $query;
        } else {
            return [];
        }

        return array_map(function ($post) {
            return new static($post);
        }, $posts);
    }
    protected function getDefaultImage()
    {
        return get_template_directory_uri() . '/assets/images/placeholder.png';
    }

}
