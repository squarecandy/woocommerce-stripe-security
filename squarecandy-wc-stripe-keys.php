<?php
/**
 * Plugin Name: SquareCandy Stripe Keys Security
 * Description: Allows the Stripe settings to be loaded from env vars or constants defined in wp-config.php
 * Author:      squarecandy
 * Plugin URI:  https://github.com/squarecandy/woocommerce-stripe-security
 * License:     GNU General Public License v3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Basic security, prevents file from being loaded directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_plugins = (array) get_option( 'active_plugins', array() );

// If the WooCommerce Stripe is active
if ( in_array( 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php', $active_plugins, true ) ) :

	// set the stripe keys and webhooks via env vars or wp-config constants
	add_filter( 'option_woocommerce_stripe_settings', 'squarecandy_woocommerce_stripe_settings' );
	function squarecandy_woocommerce_stripe_settings( $settings ) {

		$subfields = array(
			'test_publishable_key',
			'test_secret_key',
			'publishable_key',
			'secret_key',
			'test_webhook_secret',
			'webhook_secret',
		);

		$settings = squarecandy_iterate_settings_changes( $settings, $subfields, 'WC_STRIPE_' );

		return $settings;
	}

endif;

function squarecandy_iterate_settings_changes( $settings, $subfields, $prefix ) {
	foreach ( $subfields as $subfield ) {
		// construct the uppercase constant slug
		$const_name = $prefix . mb_strtoupper( $subfield );

		if ( ! empty( getenv( $const_name ) ) ) {
			// use the environment variable if it exists
			$settings[ $subfield ] = getenv( $const_name );
		} elseif ( defined( $const_name ) && ! empty( constant( $const_name ) ) ) {
			// otherwise use the constant if it exists
			$settings[ $subfield ] = constant( $const_name );
		}
	}

	return $settings;
}
