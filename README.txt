=== Woongkir ===
Contributors: sofyansitorus
Tags: woocommerce shipping,indonesia shipping, jne shipping, tiki shipping, pos shipping
Requires at least: 4.8
Tested up to: 4.9.1
Requires PHP: 5.6
Stable tag: trunk
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

WooCommerce shipping rates calculator for Indonesia shipping couriers JNE, TIKI, POS, PCP, RPX, STAR, SICEPAT, JET, PANDU, J&T, SLIS, EXPEDITO to Domestic and International shipment.

== Description ==
WooCommerce shipping rates calculator for Indonesia shipping couriers JNE, TIKI, POS, PCP, RPX, STAR, SICEPAT, JET, PANDU, J&T, SLIS, EXPEDITO to Domestic and International shipment.

Please note that this plugin is using RajaOngkir.com API as the data source. You must have RajaOngkir.com API Key to use this plugin. [Click here](https://rajaongkir.com) to get RajaOngkir.com API Key. It is free.

= Features =

* Support multiple couriers for domestic shipping: JNE, TIKI, POS, PCP, RPX, STAR, SICEPAT, JET, PANDU, J&T.
* Support multiple couriers for international shipping: JNE, TIKI, POS, SLIS, EXPEDITO.
* Support shipping rates calculation from and to subdistrict location for domestic shipping.
* Support shipping rates calculation based on dimensions and weight.
* Enable or disable any of shipping services provided by each couriers.
* Show or hide estimated time of arrival.
* Real time currency convertion to IDR for international shipping cost courier that use USD.
* Real time API Key validation on settings update.

= My Other Plugins =

* [WooGoSend](https://wordpress.org/plugins/woogosend/) - WooCommerce per kilometer shipping rates calculator for GoSend courier from Go-Jek Indonesia.
* [WooGrabExpress](https://wordpress.org/plugins/woograbexpress/) - WooCommerce per kilometer shipping rates calculator for GrabExpress courier from Grab Indonesia.
* [WooCommerce Shipping Distance Matrix](https://wordpress.org/plugins/wcsdm/) - WooCommerce shipping rates calculator based on products shipping class and distances that calculated using Google Maps Distance Matrix API.

== Installation ==
= Minimum Requirements =

* WordPress 4.8 or later
* WooCommerce 3.0 or later

= AUTOMATIC INSTALLATION =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t even need to leave your web browser. To do an automatic install of Woongkir, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type “Woongkir” and click Search Plugins. You can install it by simply clicking Install Now. After clicking that link you will be asked if you’re sure you want to install the plugin. Click yes and WordPress will automatically complete the installation. After installation has finished, click the ‘activate plugin’ link.

= MANUAL INSTALLATION =

1. Download the plugin zip file to your computer
1. Go to the WordPress admin panel menu Plugins > Add New
1. Choose upload
1. Upload the plugin zip file, the plugin will now be installed
1. After installation has finished, click the ‘activate plugin’ link

== Frequently Asked Questions ==
= How to set the plugin settings? =
You can setup the plugin setting from the Shipping Zones settings. Please check the following video tutorial how to setup the WooCommerce Shipping Zones:

https://www.youtube.com/watch?v=eThWmrnBP38

Credits: [InMotion Hosting](https://www.inmotionhosting.com/)

= Where can I get support? =
You can either create ticket at plugin support forum or GitHub repository:

* [Plugin Support Forum](https://wordpress.org/support/plugin/woongkir)
* [Plugin GitHub Repository](https://github.com/sofyansitorus/Woongkir)

= Where can I report bugs? =
You can either create ticket at plugin support forum or GitHub repository:

* [Plugin Support Forum](https://wordpress.org/support/plugin/woongkir)
* [Plugin GitHub Repository](https://github.com/sofyansitorus/Woongkir)

= Where can I request a feature? =
You can either create ticket at plugin support forum or GitHub repository:

* [Plugin Support Forum](https://wordpress.org/support/plugin/woongkir)
* [Plugin GitHub Repository](https://github.com/sofyansitorus/Woongkir)

== Screenshots ==
1. Settings panel: General Options
2. Settings panel: Domestic Shipping Options
3. Settings panel: International Shipping Options
4. Shipping Calculator Preview: Domestic Shipping
5. Shipping Calculator Preview: International Shipping

== Changelog ==

= 1.1.2 - 2018-01-15 =

* Fix - The couriers is not displayed if the product weight and dimensions is empty.
* Improvemnets - Store local storage data at first load only.

= 1.1.1 - 2018-01-14 =

* Fix - Prevent request to API server if the destination adddress is not complete.
* Improvemnets - Set timeout parameter for remote request: 10 seconds.

= 1.1.0 - 2018-01-13 =

* Feature - Add new domestic shipping couriers: STAR, SICEPAT, JET, PANDU, J&T.
* Feature - Add new international shipping couriers: TIKI, SLIS, EXPEDITO.
* Improvemnts - Tweak settings panel.
* Improvemnts - Tweak estimated time of arrival label.

= 1.0.0 - 2018-01-12 =

* Feature - Support multiple couriers for domestic shipping: JNE, TIKI, POS, PCP, RPX.
* Feature - Support multiple couriers for international shipping: JNE, POS.
* Feature - Support shipping rates calculation from and to subdistrict location for domestic shipping.
* Feature - Support shipping rates calculation based on dimensions and weight.
* Feature - Enable or disable any of shipping services provided by each couriers.
* Feature - Show or hide estimated time of arrival.
* Feature - Real time currency convertion to IDR for international shipping cost courier that use USD.
* Feature - Real time API Key validation on settings update.

== Upgrade Notice ==

= 1.1.2 =
This version add some improvements and fixes several minor bugs. Upgrade immediately.