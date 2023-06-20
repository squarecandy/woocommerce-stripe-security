<?php
add_filter('option_woocommerce_stripe_settings', 'squarecandy_woocommerce_stripe_settings');
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
