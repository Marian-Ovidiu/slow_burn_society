<?php

/**
 * Override this template by copying it to yourtheme/hostinger-reach/optin-checkbox.php
 */

namespace Hostinger\Reach;

use Hostinger\Reach\Integrations\WooCommerce\WooCommerceIntegration;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @var $is_opted_in bool Indicates if the user is opted in.
 */

if ( ! isset( $is_opted_in ) ) {
    $is_opted_in = false;
}

?>

<p class="hostinger-reach-optin form-row">
    <label for="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
        <input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" type="checkbox" name="hostinger_reach_optin" id="hostinger_reach_optin" <?php checked( isset( $_POST[ WooCommerceIntegration::OPTIN_KEY ] ) || $is_opted_in, true ); ?>  />
        <span class="hostinger-reach-optin__checkbox-text"><?php _e( 'Subscribe to our Newsletter', 'hostinger-reach' ); ?></span>
    </label>
</p>
