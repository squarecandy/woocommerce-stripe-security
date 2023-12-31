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
* In the regular "Permissions" column, set all permissions to "None" except the following:
    * All core resources: Write
    * Checkout Sessions: only set to Write if you plan to use the Stripe Checkout feature; otherwise set to None.
    * All Connect resources: Read
    * Webhook Endpoints: Read
    * Radar: Write
* If you can see a second "Connect Permissions" column in addition the regular "Permissions" column - that means you have a Platform account setup. If you don't know what that means and you didn't do it on purpose, that could be big trouble. See below! For now, mark all permissions to NONE in the Connect Permissions column.
* Click **Create key**

You'll eventually need to repeat this process again in LIVE mode to create your restricted production key. You can go ahead and do that now, or just move onward with your TEST key only and come back later to create the live one.

_Note: The settings above are still experimental. Please help us test them and report back in the Issues cue._

### Stripe Connect "Platform" Mode

If you can see a "Connect Permissions" column when you create a new restricted key, that means your account is in "Platform" mode.
Platform mode is intended for B2B SaaS companies and not for normal individual accounts. Enabling Platform mode opens up a whole new realm of features that can be exploited. And if you're using "Standard Keys" - anyone who gets ahold of those keys has access to these features, including creating independent sub-accounts that can be connected to the users separate bank, and also their Captial Loans features and Instant Payouts.

https://stripe.com/connect

If you didn't set this up intentionally, you're in a pretty bad spot. Stripe support has informed us that there's no way to disable Platform mode once you're in it - the only things you can do are to exclusively use Restricted Keys that disallow these features, or to start a new Stripe account. It's worth considering taking the time to start a new account if you don't intend to ever use the Platform features.

  
### Test the restricted keys

At this point, it would be good to just add your Test Restricted key as you normally would - by adding it in the WooCommerce > Settings > Payments panel.

Please stop at this point and help us test the restriction settings above before proceeding to the next steps. Turn on Logging in the Stripe advanced settings and keep look in the WC logs and your server logs for errors after you run some test transactions.

Once things seem to be working smoothly, you can optionally complete the following steps to remove the keys from the WP database.

  
## Part 2: Install the keys via env vars or wp-config instead of storing in the database

### 2a - MU Plugin

First, install `squarecandy-wc-stripe-keys.php` as a must use plugin.

You can drop the file into your wp-content/mu-plugins directory manually, or run these commands:

```
# start in your WP install home directory
mkdir wp-content/mu-plugins
chmod 700 wp-content/mu-plugins
wget https://github.com/squarecandy/woocommerce-stripe-security/blob/main/squarecandy-wc-stripe-keys.php -P wp-content/mu-plugins
chmod 400 wp-content/mu-plugins/squarecandy-wc-stripe-keys.php
```

### 2b - Install Credentials

The best way to handle your private API keys on the server is with Environment Variables. If this is not possible in your hosting setup, you can install them in your wp-config file instead.

**Env Variables - Apache**

Find your site's existing vhost setup file. It might look something like this:

```
<VirtualHost *:443>
DocumentRoot "/var/www/vhosts/example.com/httpdocs"
ServerName example.com
# etc... more settings here
</VirtualHost>
```

Insert the required env variables here inside `<VirtualHost>`

```
SetEnv WC_STRIPE_TEST_PUBLISHABLE_KEY "pk_test_00000000000000000000"
SetEnv WC_STRIPE_TEST_SECRET_KEY "rk_test_00000000000000000000"
SetEnv WC_STRIPE_TEST_WEBHOOK_SECRET "whsec_00000000000000000000"

SetEnv WC_STRIPE_PUBLISHABLE_KEY "pk_live_00000000000000000000"
SetEnv WC_STRIPE_SECRET_KEY "rk_live_00000000000000000000"
SetEnv WC_STRIPE_WEBHOOK_SECRET "whsec_00000000000000000000"
```

**Env Variables - Nginx/PHP-FPM**

_coming soon... see https://stackoverflow.com/questions/8098927/nginx-variables-similar-to-setenv-in-apache_

**Using wp-config**

Install your keys in your wp-config file. The values below are samples. Add your real key values below where you see the zeros.
You can do so by manually editing `wp-config.php` with the following lines:

```
define( 'WC_STRIPE_TEST_PUBLISHABLE_KEY', 'pk_test_00000000000000000000' );
define( 'WC_STRIPE_TEST_SECRET_KEY', 'rk_test_00000000000000000000' );
define( 'WC_STRIPE_TEST_WEBHOOK_SECRET', 'whsec_00000000000000000000' );
define( 'WC_STRIPE_PUBLISHABLE_KEY', 'pk_live_00000000000000000000' );
define( 'WC_STRIPE_SECRET_KEY', 'rk_live_00000000000000000000' );
define( 'WC_STRIPE_WEBHOOK_SECRET', 'whsec_000000000000' );
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
* Convince WC team to support wp-config keys without a workaround plugin
* MU Plugins for other Stripe integrations. We plan to do GiveWP. Pull requests welcome for other platforms.
