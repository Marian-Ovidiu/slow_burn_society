<?php

namespace Widget;

use WP_Widget;
use Core\App;

class MenuWidget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'menu_widget',
            __('Menu Widget', 'text_domain'),
            ['description' => __('A widget to display a menu', 'text_domain')]
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        $menu_name = camelToKebab($instance['menu_name']) ?: 'header-menu';
        $menu_items_raw = wp_get_nav_menu_items($menu_name);
        $menu_items = [];
        if ($menu_items_raw) {
            $menu_items = $this->buildMenuTree($menu_items_raw);
        }
        echo App::blade('view')->make('partials.' . $menu_name, ['menu' => $menu_items])->render();
        echo $args['after_widget'];
    }

    private function buildMenuTree($items, $parent_id = 0)
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item->menu_item_parent == $parent_id) {
                $children = $this->buildMenuTree($items, $item->ID);
                if (!empty($children)) {
                    $item->children = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
