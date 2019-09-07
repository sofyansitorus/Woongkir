<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/sofyansitorus
 * @since             1.0.0
 * @package           Woongkir
 *
 * @wordpress-plugin
 * Plugin Name:       Woongkir
 * Plugin URI:        https://github.com/sofyansitorus/Woongkir
 * Description:       WooCommerce shipping rates calculator for Indonesia domestic and international shipment: JNE, POS Indonesia, TIKI, PCP Express, RPX, Pandu Logistics, Wahana Express, SiCepat Express, J&T Express, Pahala Express, SAP Express, JET Express, Solusi Ekspres, 21 Express, Nusantara Card Semesta, Star Cargo, Lion Parcel, Ninja Xpress, Indotama Domestik Lestari, Royal Express Indonesia, Indah Logistic, Expedito.
 * Version:           1.2.11
 * Author:            Sofyan Sitorus
 * Author URI:        https://github.com/sofyansitorus
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woongkir
 * Domain Path:       /languages
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 3.7.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'WOONGKIR_FILE', __FILE__ );
define( 'WOONGKIR_PATH', plugin_dir_path( WOONGKIR_FILE ) );
define( 'WOONGKIR_URL', plugin_dir_url( WOONGKIR_FILE ) );
define( 'WOONGKIR_METHOD_ID', 'woongkir' );
define( 'WOONGKIR_METHOD_TITLE', 'Woongkir' );
define( 'WOONGKIR_VERSION', '1.2.11' );

// Load the dependecies.
require_once WOONGKIR_PATH . 'includes/helpers.php';
require_once WOONGKIR_PATH . 'includes/class-woongkir.php';

/**
 * Boot the plugin
 */
if ( woongkir_is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	Woongkir::get_instance();
}
