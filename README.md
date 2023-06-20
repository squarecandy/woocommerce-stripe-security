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
PURPOSE. See the full [LICENSE](https://github.com/squarecandy/woocommerce-stripe-security/blob/main/LICENSE).

## Part 1: Setup a Restricted Key

**Create a restricted Stripe API key**

* Login to your Stripe Dashboard and go to [Developers > API Keys](https://dashboard.stripe.com/test/apikeys)
* You may want to start with just a TEST key: Make sure your dashboard is in "Test mode".
* Click **+ Create restricted key**
* Give your key a meaningful name. Maybe "WooCommerce Key" or something similar.
* If you can see a "Connect Permissions" column, set everything to "None". If you don't use Stripe Connect on your account, consider contacting support to get help having the feature fully disabled/removed. Basically, unless you're building a custom webapp where you have B2B customers that are going to have their own stripe accounts with their own banks attached, you don't need it and shouldn't risk someone getting permission to use it in malicious ways.
* In the regular "Permissions" column, set all permissions to "None" except the following:
    * All core resources: Write
    * Checkout Sessions: only set to Write if you plan to use the Stripe Checkout feature; otherwise set to None.
    * Webhook Endpoints: Read
    * Radar: Write
* Click **Create key**

You'll eventually need to repeat this process again in LIVE mode to create your restricted production key. You can go ahead and do that now, or just move onward with your TEST key only and come back later to create the live one.

_Note: The settings above are still experimental. Please help us test them and report back in the Issues cue._

## Part 1a: Test the restricted keys

At this point, it would be good to just add your Test Restricted key as you normally would - by adding it in the WooCommerce > Settings > Payments panel.

Please stop at this point and help us test the restriction settings above before proceeding to the next steps.

Once things seem to be working smoothly, you can optionally complete the following steps to remove the keys from the WP database.

  
## Part 2: Install the keys via wp-config instead of storing in the database

First, install `squarecandy-wc-stripe-keys.php` as a must use plugin.

* if `wp-content/mu-plugins` does not exist yet, create the directory
* change the permissions on mu-plugins directory to 700. (Owner read/write only)
* copy `squarecandy-wc-stripe-keys.php` into the directory
* change the permissions of `squarecandy-wc-stripe-keys.php` to 400 (Owner read only)

You can do the above manually, or just run these commands:

```
# start in your WP install home directory
mkdir wp-content/mu-plugins
chmod 700 wp-content/mu-plugins
wget https://github.com/squarecandy/woocommerce-stripe-security/blob/main/squarecandy-wc-stripe-keys.php -P wp-content/mu-plugins
chmod 400 wp-content/mu-plugins/squarecandy-wc-stripe-keys.php
```

Next install your keys in your wp-config file. The values below are samples. Add your real key values below where you see the zeros.
You can do so by manually editing `wp-config.php` with the following lines:

```
define( 'WC_STRIPE_TEST_PUBLISHABLE_KEY', 'pk_test_00000000000000000000' );
define( 'WC_STRIPE_TEST_SECRET_KEY', 'rk_test_00000000000000000000' );
define( 'WC_STRIPE_TEST_WEBHOOK_SECRET', 'whsec_00000000000000000000' );
define( 'WC_STRIPE_PUBLISHABLE_KEY', 'pk_live_00000000000000000000' );
define( 'WC_STRIPE_SECRET_KEY', 'rk_live_00000000000000000000' );
define( 'WC_STRIPE_WEBHOOK_SECRET', 'whsec_000000000000' );
```

or via WP CLI like this if you already installed your keys in the dashboard settings:
(Note that the `--quiet` flag is to keep your keys from being logged in your command history. But you won't get Success or Fail messages back.)

```
# Test Publishable Key - get the existing value from the database
wp config set WC_STRIPE_TEST_PUBLISHABLE_KEY `wp option pluck woocommerce_stripe_settings test_publishable_key` --quiet

# Test Secret Key - use your new Restricted Key value.
wp config set WC_STRIPE_TEST_SECRET_KEY rk_test_00000000000000000000 --quiet

# Test Webhook - get the existing value from the database
wp config set WC_STRIPE_TEST_WEBHOOK_SECRET `wp option pluck woocommerce_stripe_settings test_webhook_secret` --quiet

# Live Publishable Key - get the existing value from the database
wp config set WC_STRIPE_PUBLISHABLE_KEY `wp option pluck woocommerce_stripe_settings publishable_key` --quiet

# Live Secret Key - use your new Restricted Key value.
wp config set WC_STRIPE_SECRET_KEY rk_live_00000000000000000000 --quiet

# Live Webhook - get the existing value from the database
wp config set WC_STRIPE_WEBHOOK_SECRET `wp option pluck woocommerce_stripe_settings webhook_secret` --quiet
```

or via WP CLI like this if you're starting from a fresh install:

```
wp config set WC_STRIPE_TEST_PUBLISHABLE_KEY pk_test_00000000000000000000 --quiet
wp config set WC_STRIPE_TEST_SECRET_KEY rk_test_00000000000000000000 --quiet
wp config set WC_STRIPE_TEST_WEBHOOK_SECRET whsec_00000000000000000000 --quiet
wp config set WC_STRIPE_PUBLISHABLE_KEY pk_live_00000000000000000000 --quiet
wp config set WC_STRIPE_SECRET_KEY rk_live_00000000000000000000 --quiet
wp config set WC_STRIPE_WEBHOOK_SECRET whsec_00000000000000000000 --quiet
```

  
## Part 3: Clean Up the old insecure Standard Keys

### Delete the old values from the database

The manual method of deleting the values from the database is to go to  
**Woocommerce > Settings > Payments > Stripe > Manage > Settings > Edit Account Keys**

For now - in order to fool the plugin, you'll need to put in fake keys with the correct prefixes and save them to the db.

Just delete all of the live values, replace them with fake keys with proper prefixes and click Save Live Keys. Do the same with the test values and click Save Test Keys.

Or you can do it quickly via WP CLI like this:

```
wp option patch insert woocommerce_stripe_settings test_publishable_key 'pk_test_123placeholder'
wp option patch insert woocommerce_stripe_settings test_secret_key 'rk_test_123placeholder'
wp option patch insert woocommerce_stripe_settings test_webhook_secret 'whsec_123placeholder'
wp option patch insert woocommerce_stripe_settings publishable_key 'pk_live_123placeholder'
wp option patch insert woocommerce_stripe_settings secret_key 'rk_live_123placeholder'
wp option patch insert woocommerce_stripe_settings webhook_secret 'whsec_123placeholder'
```

**Note: once you do this, you'll start getting warnings in the dashboard about stripe keys being invalid. Ignore this and just confirm that things are working as expected by just testing the site on the front end. Hopefully we can get WC to address this soon.**

### Roll any Standard Keys

Once you have **FULLY TESTED** that everything is working as expected, you can go back to https://dashboard.stripe.com/test/apikeys and under Standard Keys > Secret Key, click `•••` and choose Roll key. 

Warning: don't proceed with this step until you're ready and unless you really understand the potential consequenses.

## Roadmap

* Change the GUI fields to disabled and provide information that the keys are being served via wp-config
* Convince WC team to support wp-config keys without a bunch of workarounds and warning messages
* MU Plugins for other Stripe integrations. We plan to do GiveWP. Pull requests welcome for other platforms.
