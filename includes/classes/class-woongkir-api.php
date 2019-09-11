<?php
/**
 * The file that defines the Woongkir_API class
 *
 * @link       https://github.com/sofyansitorus
 * @since      1.0.0
 *
 * @package    Woongkir
 * @subpackage Woongkir/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Woongkir_API API class.
 *
 * This is used to make request to RajaOngkir.com API server.
 *
 * @since      1.0.0
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_API {

	/**
	 * Class options.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $options = array();

	/**
	 * List of account type and allowed features.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $accounts = array();

	/**
	 * List of used delivery couriers and services.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $couriers = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param array $options Class options.
	 */
	public function __construct( $options = array() ) {
		if ( $options && is_array( $options ) ) {
			foreach ( $options as $key => $value ) {
				$this->set_option( $key, $value );
			}
		}

		$this->populate_accounts();
		$this->populate_couriers();
	}

	/**
	 * Populate accounts list
	 *
	 * @since ??
	 *
	 * @return void
	 */
	private function populate_accounts() {
		$files = glob( WOONGKIR_PATH . 'includes/accounts/class-woongkir-account-*.php' );

		foreach ( $files as $file ) {
			$class_name = str_replace( array( 'class-', '-' ), array( '', '_' ), basename( $file, '.php' ) );

			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			$account = new $class_name();

			$this->accounts[ $account->get_type() ] = $account;
		}

		if ( $this->accounts ) {
			uasort( $this->accounts, array( $this, 'sort_by_priority' ) );
		}
	}

	/**
	 * Populate couriers list
	 *
	 * @since ??
	 *
	 * @return void
	 */
	private function populate_couriers() {
		$files = glob( WOONGKIR_PATH . 'includes/couriers/class-woongkir-courier-*.php' );

		foreach ( $files as $file ) {
			$class_name = str_replace( array( 'class-', '-' ), array( '', '_' ), basename( $file, '.php' ) );

			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			$courier = new $class_name();

			$services_domestic = $courier->get_services_domestic();

			if ( $services_domestic ) {
				if ( ! isset( $this->couriers['domestic'] ) ) {
					$this->couriers['domestic'] = array();
				}

				$this->couriers['domestic'][ $courier->get_id() ] = array(
					'label'    => $courier->get_name(),
					'website'  => $courier->get_website(),
					'account'  => $courier->get_account_domestic(),
					'services' => $services_domestic,
				);
			}

			$services_international = $courier->get_services_international();

			if ( $services_international ) {
				if ( ! isset( $this->couriers['international'] ) ) {
					$this->couriers['international'] = array();
				}

				$this->couriers['international'][ $courier->get_id() ] = array(
					'response_id' => $courier->get_response_id(),
					'label'       => $courier->get_name(),
					'website'     => $courier->get_website(),
					'account'     => $courier->get_account_international(),
					'services'    => $services_international,
				);
			}
		}
	}

	private function sort_by_priority( $a, $b ) {
		$a_priority = 0;

		if ( is_callable( array( $a, 'get_priority' ) ) ) {
			$a_priority = $a->get_priority();
		} elseif ( isset( $a['priority'] ) ) {
			$a_priority = $a['priority'];
		}

		$b_priority = 0;

		if ( is_callable( array( $b, 'get_priority' ) ) ) {
			$b_priority = $b->get_priority();
		} elseif ( isset( $b['priority'] ) ) {
			$b_priority = $b['priority'];
		}

		return strcasecmp( $a_priority, $b_priority );
	}

	/**
	 * Get shipping cost.
	 *
	 * @since 1.0.0
	 * @param array $destination Shipping destination data.
	 * @param array $origin Shipping origin data.
	 * @param array $dimension_weight Shipping package weight and dimension data.
	 * @param array $courier Request Shipping couriers data.
	 * @return array
	 */
	public function get_cost( $destination, $origin, $dimension_weight, $courier ) {
		$results  = array();
		$account  = $this->get_account( $this->get_option( 'account_type' ) );
		$endpoint = empty( $destination['country'] ) ? 'cost' : 'internationalCost';

		if ( $courier && ! $account['multiple_couriers'] ) {
			$courier = array_slice( $courier, 0, 1 );
		}

		$courier_chunks = $courier ? array_chunk( $courier, apply_filters( 'woongkir_api_courier_chunks', 3 ) ) : false;

		// Bail early when the couriers data is empty.
		if ( ! $courier_chunks ) {
			return $results;
		}

		foreach ( $courier_chunks as $couriers ) {
			switch ( $endpoint ) {
				case 'internationalCost':
					$params = array(
						'destination' => $destination['country'],
						'origin'      => $origin['city'],
						'courier'     => implode( ':', $couriers ),
					);
					break;

				default:
					$params = array(
						'destination'     => ( $account['subdistrict'] && ! empty( $destination['subdistrict'] ) ) ? $destination['subdistrict'] : $destination['city'],
						'destinationType' => ( $account['subdistrict'] && ! empty( $destination['subdistrict'] ) ) ? 'subdistrict' : 'city',
						'origin'          => ( $account['subdistrict'] && ! empty( $origin['subdistrict'] ) ) ? $origin['subdistrict'] : $origin['city'],
						'originType'      => ( $account['subdistrict'] && ! empty( $origin['subdistrict'] ) ) ? 'subdistrict' : 'city',
						'courier'         => implode( ':', $couriers ),
					);
					break;
			}

			$results[] = $this->remote_post( $endpoint, array_merge( $params, $dimension_weight ) );
		}

		return $results;
	}

	/**
	 * Get currency exchange value.
	 *
	 * @since 1.0.0
	 */
	public function get_currency() {
		return $this->remote_get( 'currency' );
	}

	/**
	 * Get courier data.
	 *
	 * @since 1.0.0
	 * @param string $zone_id Courier key.
	 */
	public function get_courier( $zone_id = null ) {
		if ( ! is_null( $zone_id ) ) {
			return isset( $this->couriers[ $zone_id ] ) ? $this->couriers[ $zone_id ] : false;
		}

		return $this->couriers;
	}

	/**
	 * Set class option.
	 *
	 * @since 1.0.0
	 * @param string $key Option key.
	 * @param mixed  $value Option value.
	 */
	public function set_option( $key, $value ) {
		$this->options[ $key ] = $value;
	}

	/**
	 * Get class option.
	 *
	 * @since 1.0.0
	 * @param string $key Option key.
	 * @param string $default Option default value.
	 */
	public function get_option( $key, $default = null ) {
		return isset( $this->options[ $key ] ) ? $this->options[ $key ] : $default;
	}

	/**
	 * Make request to API server.
	 *
	 * @since 1.0.0
	 * @param string $endpoint API request URL endpoint.
	 * @param array  $params API request parameters.
	 */
	public function remote_request( $endpoint, $params = array() ) {
		$args = wp_parse_args(
			$params,
			array(
				'timeout' => 10,
				'headers' => array(
					'key' => $this->get_option( 'api_key' ),
				),
			)
		);

		/**
		 * Developers can modify the api request via filter hooks.
		 *
		 * @since 1.2.7
		 *
		 * This example shows how you can modify the $response var via custom function:
		 *
		 *      add_filter( 'woongkir_api_remote_request_pre', 'my_api_remote_request_pre', 10, 4 );
		 *
		 *      function my_api_remote_request_pre( $false, $endpoint, $args, $param, $obj ) {
		 *          // Return the response data JSON
		 *          return wp_json_encode( array() );
		 *      }
		 */
		$response = apply_filters( 'woongkir_api_remote_request_pre', false, $endpoint, $args, $params, $this );

		if ( false === $response ) {
			$response = wp_remote_request( $this->api_url( $endpoint ), $args );
		}

		return $this->validate_api_response( $response );
	}

	/**
	 * Make request to API server using the POST method.
	 *
	 * @since 1.0.0
	 * @param string $endpoint API request URL endpoint.
	 * @param array  $body API request body parameters.
	 */
	public function remote_post( $endpoint, $body = array() ) {
		$args = array(
			'timeout' => 10,
			'headers' => array(
				'key'          => $this->get_option( 'api_key' ),
				'content-type' => 'application/x-www-form-urlencoded',
			),
			'body'    => $body,
		);

		/**
		 * Developers can modify the api request via filter hooks.
		 *
		 * @since 1.2.7
		 *
		 * This example shows how you can modify the $response var via custom function:
		 *
		 *      add_filter( 'woongkir_api_remote_post_pre', 'my_api_remote_post_pre', 10, 4 );
		 *
		 *      function my_api_remote_post_pre( $false, $endpoint, $args, $body, $obj ) {
		 *          // Return the response data JSON
		 *          return wp_json_encode( array() );
		 *      }
		 */
		$response = apply_filters( 'woongkir_api_remote_post_pre', false, $endpoint, $args, $body, $this );

		if ( false === $response ) {
			$response = wp_remote_post( $this->api_url( $endpoint ), $args );
		}

		return $this->validate_api_response( $response );
	}

	/**
	 * Make request to API server using the GET method.
	 *
	 * @since 1.0.0
	 * @param string $endpoint API request URL endpoint.
	 * @param array  $query_url API request URL query string parameters.
	 */
	public function remote_get( $endpoint, $query_url = array() ) {
		$args = array(
			'timeout' => 10,
			'headers' => array(
				'key' => $this->get_option( 'api_key' ),
			),
		);

		/**
		 * Developers can modify the api request via filter hooks.
		 *
		 * @since 1.2.7
		 *
		 * This example shows how you can modify the $response var via custom function:
		 *
		 *      add_filter( 'woongkir_api_remote_get_pre', 'my_api_remote_get_pre', 10, 4 );
		 *
		 *      function my_api_remote_get_pre( $false, $endpoint, $args, $query_url, $obj ) {
		 *          // Return the response data JSON
		 *          return wp_json_encode( array() );
		 *      }
		 */
		$response = apply_filters( 'woongkir_api_remote_get_pre', false, $endpoint, $args, $query_url, $this );

		if ( false === $response ) {
			$url = $this->api_url( $endpoint );

			if ( $query_url ) {
				$url = add_query_arg( $query_url, $url );
			}

			$response = wp_remote_get( $url, $args );
		}

		return $this->validate_api_response( $response );
	}

	/**
	 * Validate API request response.
	 *
	 * @since 1.0.0
	 * @param mixed $response API request response data.
	 * @throws Exception Error exception when response data is invalid.
	 * @return mixed WP_Error object on failure.
	 */
	private function validate_api_response( $response ) {
		try {
			if ( is_wp_error( $response ) ) {
				throw new Exception( $response->get_error_message() );
			}

			$body = wp_remote_retrieve_body( $response );

			if ( empty( $body ) ) {
				throw new Exception( __( 'API response is empty.', 'woongkir' ) );
			}

			// Try to capture the data for response that has incorrect JSON format.
			if ( ! preg_match( '/^\{(.*)\}$/s', $body ) && preg_match( '/{"rajaongkir"(.*?)}}/m', $body, $matches, PREG_OFFSET_CAPTURE, 0 ) ) {
				$body = isset( $matches[0][0] ) && ! empty( $matches[0][0] ) ? $matches[0][0] : $body;
			}

			$data       = json_decode( $body );
			$json_error = json_last_error_msg();

			if ( $json_error && strtolower( $json_error ) !== 'no error' ) {
				// translators: %1$s - JSON error message, %2$s API response body.
				throw new Exception( wp_sprintf( __( 'Failed to decode the JSON data: Error: %1$s, Body: %2$s', 'woongkir' ), $json_error, $body ) );
			}

			if ( $data && isset( $data->rajaongkir->status ) && 200 !== intval( $data->rajaongkir->status->code ) ) {
				$error_code        = $data->rajaongkir->status->code;
				$error_description = isset( $data->rajaongkir->status->description ) ? $data->rajaongkir->status->description : '';
				// translators: %1$s - API error code, %2$s API error description.
				throw new Exception( wp_sprintf( __( 'Error Code: %1$s, Error Description: %2$s', 'woongkir' ), $error_code, $error_description ) );
			}

			if ( $data && isset( $data->rajaongkir->results ) && is_array( $data->rajaongkir->results ) ) {
				return $data->rajaongkir->results;
			}

			if ( $data && isset( $data->rajaongkir->result ) && is_array( $data->rajaongkir->result ) ) {
				return $data->rajaongkir->result;
			}

			// translators: %1$s - API response body.
			throw new Exception( wp_sprintf( __( 'API response is invalid:  %1$s', 'woongkir' ), $body ), 1 );
		} catch ( Exception $e ) {
			$wc_log = wc_get_logger();
			$wc_log->log( 'error', wp_strip_all_tags( $e->getMessage(), true ), array( 'source' => 'woongkir_api_error' ) );

			// translators: %s - Error message from RajaOngkir.com.
			return new WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from RajaOngkir.com</strong>: %s', 'woongkir' ), $e->getMessage() ) );
		}
	}

	/**
	 * Validate API account.
	 *
	 * @since 1.0.0
	 */
	public function validate_account() {
		$account_type = $this->get_option( 'account_type' );

		// Destination test data.
		$destination = array(
			'country'     => 0,
			'province'    => 0,
			'city'        => 'pro' !== $account_type ? 114 : 0,
			'subdistrict' => 'pro' === $account_type ? 574 : 0,
		);

		// Origin test data.
		$origin = array(
			'province'    => 0,
			'city'        => 'pro' !== $account_type ? 501 : 0,
			'subdistrict' => 'pro' === $account_type ? 538 : 0,
		);

		// Dimension & weight test data.
		$dimension_weight = array(
			'width'  => 0,
			'length' => 0,
			'height' => 0,
			'weight' => 1700,
		);

		// Courier test data.
		$courier = array( 'jne' );

		return $this->get_cost( $destination, $origin, $dimension_weight, $courier );
	}

	/**
	 * Get API request URL.
	 *
	 * @since 1.0.0
	 * @param string $endpoint API URL endpoint.
	 * @return string
	 */
	private function api_url( $endpoint ) {
		$account = $this->get_account( $this->get_option( 'account_type' ) );
		switch ( $endpoint ) {
			case 'internationalOrigin':
			case 'internationalDestination':
			case 'internationalCost':
				$url = $account['api_url'] . '/v2/' . $endpoint;
				break;

			default:
				$url = $account['api_url'] . '/' . $endpoint;
				break;
		}

		return $url;
	}

	/**
	 * Get couriers names
	 *
	 * @return array
	 */
	public function get_couriers_names() {
		$names = array();

		foreach ( $this->couriers as $couriers ) {
			foreach ( $couriers as $code => $courier ) {
				$names[ $code ] = $courier['label'];
			}
		}

		return $names;
	}

	/**
	 * Get accounts data.
	 *
	 * @return array
	 */
	public function get_accounts( $as_array = true ) {
		if ( ! $as_array ) {
			return $this->accounts;
		}

		$accounts = array();

		foreach ( $this->accounts as $type => $account ) {
			$accounts[ $type ] = $account->to_array();
		}

		return $accounts;
	}

	/**
	 * Get account data.
	 *
	 * @since 1.0.0
	 * @param string $type Acoount type key.
	 */
	public function get_account( $type, $as_array = true ) {
		$accounts = $this->get_accounts( $as_array );

		if ( isset( $accounts[ $type ] ) ) {
			return $accounts[ $type ];
		}

		return false;
	}
}
