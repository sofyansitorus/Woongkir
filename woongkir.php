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
 * Description:       WooCommerce per kilometer shipping rates calculator for GoSend courier from Go-Jek Indonesia.
 * Version:           1.0.1
 * Author:            Sofyan Sitorus
 * Author URI:        https://github.com/sofyansitorus
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woongkir
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Defines plugin named constants.
define( 'WONGKIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'WONGKIR_URL', plugin_dir_url( __FILE__ ) );

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
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	/**
	 * Wrap and load main class to ensure the classes need to extend is exist.
	 *
	 * @since 1.0.0
	 */
	function woongkir_load_dependencies() {
		require_once WONGKIR_PATH . 'includes/class-raja-ongkir.php';
		require_once WONGKIR_PATH . 'includes/class-woongkir.php';
	}
	add_action( 'woocommerce_shipping_init', 'woongkir_load_dependencies' );

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
		if ( ( is_admin() && 'woocommerce_page_wc-settings' === $hook ) || is_woocommerce() || is_cart() || is_checkout() ) {
			wp_register_script( 'store.js', WONGKIR_URL . 'assets/js/store.min.js' );
			wp_enqueue_script( 'woongkir', WONGKIR_URL . 'assets/js/woongkir.min.js', array( 'jquery', 'store.js' ) );
			wp_localize_script(
				'woongkir', 'woongkir_params', array(
					'ajax_url' => admin_url( 'ajax.php' ),
					'json'     => array(
						'country_url'     => WONGKIR_URL . 'data/country.json',
						'country_key'     => 'woongkir_country_data',
						'province_url'    => WONGKIR_URL . 'data/province.json',
						'province_key'    => 'woongkir_province_data',
						'city_url'        => WONGKIR_URL . 'data/city.json',
						'city_key'        => 'woongkir_city_data',
						'subdistrict_url' => WONGKIR_URL . 'data/subdistrict.json',
						'subdistrict_key' => 'woongkir_subdistrict_data',
					),
					'text'     => array(
						'select_country'     => __( 'Select country', 'woongkir' ),
						'select_province'    => __( 'Select province', 'woongkir' ),
						'select_city'        => __( 'Select city', 'woongkir' ),
						'select_subdistrict' => __( 'Select subdistrict', 'woongkir' ),
					),
				)
			);
		}
	}
	add_action( 'admin_enqueue_scripts', 'woongkir_enqueue_scripts' );
	add_action( 'wp_enqueue_scripts', 'woongkir_enqueue_scripts' );
}


