=== Woongkir ===
Contributors: sofyansitorus
Tags: JNE, POS Indonesia, TIKI, PCP Express, RPX, Pandu Logistics, Wahana Express, SiCepat Express, J&T Express, Pahala Express, SAP Express, JET Express, Solusi Ekspres, 21 Express, Nusantara Card Semesta, Star Cargo, Lion Parcel, Ninja Xpress, Indotama Domestik Lestari, Royal Express Indonesia, Indah Logistic, Expedito
Requires at least: 4.8
Tested up to: 5.2.2
Requires PHP: 5.6
Stable tag: 1.2.12
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

WooCommerce shipping rates calculator for Indonesia domestic and international shipment: JNE, POS Indonesia, TIKI, PCP Express, RPX, Pandu Logistics, Wahana Express, SiCepat Express, J&T Express, Pahala Express, SAP Express, JET Express, Solusi Ekspres, 21 Express, Nusantara Card Semesta, Star Cargo, Lion Parcel, Ninja Xpress, Indotama Domestik Lestari, Royal Express Indonesia, Indah Logistic, Expedito.

== Description ==

WooCommerce shipping rates calculator for Indonesia domestic and international shipment: JNE, POS Indonesia, TIKI, PCP Express, RPX, Pandu Logistics, Wahana Express, SiCepat Express, J&T Express, Pahala Express, SAP Express, JET Express, Solusi Ekspres, 21 Express, Nusantara Card Semesta, Star Cargo, Lion Parcel, Ninja Xpress, Indotama Domestik Lestari, Royal Express Indonesia, Indah Logistic, Expedito.

