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
 * Description:       WooCommerce shipping rates calculator using Indonesia shipping using couriers. Available 14 domestic couriers + 4 international shipping couriers: POS Indonesia, TIKI, JNE, RPX, PCP Express, Star Cargo, SiCepat, JET Express, SAP Express, Pahala Express, Solusi Ekspres, J&T Express, Pandu Logistics, Wahana Express, Expedito.
 * Version:           1.2.8
 * Author:            Sofyan Sitorus
 * Author URI:        https://github.com/sofyansitorus
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woongkir
 * Domain Path:       /languages
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 3.5.4
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Defines plugin named constants.
define( 'WOONGKIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOONGKIR_URL', plugin_dir_url( __FILE__ ) );
define( 'WOONGKIR_VERSION', '1.2.3' );
define( 'WOONGKIR_METHOD_ID', 'woongkir' );
define( 'WOONGKIR_METHOD_TITLE', 'Woongkir' );

/**
 * Check if plugin is active
 *
 * @param string $plugin_file Plugin file name.
 */
function woongkir_is_plugin_active( $plugin_file ) {

	$active_plugins = (array) apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, (array) get_site_option( 'active_sitewide_plugins', array() ) );
	}

	return in_array( $plugin_file, $active_plugins, true ) || array_key_exists( $plugin_file, $active_plugins );
}

/**
 * Check if WooCommerce plugin is active
 */
if ( ! woongkir_is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	return;
}

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
	foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
		if ( empty( $zone['shipping_methods'] ) || empty( $zone['zone_id'] ) ) {
			continue;
		}
		foreach ( $zone['shipping_methods'] as $zone_shipping_method ) {
			if ( $zone_shipping_method instanceof Woongkir ) {
				$zone_id = $zone['zone_id'];
				break;
			}
		}
		if ( $zone_id ) {
			break;
		}
	}
	$links = array_merge(
		array(
			'<a href="' . esc_url(
				add_query_arg(
					array(
						'page'              => 'wc-settings',
						'tab'               => 'shipping',
						'zone_id'           => $zone_id,
						'woongkir_settings' => true,
					),
					admin_url( 'admin.php' )
				)
			) . '">' . __( 'Settings', 'woongkir' ) . '</a>',
		),
		$links
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
 * @since 1.1.5
 * @param string $handle  The registered script handle you are attaching the data for.
 * @param string $name  The name of the variable which will contain the data.
 * @param array  $data  The script data itself.
 */
function woongkir_localize_script( $handle, $name, $data = array() ) {
	wp_localize_script(
		$handle,
		$name,
		wp_parse_args(
			$data,
			array(
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
					'placeholder' => array(
						'state'     => __( 'Province', 'woongkir' ),
						'city'      => __( 'Town / City', 'woongkir' ),
						'address_2' => __( 'Subdistrict', 'woongkir' ),
					),
					'label'       => array(
						'state'     => __( 'Province', 'woongkir' ),
						'city'      => __( 'Town / City', 'woongkir' ),
						'address_2' => __( 'Subdistrict', 'woongkir' ),
					),
				),
				'debug'         => ( 'yes' === get_option( 'woocommerce_shipping_debug_mode', 'no' ) ),
				'show_settings' => isset( $_GET['woongkir_settings'] ) && is_admin(),
				'method_id'     => WOONGKIR_METHOD_ID,
				'method_title'  => WOONGKIR_METHOD_TITLE,
			)
		)
	);
}

/**
 * Enqueue backend scripts.
 *
 * @since 1.0.0
 * @param string $hook Passed screen ID in admin area.
 */
