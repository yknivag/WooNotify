=== MQTT WooCommerce Alerts ===
Contributors: yknivag
Donate link: https://www.paypal.me/yknivag
Tags: woocommerce, mqtt, shiftr.io, notify, notifier
Requires PHP: 7.0
Requires at least: 5.2
Tested up to: 5.4
Stable tag: 0.1.0
License: LGPLv3 or later
License URI: https://www.gnu.org/licenses/lgpl-3.0.en.html

== Description ==

# WooShiftrMQTT

WooCommerce plugin which sends messages to a shiftr.io MQTT instance on order state changes and for low stock/out of stock nofifications.

The following topics are published to MQTT:
* <topic_prefix>/orders/payment-pending
* <topic_prefix>/orders/on-hold
* <topic_prefix>/orders/processing
* <topic_prefix>/orders/completed
* <topic_prefix>/orders/cancelled
* <topic_prefix>/orders/refunded
* <topic_prefix>/orders/failed
* <topic_prefix>/stock/low
* <topic_prefix>/stock/out

A message is published to a topic when an order or product transitions to that state.  The payload of the message is either the order id or the product_id.

The data transmitted is kept to the bear minimum to limit the data exposure over MQTT.  Further information may be retrieved by the end point using the standard WooCommerce API by querying the order or product by id.

== Installation ==

1. Upload plugin `simple-paypal-recurring-donations` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin using the 'Plugins' menu in your WordPress admin panel.
3. You can adjust the necessary settings using your WordPress admin panel in "MQTTWoo".
4. Create a page or a post, customize button settings and insert generated shortcode into the text.

== Frequently Asked Questions ==

= Why use MQTT instead of the API =
Calls to the API put a load on the server and to make a "real-time" update the API would have to be called at least every 5 minuntes and that would put a substantial load on the server.
The idea behind this plugin is to use MQTT to push an event notification so that the API need only be called when a change is detected.

= Why shiftr.io =
It's free and it works well.  In essence the plugin posts to a URL using basic authentication and so could be used with any other MQTT broker with an HTTP interface that follows the same pattern.  Or your own server.

= What is the topic prefix =
By default the topic prefix is empty, but it may be set to allow the topics to fit in with an existing MQTT infrastructure.

== Changelog ==

= 0.1.0 - 20th June 2020 =
* Initial Release.
