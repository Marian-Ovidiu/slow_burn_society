<?php

namespace Hostinger\Reach\Dto;

use WC_Product;

class CartItem {

    private WC_Product $product;
    private int $quantity;

    public function __construct( int $product_id, int $quantity ) {
        $this->product  = wc_get_product( $product_id );
        $this->quantity = $quantity;
    }

    public function to_array(): array {
        return array(
            'quantity'    => $this->quantity,
            'product_id'  => $this->product->get_id(),
            'sku'         => $this->product->get_sku(),
            'link'        => $this->product->get_permalink(),
            'name'        => $this->product->get_name(),
            'price'       => $this->product->get_price(),
            'description' => $this->product->get_description(),
            'thumbnail'   => $this->get_product_thumbnail( $this->product ),
            'tags'        => $this->get_product_tags( $this->product ),
            'categories'  => $this->get_categories( $this->product ),
        );
    }

    public function get_categories( object $product ): array {
        $categories   = array();
        $category_ids = $product->get_category_ids();

        if ( empty( $category_ids ) ) {
            return $categories;
        }

        foreach ( $category_ids as $category_id ) {
            $term = get_term( $category_id, 'product_cat' );

            if ( ! $term || is_wp_error( $term ) ) {
                continue;
            }

            $categories[] = array(
                'id'   => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'link' => get_term_link( $term ),
            );
        }

        return $categories;
    }

    public function get_product_thumbnail( object $product ): string {
        $image_data = $product->get_image_id() ? wp_get_attachment_image_src( $product->get_image_id(), 'full' ) : array();
        if ( ! empty( $image_data ) ) {
            return $image_data[0];
        }

        return '';
    }

    public function get_product_tags( object $product ): array {
        $tags    = array();
        $tag_ids = $product->get_tag_ids();

        if ( empty( $tag_ids ) ) {
            return $tags;
        }

        foreach ( $tag_ids as $tag_id ) {
            $term = get_term( $tag_id, 'product_tag' );

            if ( ! $term || is_wp_error( $term ) ) {
                continue;
            }

            $tags[] = array(
                'id'   => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
            );
        }

        return $tags;
    }
}
