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
 * Description:       WooCommerce shipping rates calculator for Indonesia domestic and international shipment: AnterAja, 21 Express, Expedito, IDexpress Service Solution, Indotama Domestik Lestari, Indah Logistic, JET Express, Jalur Nugraha Ekakurir (JNE), J&T Express, JTL Express, Lion Parcel, Ninja Xpress, Pahala Express, Pandu Logistics, PCP, POS Indonesia, Royal Express Indonesia, RPX, SAP Express, Sentral Cargo, SiCepat Express, Solusi Ekspres, Star Cargo, TIKI, Wahana Express.
 * Version:           1.3.12
 * Author:            Sofyan Sitorus
 * Author URI:        https://github.com/sofyansitorus
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woongkir
 * Domain Path:       /languages
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 8.6.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'WOONGKIR_METHOD_ID', 'woongkir' );
define( 'WOONGKIR_FILE', __FILE__ );
define( 'WOONGKIR_PATH', plugin_dir_path( WOONGKIR_FILE ) );
define( 'WOONGKIR_URL', plugin_dir_url( WOONGKIR_FILE ) );

// Load the helpers.
require_once WOONGKIR_PATH . 'includes/helpers.php';

// Register the class auto loader.
if ( function_exists( 'woongkir_autoload' ) ) {
	spl_autoload_register( 'woongkir_autoload' );
}

/**
 * Boot the plugin
 */
if ( woongkir_is_plugin_active( 'woocommerce/woocommerce.php' ) && class_exists( 'Woongkir' ) ) {
	// Initialize the woongkir class.
	Woongkir::get_instance();
}
