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
 * Description:       WooCommerce shipping rates calculator using Indonesia shipping couriers JNE, TIKI, POS, PCP, RPX, STAR, SICEPAT, JET, PANDU, J&T, SLIS, EXPEDITO for Domestic and International shipment.
 * Version:           1.1.3
 * Author:            Sofyan Sitorus
 * Author URI:        https://github.com/sofyansitorus
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woongkir
 * Domain Path:       /languages
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 3.2.6
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Defines plugin named constants.
define( 'WOONGKIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOONGKIR_URL', plugin_dir_url( __FILE__ ) );
define( 'WOONGKIR_VERSION', '1.1.3' );

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function woongkir_load_textdomain() {
	load_plugin_textdomain( 'woongkir', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'woongkir_load_textdomain' );

/**
 * Check if WooCommerce is active
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	return;
}// End if().

/**
 * Wrap and load main class to ensure the classes need to extend is exist.
 *
 * @since 1.0.0
 */
function woongkir_load_dependencies() {
	require_once WOONGKIR_PATH . 'includes/class-raja-ongkir.php';
	require_once WOONGKIR_PATH . 'includes/class-woongkir.php';
}
add_action( 'woocommerce_shipping_init', 'woongkir_load_dependencies' );

/**
 * Add plugin action links.
 *
 * Add a link to the settings page on the plugins.php page.
 *
 * @since 1.1.3
 *
 * @param  array $links List of existing plugin action links.
 * @return array         List of modified plugin action links.
 */
function woongkir_plugin_action_links( $links ) {
	$zone_id = 0;
	$zones   = WC_Shipping_Zones::get_zones();
	foreach ( $zones as $zone ) {
		if ( empty( $zone['shipping_methods'] ) || empty( $zone['zone_id'] ) ) {
			continue;
		}
		foreach ( $zone['shipping_methods'] as $zone_shipping_method ) {
			if ( $zone_shipping_method instanceof Woongkir ) {
				$zone_id = $zone['zone_id'];
				break;
			}
		}
	}

	$links = array_merge(
		array(
			'<a href="' . esc_url( wp_nonce_url( admin_url( 'admin.php?page=wc-settings&tab=shipping&zone_id=' . $zone_id ), 'woongkir_settings', 'woongkir_nonce' ) ) . '">' . __( 'Settings', 'woongkir' ) . '</a>',
		), $links
	);

	return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woongkir_plugin_action_links' );


/**
 * Register shipping method to WooCommerce.
 *
 * @since 1.0.0
 * @param array $methods registered shipping methods.
 */
function woongkir_shipping_methods( $methods ) {
	if ( class_exists( 'Woongkir' ) ) {
		$methods['woongkir'] = 'Woongkir';
	}
	return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'woongkir_shipping_methods' );

/**
 * Enqueue scripts.
 *
 * @since 1.0.0
 * @param string $hook Passed screen ID in admin area.
 */
function woongkir_enqueue_scripts( $hook = null ) {
	if ( ( is_admin() && 'woocommerce_page_wc-settings' === $hook ) || ! is_admin() ) {
		// Register store.js scripts.
		$store_js = ( defined( 'WOONGKIR_DEV' ) && WOONGKIR_DEV ) ? add_query_arg( array( 't' => time() ), WOONGKIR_URL . 'assets/js/store.js' ) : WOONGKIR_URL . 'assets/js/store.min.js';
		wp_enqueue_script(
			'store.js', // Give the script a unique ID.
			$store_js, // Define the path to the JS file.
			array( 'jquery' ), // Define dependencies.
			WOONGKIR_VERSION, // Define a version (optional).
			true // Specify whether to put in footer (leave this true).
		);

		// Enqueue main scripts.
		$woongkir_js = ( defined( 'WOONGKIR_DEV' ) && WOONGKIR_DEV ) ? add_query_arg( array( 't' => time() ), WOONGKIR_URL . 'assets/js/woongkir.js' ) : WOONGKIR_URL . 'assets/js/woongkir.min.js';
		wp_enqueue_script(
			'woongkir', // Give the script a unique ID.
			$woongkir_js, // Define the path to the JS file.
			array( 'jquery', 'store.js' ), // Define dependencies.
			WOONGKIR_VERSION, // Define a version (optional).
			true // Specify whether to put in footer (leave this true).
		);

		wp_localize_script(
			'woongkir', 'woongkir_params', array(
				'ajax_url'      => admin_url( 'ajax.php' ),
				'json'          => array(
					'country_url'     => add_query_arg( array( 't' => current_time( 'timestamp' ) ), WOONGKIR_URL . 'data/country.json' ),
					'country_key'     => 'woongkir_country_data',
					'province_url'    => add_query_arg( array( 't' => current_time( 'timestamp' ) ), WOONGKIR_URL . 'data/province.json' ),
					'province_key'    => 'woongkir_province_data',
					'city_url'        => add_query_arg( array( 't' => current_time( 'timestamp' ) ), WOONGKIR_URL . 'data/city.json' ),
					'city_key'        => 'woongkir_city_data',
					'subdistrict_url' => add_query_arg( array( 't' => current_time( 'timestamp' ) ), WOONGKIR_URL . 'data/subdistrict.json' ),
					'subdistrict_key' => 'woongkir_subdistrict_data',
				),
				'text'          => array(
					'select_country'     => __( 'Select country', 'woongkir' ),
					'select_province'    => __( 'Select province', 'woongkir' ),
					'select_city'        => __( 'Select city', 'woongkir' ),
					'select_subdistrict' => __( 'Select subdistrict', 'woongkir' ),
				),
				'debug'         => ( 'yes' === get_option( 'woocommerce_shipping_debug_mode', 'no' ) ),
				'show_settings' => ( isset( $_GET['woongkir_nonce'] ) && wp_verify_nonce( $_GET['woongkir_nonce'], 'woongkir_settings' ) && is_admin() ),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'woongkir_enqueue_scripts', 999 );
add_action( 'wp_enqueue_scripts', 'woongkir_enqueue_scripts', 999 );

// Show city field in the shipping calculator form.
add_filter( 'woocommerce_shipping_calculator_enable_city', '__return_true' );
