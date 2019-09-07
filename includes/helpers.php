<?php
/**
 * Helper methods.
 *
 * @package Woongkir
 */

if ( ! function_exists( 'woongkir_is_plugin_active' ) ) :
	/**
	 * Check if plugin is active
	 * 
	 * @since 1.0.0
	 *
	 * @param string $plugin_file Plugin file name.
	 */
	function woongkir_is_plugin_active( $plugin_file ) {
		$active_plugins = (array) apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, (array) get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( $plugin_file, $active_plugins, true ) || array_key_exists( $plugin_file, $active_plugins );
	}
endif;

if ( ! function_exists( 'woongkir_get_json_data' ) ) :
	/**
	 * Get json file data.
	 *
	 * @since 1.0.0
	 * @param array $file_name File name for the json data.
	 * @param array $search Serach keyword data.
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
	 * @since ??
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
				'show_settings' => isset( $_GET['woongkir_settings'] ) && is_admin(), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'method_id'         => WOONGKIR_METHOD_ID,
			'method_title'      => WOONGKIR_METHOD_TITLE,
			)
		);
	}
endif;

if ( ! function_exists( 'woongkir_is_dev' ) ) :
	/**
	 * Check is in development envirntment.
	 * 
	 * @since ??
	 *
	 * @return bool
	 */
	function woongkir_is_dev() {
		return defined( 'WOONGKIR_DEV' ) && WOONGKIR_DEV;
	}
endif;
