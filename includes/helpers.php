<?php
/**
 * Helper methods.
 *
 * @package Woongkir
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'woongkir_is_plugin_active' ) ) :
	/**
	 * Check if plugin is active
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_file Plugin file name.
	 */
	function woongkir_is_plugin_active( $plugin_file ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $plugin_file );
	}
endif;

if ( ! function_exists( 'woongkir_autoload' ) ) :
	/**
	 * Class autoload
	 *
	 * @since 1.2.12
	 *
	 * @param string $class Class name.
	 *
	 * @return void
	 */
	function woongkir_autoload( $class ) {
		$class = strtolower( $class );

		if ( strpos( $class, 'woongkir' ) !== 0 ) {
			return;
		}

		if ( strpos( $class, 'woongkir_account_' ) === 0 ) {
			require_once WOONGKIR_PATH . 'includes/accounts/class-' . str_replace( '_', '-', $class ) . '.php';
		} elseif ( strpos( $class, 'woongkir_courier_' ) === 0 ) {
			require_once WOONGKIR_PATH . 'includes/couriers/class-' . str_replace( '_', '-', $class ) . '.php';
		} else {
			require_once WOONGKIR_PATH . 'includes/classes/class-' . str_replace( '_', '-', $class ) . '.php';
		}
	}
endif;

if ( ! function_exists( 'woongkir_get_json_data' ) ) :
	/**
	 * Get json file data.
	 *
	 * @since 1.0.0
	 * @param array $file_name File name for the json data.
	 * @param array $search Search keyword data.
	 * @throws  Exception If WordPress Filesystem Abstraction classes is not available.
	 * @return array
	 */
	function woongkir_get_json_data( $file_name, $search = array() ) {
		global $wp_filesystem;

		$file_url  = WOONGKIR_URL . 'data/' . $file_name . '.json';
		$file_path = WOONGKIR_PATH . 'data/' . $file_name . '.json';

		try {
			require_once ABSPATH . 'wp-admin/includes/file.php';

			if ( is_null( $wp_filesystem ) ) {
				WP_Filesystem();
			}

			if ( ! $wp_filesystem instanceof WP_Filesystem_Base || ( is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) ) {
				throw new Exception( 'WordPress Filesystem Abstraction classes is not available', 1 );
			}

			if ( ! $wp_filesystem->exists( $file_path ) ) {
				throw new Exception( 'JSON file is not exists or unreadable', 1 );
			}

			$json = $wp_filesystem->get_contents( $file_path );
		} catch ( Exception $e ) {
			// Get JSON data by HTTP if the WP_Filesystem API procedure failed.
			$json = wp_remote_retrieve_body( wp_remote_get( esc_url_raw( $file_url ) ) );
		}

		if ( ! $json ) {
			return false;
		}

		$json_data  = json_decode( $json, true );
		$json_error = json_last_error_msg();

		if ( ! $json_data || ( $json_error && 'no error' !== strtolower( $json_error ) ) ) {
			return false;
		}

		// Search JSON data by associative array. Return the match row or false if not found.
		if ( $search ) {
			foreach ( $json_data as $row ) {
				if ( array_intersect_assoc( $search, $row ) === $search ) {
					return $row;
				}
			}

			return false;
		}

		return $json_data;
	}
endif;

