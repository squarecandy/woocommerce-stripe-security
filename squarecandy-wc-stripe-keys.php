<?php
/**
 * Plugin Name: SquareCandy Stripe Keys Security for WooCommerce
 * Description: Allows the WC Stripe settings to be loaded from constants defined in wp-config.php
 * Author:      squarecandy
 * License:     GNU General Public License v3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Basic security, prevents file from being loaded directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// set the stripe keys via wp-config
add_filter( 'option_woocommerce_stripe_settings', 'squarecandy_woocommerce_stripe_settings' );
function squarecandy_woocommerce_stripe_settings( $settings ) {

	if ( defined( 'WC_STRIPE_TEST_PUBLISHABLE_KEY' ) ) {
		$settings['test_publishable_key'] = WC_STRIPE_TEST_PUBLISHABLE_KEY;
	}

	if ( defined( 'WC_STRIPE_TEST_SECRET_KEY' ) ) {
		$settings['test_secret_key'] = WC_STRIPE_TEST_SECRET_KEY;
	}

	if ( defined( 'WC_STRIPE_TEST_WEBHOOK_SECRET' ) ) {
		$settings['test_webhook_secret'] = WC_STRIPE_TEST_WEBHOOK_SECRET;
	}

	if ( defined( 'WC_STRIPE_PUBLISHABLE_KEY' ) ) {
		$settings['publishable_key'] = WC_STRIPE_PUBLISHABLE_KEY;
	}

	if ( defined( 'WC_STRIPE_SECRET_KEY' ) ) {
		$settings['secret_key'] = WC_STRIPE_SECRET_KEY;
	}

	if ( defined( 'WC_STRIPE_WEBHOOK_SECRET' ) ) {
		$settings['webhook_secret'] = WC_STRIPE_WEBHOOK_SECRET;
	}

	return $settings;
}
