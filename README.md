# woocommerce-stripe-security

This documentation for Stripe setup and accompanying mu-plugin resolves two major security issues identified in the default Stripe setup with WooCommerce and other WP Plugins:

1) Stripe's default "Standard Key" API key system allows you to take all actions on their system. This allows for massive potential abuse if the keys ever get compromised, such as the [incident documented by Shannon Mattern](https://webdesigneracademy.com/my-stripe-account-was-hacked-and-stripe-said-i-have-to-repay-70k/).
2) WooCommerce default storage for Stripe keys is in the WordPress database in plain text. This means any compromise of the database is also a compromise of your Stripe API keys and a potential major financial liablity.

*Note: this is not a normal WordPress Plugin. You must install everything according to the instructions below.*

## Part 0: Disclaimer

Always test everything thoroughly on a staging copy of your site before moving to production.
Delete existing keys at your own risk. Remember that one set of keys may be in use by multiple platforms or systems.

Nothing presented here is legal or financial advice.

Everything here is provided "AS IS" WITHOUT WARRANTY
OF ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO,
THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
PURPOSE. See the full LICENSE.

## Part 1: Setup a Restricted Key

**Create a restricted Stripe API key**



## Part 2: Install the keys via wp-config instead of storing in the database

First, install `squarecandy-wc-stripe-keys.php` as a must use plugin.

* if `wp-content/mu-plugins` does not exist yet, create the directory
* change the permissions on mu-plugins directory to 700. (Owner read/write only)
* copy `squarecandy-wc-stripe-keys.php` into the directory
* change the permissions of `squarecandy-wc-stripe-keys.php` to 400 (Owner read only)

You can do the above manually, or from the command line:

```
mkdir wp-content/mu-plugins
chmod 700 wp-content/mu-plugins
wget https://github.com/squarecandy/woocommerce-stripe-security/blob/main/squarecandy-wc-stripe-keys.php -P wp-content/mu-plugins
chmod 400 wp-content/mu-plugins/squarecandy-wc-stripe-keys.php
```

Next install your keys in your wp-config file. The values below are samples. Add your real key values below.
You can do so by manually editing `wp-config.php` with the following lines:

```
define( 'WC_STRIPE_TEST_PUBLISHABLE_KEY', 'pk_test_000000000000000000000000000000000000000000000000000000000000000000' );
define( 'WC_STRIPE_TEST_SECRET_KEY', 'rk_test_000000000000000000000000000000000000000000000000000000000000000000' );
define( 'WC_STRIPE_TEST_WEBHOOK_SECRET', 'whsec_00000000000000000000000000000000' );
define( 'WC_STRIPE_PUBLISHABLE_KEY', 'pk_live_000000000000000000000000000000000000000000000000000000000000000000' );
define( 'WC_STRIPE_SECRET_KEY', 'rk_live_000000000000000000000000000000000000000000000000000000000000000000' );
define( 'WC_STRIPE_WEBHOOK_SECRET', 'whsec_00000000000000000000000000000000' );
```

or via WP CLI like this if you already installed your keys in the dashboard settings:
(Note that the `--quiet` flag is important to keep your keys from being logged in your command history)

```
# Test Publishable Key - get the existing value from the database
wp config set WC_STRIPE_TEST_PUBLISHABLE_KEY `wp option pluck woocommerce_stripe_settings test_publishable_key` --quiet

# Test Secret Key - use your new Restricted Key value.
wp config set WC_STRIPE_TEST_SECRET_KEY rk_test_000000000000000000000000000000000000000000000000000000000000000000 --quiet

# Test Webhook - get the existing value from the database
wp config set WC_STRIPE_TEST_WEBHOOK_SECRET `wp option pluck woocommerce_stripe_settings test_webhook_secret` --quiet

# Live Publishable Key - get the existing value from the database
wp config set WC_STRIPE_PUBLISHABLE_KEY `wp option pluck woocommerce_stripe_settings publishable_key` --quiet

# Live Secret Key - use your new Restricted Key value.
wp config set WC_STRIPE_SECRET_KEY rk_live_000000000000000000000000000000000000000000000000000000000000000000 --quiet

# Live Webhook - get the existing value from the database
wp config set WC_STRIPE_WEBHOOK_SECRET `wp option pluck woocommerce_stripe_settings webhook_secret` --quiet
```

or via WP CLI like this if you're starting from a fresh install:

```
# Test Publishable Key - get the existing value from the database
wp config set WC_STRIPE_TEST_PUBLISHABLE_KEY pk_test_000000000000000000000000000000000000000000000000000000000000000000 --quiet

# Test Secret Key - use your new Restricted Key value.
wp config set WC_STRIPE_TEST_SECRET_KEY rk_test_000000000000000000000000000000000000000000000000000000000000000000 --quiet

# Test Webhook - get the existing value from the database
wp config set WC_STRIPE_TEST_WEBHOOK_SECRET whsec_00000000000000000000000000000000 --quiet

# Live Publishable Key - get the existing value from the database
wp config set WC_STRIPE_TEST_PUBLISHABLE_KEY pk_live_000000000000000000000000000000000000000000000000000000000000000000 --quiet

# Live Secret Key - use your new Restricted Key value.
wp config set WC_STRIPE_SECRET_KEY rk_live_000000000000000000000000000000000000000000000000000000000000000000 --quiet

# Live Webhook - get the existing value from the database
wp config set WC_STRIPE_WEBHOOK_SECRET whsec_00000000000000000000000000000000 --quiet
```

## Part 3: Clean Up the old insecure Standard Keys

### Delete the old values from the database

The manual method of deleting the values from the database is to go to  
**Woocommerce > Settings > Payments > Stripe > Manage > Settings > Edit Account Keys**

For now - in order to fool the plugin, you'll need to put in fake keys with the correct prefixes and save them to the db.

Just delete all of the live values, replace them with fake keys with proper prefixes and click Save Live Keys. Do the same with the test values and click Save Test Keys.

Or you can do it quickly via WP CLI like this:

```
wp option patch insert woocommerce_stripe_settings test_publishable_key 'pk_test_00000'
wp option patch insert woocommerce_stripe_settings test_secret_key 'rk_test_00000'
wp option patch insert woocommerce_stripe_settings publishable_key 'pk_live_00000'
wp option patch insert woocommerce_stripe_settings secret_key 'rk_live_00000'
wp option patch insert woocommerce_stripe_settings test_webhook_secret 'whsec_00000'
wp option patch insert woocommerce_stripe_settings webhook_secret 'whsec_00000'
```

### Roll any Standard Keys

Once you have **FULLY TESTED** that everything is working as expected, you can go back to https://dashboard.stripe.com/test/apikeys and under Standard Keys > Secret Key, click `•••` and choose Roll key. Seriously though - don't proceed with this step until you're ready and unless you really understand the potential consequenses.

## Roadmap

* Change the GUI fields to disabled and provide information that the keys are being served via wp-config