function woongkir_enqueue_backend_scripts( $hook = null ) {
	if ( ( is_admin() && 'woocommerce_page_wc-settings' === $hook ) ) {
		$is_debug = defined( 'WOONGKIR_DEV' ) && WOONGKIR_DEV;

		// Define the styles URL.
		$css_url = WOONGKIR_URL . 'assets/css/woongkir-backend.min.css';
		if ( $is_debug ) {
			$css_url = add_query_arg( array( 't' => time() ), str_replace( '.min', '', $css_url ) );
		}

		// Enqueue admin styles.
		wp_enqueue_style(
			'woongkir-backend', // Give the script a unique ID.
			$css_url, // Define the path to the JS file.
			array(), // Define dependencies.
			WOONGKIR_VERSION, // Define a version (optional).
			false // Specify whether to put in footer (leave this false).
		);

		// Register lockr.js scripts.
		$lockr_url = WOONGKIR_URL . 'assets/js/lockr.min.js';
		if ( $is_debug ) {
			$lockr_url = add_query_arg( array( 't' => time() ), str_replace( '.min', '', $lockr_url ) );
		}

		wp_register_script(
			'lockr.js', // Give the script a unique ID.
			$lockr_url, // Define the path to the JS file.
			array( 'jquery' ), // Define dependencies.
			WOONGKIR_VERSION, // Define a version (optional).
			true // Specify whether to put in footer (leave this true).
		);

		// Define the scripts URL.
		$js_url = WOONGKIR_URL . 'assets/js/woongkir-backend.min.js';
		if ( $is_debug ) {
			$js_url = add_query_arg( array( 't' => time() ), str_replace( '.min', '', $js_url ) );
		}

		wp_enqueue_script(
			'woongkir-backend', // Give the script a unique ID.
			$js_url, // Define the path to the JS file.
			array( 'jquery', 'wp-util', 'select2', 'selectWoo', 'lockr.js' ), // Define dependencies.
			WOONGKIR_VERSION, // Define a version (optional).
			true // Specify whether to put in footer (leave this true).
		);

		woongkir_localize_script( 'woongkir-backend', 'woongkir_params' );
	}
}
add_action( 'admin_enqueue_scripts', 'woongkir_enqueue_backend_scripts', 999 );

/**
 * Enqueue frontend scripts.
 *
 * @since 1.0.0
 */
function woongkir_enqueue_frontend_scripts() {
	if ( ! is_admin() ) {
		$is_debug = defined( 'WOONGKIR_DEV' ) && WOONGKIR_DEV;

		// Register lockr.js scripts.
		$lockr_url = WOONGKIR_URL . 'assets/js/lockr.min.js';
		if ( $is_debug ) {
			$lockr_url = add_query_arg( array( 't' => time() ), str_replace( '.min', '', $lockr_url ) );
		}

		wp_register_script(
			'lockr.js', // Give the script a unique ID.
			$lockr_url, // Define the path to the JS file.
			array(), // Define dependencies.
			WOONGKIR_VERSION, // Define a version (optional).
			true // Specify whether to put in footer (leave this true).
		);

		// Enqueue main scripts.
		$js_url = WOONGKIR_URL . 'assets/js/woongkir-frontend.min.js';
		if ( $is_debug ) {
			$js_url = add_query_arg( array( 't' => time() ), str_replace( '.min', '', $js_url ) );
		}

		wp_enqueue_script(
			'woongkir-frontend', // Give the script a unique ID.
			$js_url, // Define the path to the JS file.
			array( 'jquery', 'wp-util', 'select2', 'selectWoo', 'lockr.js' ), // Define dependencies.
			WOONGKIR_VERSION, // Define a version (optional).
			true // Specify whether to put in footer (leave this true).
		);

		woongkir_localize_script( 'woongkir-frontend', 'woongkir_params' );
	}
}
add_action( 'wp_enqueue_scripts', 'woongkir_enqueue_frontend_scripts', 999 );

/**
 * Print hidden element for the hidden address 2 field value
 * in shipping calculator form.
 *
 * @since 1.2.4
 * @return void
 */
function woongkir_after_shipping_calculator() {
	// Address 2 hidden field.
	if ( apply_filters( 'woocommerce_shipping_calculator_enable_address_2', true ) ) {
		$address_2 = WC()->cart->get_customer()->get_shipping_address_2();
		?>
		<input type="hidden" id="calc_shipping_address_2_dummy" value="<?php echo esc_attr( $address_2 ); ?>" />
		<?php
	}
}
add_action( 'woocommerce_after_shipping_calculator', 'woongkir_after_shipping_calculator' );