if ( ! function_exists( 'woongkir_scripts_params' ) ) :
	/**
	 * Get localized scripts parameters.
	 *
	 * @since 1.2.11
	 *
	 * @param array $params Custom localized scripts parameters.
	 *
	 * @return array
	 */
	function woongkir_scripts_params( $params = array() ) {
		return wp_parse_args(
			$params,
			array(
				'ajax_url'      => admin_url( 'ajax.php' ),
				'json'          => array(
					'country_url'     => add_query_arg( array( 't' => time() ), WOONGKIR_URL . 'data/country.json' ),
					'country_key'     => 'woongkir_country_data',
					'province_url'    => add_query_arg( array( 't' => time() ), WOONGKIR_URL . 'data/province.json' ),
					'province_key'    => 'woongkir_province_data',
					'city_url'        => add_query_arg( array( 't' => time() ), WOONGKIR_URL . 'data/city.json' ),
					'city_key'        => 'woongkir_city_data',
					'subdistrict_url' => add_query_arg( array( 't' => time() ), WOONGKIR_URL . 'data/subdistrict.json' ),
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
				'show_settings' => isset( $_GET['woongkir_settings'] ) && is_admin(), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'method_id'         => WOONGKIR_METHOD_ID,
			'method_title'      => woongkir_get_plugin_data( 'Name' ),
			)
		);
	}
endif;

if ( ! function_exists( 'woongkir_sort_by_priority' ) ) :
	/**
	 * Sort data by priority
	 *
	 * @param array $a Item to compare.
	 * @param array $b Item to compare.
	 *
	 * @return int
	 */
	function woongkir_sort_by_priority( $a, $b ) {
		$a_priority = 0;

		if ( is_object( $a ) && is_callable( array( $a, 'get_priority' ) ) ) {
			$a_priority = $a->get_priority();
		} elseif ( isset( $a['priority'] ) ) {
			$a_priority = $a['priority'];
		}

		$b_priority = 0;

		if ( is_object( $b ) && is_callable( array( $b, 'get_priority' ) ) ) {
			$b_priority = $b->get_priority();
		} elseif ( isset( $b['priority'] ) ) {
			$b_priority = $b['priority'];
		}

		return strcasecmp( $a_priority, $b_priority );
	}
endif;

if ( ! function_exists( 'woongkir_is_dev' ) ) :
	/**
	 * Check is in development environment.
	 *
	 * @since 1.2.11
	 *
	 * @return bool
	 */
	function woongkir_is_dev() {
		if ( defined( 'WOONGKIR_DEV' ) && WOONGKIR_DEV ) {
			return true;
		}

		if ( function_exists( 'getenv' ) && getenv( 'WOONGKIR_DEV' ) ) {
			return true;
		}

		return false;
	}
endif;

if ( ! function_exists( 'woongkir_get_plugin_data' ) ) :
	/**
	 * Get plugin data
	 *
	 * @since 1.2.13
	 *
	 * @param string $selected Selected data key.
	 * @param string $selected_default Selected data key default value.
	 * @param bool   $markup If the returned data should have HTML markup applied.
	 * @param bool   $translate If the returned data should be translated.
	 *
	 * @return (string|array)
	 */
	function woongkir_get_plugin_data( $selected = null, $selected_default = '', $markup = false, $translate = true ) {
		static $plugin_data;

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_null( $plugin_data ) ) {
			$plugin_data = get_plugin_data( WOONGKIR_FILE, $markup, $translate );
		}

		if ( ! is_null( $selected ) ) {
			return isset( $plugin_data[ $selected ] ) ? $plugin_data[ $selected ] : $selected_default;
		}

		return $plugin_data;
	}
endif;

if ( ! function_exists( 'woongkir_instances' ) ) :
	/**
	 * Get shipping method instances
	 *
	 * @since 1.3.0
	 *
	 * @param bool $enabled_only Filter to includes only enabled instances.
	 * @return array
	 */
	function woongkir_instances( $enabled_only = true ) {
		$instances = array();

		$zone_data_store = new WC_Shipping_Zone_Data_Store();

		$shipping_methods = $zone_data_store->get_methods( '0', $enabled_only );

		if ( $shipping_methods ) {
			foreach ( $shipping_methods as $shipping_method ) {
				if ( WOONGKIR_METHOD_ID !== $shipping_method->method_id ) {
					continue;
				}

				$instances[] = array(
					'zone_id'     => 0,
					'method_id'   => $shipping_method->method_id,
					'instance_id' => $shipping_method->instance_id,
				);
			}
		}

		$zones = WC_Shipping_Zones::get_zones();

		if ( ! empty( $zones ) ) {
			foreach ( $zones as $zone ) {
				$shipping_methods = $zone_data_store->get_methods( $zone['id'], $enabled_only );
				if ( $shipping_methods ) {
					foreach ( $shipping_methods as $shipping_method ) {
						if ( WOONGKIR_METHOD_ID !== $shipping_method->method_id ) {
							continue;
						}

						$instances[] = array(
							'zone_id'     => 0,
							'method_id'   => $shipping_method->method_id,
							'instance_id' => $shipping_method->instance_id,
						);
					}
				}
			}
		}

		return apply_filters( 'woongkir_instances', $instances );
	}
endif;