Please note that this plugin is using RajaOngkir.com API as the data source. You must have RajaOngkir.com API Key to use this plugin. [Click here](https://rajaongkir.com?utm_source=woongkir.com) to get RajaOngkir.com API Key. It is free.

= Features =

* Support domestic shipping couriers: POS Indonesia, TIKI, JNE, RPX, PCP Express, Star Cargo, SiCepat, JET Express, SAP Express, Pahala Express, Solusi Ekspres, J&T Express, Pandu Logistics, Wahana Express.
* Support international shipping couriers: POS Indonesia, JNE, TIKI, Solusi Ekspres, Expedito.
* Support multiple couriers.
* Support shipping rates calculation from and to subdistrict location for domestic shipping.
* Support shipping rates calculation based on dimensions and weight.
* Enable or disable any of shipping services provided by each couriers.
* Set shipping couriers priority.
* Set base weight for cart content.
* Show or hide estimated time of arrival.
* Real time currency convertion to IDR for international shipping cost courier that use USD.
* Real time API Key validation on settings update.

= Another WooCommerce Shipping Plugins =

* [WooGoSend](https://wordpress.org/plugins/woogosend/) - WooCommerce per kilometer shipping rates calculator for GoSend Go-Jek Indonesia courier.
* [WooGrabExpress](https://wordpress.org/plugins/woograbexpress/) - WooCommerce per kilometer shipping rates calculator for GrabExpress Grab Indonesia courier.
* [WooCommerce Shipping Distance Matrix](https://wordpress.org/plugins/wcsdm/) - WooCommerce shipping rates calculator based on products shipping class and route distances that calculated using Google Maps Distance Matrix API.

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
You can setup the plugin setting from the WooCommerce Shipping Zones settings panel. Please [click here](https://fast.wistia.net/embed/iframe/95yiocro6p) for the video tutorial how to setup the WooCommerce Shipping Zones.

= I see message "There are no shipping methods available" in the cart/checkout page, what should I do? =
Please try to switch the WooCommerce Shipping Debug Mode setting to "On". Then open your cart/checkout page. You will see the error printed if there was.

[Click here](https://fast.wistia.net/embed/iframe/9c9008dxnr) for how to switch WooCommerce Shipping Debug Mode.

= Where can I get support report bug? =
You can create support ticket at plugin support forum :

* [Plugin Support Forum](https://wordpress.org/support/plugin/woongkir)

= Can I contribute to develop this plugin? =
I always welcome and encourage contributions to this plugin. Please visit the plugin GitHub repository:

* [Plugin GitHub Repository](https://github.com/sofyansitorus/Woongkir)

== Screenshots ==
1. Settings panel: General Options
2. Settings panel: Domestic Shipping Options
3. Settings panel: International Shipping Options
4. Shipping Calculator Preview: Domestic Shipping
5. Shipping Calculator Preview: International Shipping

== Changelog ==

= 1.2.12 =

* Improved UI/UX in admin area for mobile mobile devices.
* Refactored code base to make it modular for easy to extend.
* Introduced new filters: woongkir_api_request_post_pre, woongkir_api_request_get_pre, woongkir_shipping_results, woongkir_shipping_origin_info, woongkir_shipping_destination_info, woongkir_shipping_dimension_weight

= 1.2.11 =

* Fixed bug to calculate volumetric.
* Added new couriers: 21 Express, Nusantara Card Semesta, Lion Parcel, Ninja Xpress, Indotama Domestik Lestari, Royal Express Indonesia, Indah Logistic.
* Updated existing couriers.

= 1.2.10 =

* Fixed fail to parse the API response in some cases.

= 1.2.9 =

* Improvement - Enabled Volumetric Converter
* Improvement - Added new hooks: woocommerce_woongkir_shipping_origin_info, woocommerce_woongkir_shipping_destination_info
* Fix - Fixed bug when calculating cost 1 courier only in starter account

= 1.2.7 =

* Fix - Fixed duplicate post code field in checkout form when using custom form structure
* Fix - Fixed timeout issue when many couriers selected
* Fix - Removed Cahaya Logistic Domestic Courier (Not supported by RajaOngkir anymore)
* Fix - Removed TIKI International Courier (Not supported by RajaOngkir temporarily)
* Improvement - Enabled to sort couriers list
* Improvement - Enabled logger for API response error

= 1.2.6 =

* Fix - Fixed corrupted file during the build process

= 1.2.5 =

* Fix - Fixed checkout form not updated on fields change
* Improvements - Improved backend form

= 1.2.4 =

* Fix - Fixed City and subdistrict dropdown field now appear in my account address fields
* Fix - Fixed Fixed Subdistrict selected value always empty in shipping calculator form
* Improvements - Improved backend form

= 1.2.3 =

* Bug Fix - Compatibilty issue with WooCommerce 3.5

= 1.2.2 =

* Bug Fix - Empty JS File.

= 1.2.1 =

* Improvements - Added 5 new domestic couriers: Solusi Ekspres, Wahana Prestasi Logistik, Cahaya Ekspress Logistik, Pahala Kencana Express, SAP Express Courier

= 1.2 =

* Feature - Enabled subdistrict field in shipping rate calcultor form.
* Fix - Plugin is not detected in WordPress multisite.

= 1.1.4 =

* Improvements - Add new setting field for base weight.
* Improvements - Add logo for couriers in setting panel.
* Fix - Bug in get detination info.
* Fix - Bug in get weight info.

= 1.1.3 =

* Improvements - Add "Settings" link on the plugins.php page.

= 1.1.2 =

* Fix - The couriers is not displayed if the product weight and dimensions is empty.
* Improvements - Store local storage data at first load only.

= 1.1.1 =

* Fix - Prevent request to API server if the destination adddress is not complete.
* Improvements - Set timeout parameter for remote request: 10 seconds.

= 1.1.0 =

* Feature - Add new domestic shipping couriers: STAR, SICEPAT, JET, PANDU, J&T.
* Feature - Add new international shipping couriers: TIKI, SLIS, EXPEDITO.
* Improvemnts - Tweak settings panel.
* Improvemnts - Tweak estimated time of arrival label.

= 1.0.0 =

* Feature - Support multiple couriers for domestic shipping: JNE, TIKI, POS, PCP, RPX.
* Feature - Support multiple couriers for international shipping: JNE, POS.
* Feature - Support shipping rates calculation from and to subdistrict location for domestic shipping.
* Feature - Support shipping rates calculation based on dimensions and weight.
* Feature - Enable or disable any of shipping services provided by each couriers.
* Feature - Show or hide estimated time of arrival.
* Feature - Real time currency convertion to IDR for international shipping cost courier that use USD.
* Feature - Real time API Key validation on settings update.

== Upgrade Notice ==

= 1.2.7 =
This version include important bug fixes. Upgrade immediately is always recommended.