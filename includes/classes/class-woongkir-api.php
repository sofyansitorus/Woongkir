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

			$this->couriers[ $courier->get_code() ] = $courier;
		}

		if ( $this->couriers ) {
			uasort( $this->couriers, array( $this, 'sort_by_priority' ) );
		}
	}

	/**
	 * Sort data by priority
	 *
	 * @param array $a Item to compare.
	 * @param array $b Item to compare.
	 *
	 * @return int
	 */
	protected function sort_by_priority( $a, $b ) {
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
		$account  = $this->get_account();
		$endpoint = empty( $destination['country'] ) ? 'cost' : 'internationalCost';

		if ( $courier && ! $account->feature_enable( 'multiple_couriers' ) ) {
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
						'destination'     => ( $account->feature_enable( 'subdistrict' ) && ! empty( $destination['subdistrict'] ) ) ? $destination['subdistrict'] : $destination['city'],
						'destinationType' => ( $account->feature_enable( 'subdistrict' ) && ! empty( $destination['subdistrict'] ) ) ? 'subdistrict' : 'city',
						'origin'          => ( $account->feature_enable( 'subdistrict' ) && ! empty( $origin['subdistrict'] ) ) ? $origin['subdistrict'] : $origin['city'],
						'originType'      => ( $account->feature_enable( 'subdistrict' ) && ! empty( $origin['subdistrict'] ) ) ? 'subdistrict' : 'city',
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

		return $this->api_response_parser( $response );
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

		return $this->api_response_parser( $response );
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

		return $this->api_response_parser( $response );
	}

	/**
	 * Validate API account.
	 *
	 * @since 1.0.0
	 */
	public function validate_account() {
		$account_type = $this->get_option( 'account_type', 'starter' );

		$params = array(
			'courier'     => array( 'jne' ),
			'weight'      => 1700,
			'origin'      => 'pro' === $account_type ? '538' : '501',
			'destination' => 'pro' === $account_type ? '574' : '114',
		);

		if ( 'pro' === $account_type ) {
			$params['originType']      = 'subdistrict';
			$params['destinationType'] = 'subdistrict';
		}

		return $this->calculate_shipping( $params );
	}

	/**
	 * Get API request URL.
	 *
	 * @since 1.0.0
	 * @param string $endpoint API URL endpoint.
	 * @return string
	 */
	private function api_url( $endpoint ) {
		$account = $this->get_account();

		switch ( $endpoint ) {
			case 'internationalOrigin':
			case 'internationalDestination':
			case 'internationalCost':
				$url = $account->get_api_url() . '/v2/' . $endpoint;
				break;

			default:
				$url = $account->get_api_url() . '/' . $endpoint;
				break;
		}

		return $url;
	}

	/**
	 * Get accounts object or data.
	 *
	 * @since ??
	 *
	 * @param bool $as_array Wethere to return data as array or not.
	 *
	 * @return array
	 */
	public function get_accounts( $as_array = false ) {
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
	 * Get account object or data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $account_type Account type key.
	 * @param bool   $as_array Wethere to return data as array or not.
	 *
	 * @return (Woongkir_Account|array|bool) Courier object or array data. False on failure.
	 */
	public function get_account( $account_type = null, $as_array = false ) {
		$accounts = $this->get_accounts( $as_array );

		if ( is_null( $account_type ) ) {
			$account_type = $this->get_option( 'account_type', 'starter' );
		}

		if ( isset( $accounts[ $account_type ] ) ) {
			return $accounts[ $account_type ];
		}

		return false;
	}

	/**
	 * Get couriers object or data.
	 *
	 * @since ??
	 *
	 * @param string  $zone Couriers zone: domestic, international, all.
	 * @param string  $account_type Filters couriers allowed for specific account type: starter, basic, prop, all.
	 * @param boolean $as_array Wethere to return data as array or not.
	 *
	 * @return array
	 */
	public function get_couriers( $zone = 'all', $account_type = 'all', $as_array = false ) {
		$couriers = array();

		foreach ( $this->couriers as $id => $courier ) {
			if ( 'domestic' === $zone ) {
				$services = $courier->get_services_domestic();

				if ( 'all' === $account_type && $services ) {
					$couriers[ $id ] = $as_array ? $courier->to_array( $zone ) : $courier;
				} elseif ( in_array( $account_type, $courier->get_account_domestic(), true ) ) {
					$couriers[ $id ] = $as_array ? $courier->to_array( $zone ) : $courier;
				}
			} elseif ( 'international' === $zone ) {
				$services = $courier->get_services_international();

				if ( 'all' === $account_type && $services ) {
					$couriers[ $id ] = $as_array ? $courier->to_array( $zone ) : $courier;
				} elseif ( in_array( $account_type, $courier->get_account_international(), true ) ) {
					$couriers[ $id ] = $as_array ? $courier->to_array( $zone ) : $courier;
				}
			} else {
				if ( 'all' === $account_type ) {
					$couriers[ $id ] = $as_array ? $courier->to_array( $zone ) : $courier;
				} elseif ( in_array( $account_type, $courier->get_account_domestic(), true ) ) {
					$couriers[ $id ] = $as_array ? $courier->to_array( $zone ) : $courier;
				} elseif ( in_array( $account_type, $courier->get_account_international(), true ) ) {
					$couriers[ $id ] = $as_array ? $courier->to_array( $zone ) : $courier;
				}
			}
		}

		return $couriers;
	}

	/**
	 * Get courier object or data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code Courier code.
	 * @param bool   $as_array Wethere to return data as array or not.
	 *
	 * @return (Woongkir_Courier|array|bool) Courier object or array data. False on failure.
	 */
	public function get_courier( $code, $as_array = false ) {
		$couriers = $this->get_couriers( 'all', 'all', $as_array );

		if ( isset( $couriers[ $code ] ) ) {
			return $couriers[ $code ];
		}

		return false;
	}

	/**
	 * Get courier object or data by response code.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code Courier code.
	 * @param bool   $as_array Wethere to return data as array or not.
	 *
	 * @return (Woongkir_Courier|array|bool) Courier object or array data. False on failure.
	 */
	public function get_courier_by_response( $code, $as_array = false ) {
		$couriers = $this->get_couriers( 'all', 'all', $as_array );

		foreach ( $couriers as $courier ) {
			if ( is_object( $courier ) && $courier->get_response_code() === $code ) {
				return $courier;
			}

			if ( is_array( $courier ) && $courier['response_code'] === $code ) {
				return $courier;
			}
		}

		return false;
	}


	/**
	 * Get couriers names
	 *
	 * @return array
	 */
	public function get_couriers_names() {
		$names = array();

		foreach ( $this->couriers as $courier ) {
			$names[ $courier->get_code() ] = $courier->get_label();
		}

		return $names;
	}

	/**
	 * Validate API request response.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $response API request response data.
	 *
	 * @throws Exception Error exception when response data is invalid.
	 *
	 * @return mixed WP_Error object on failure.
	 */
	public function api_response_parser( $response ) {
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

			$json_data  = json_decode( $body, true );
			$json_error = json_last_error_msg();

			if ( $json_error && strtolower( $json_error ) !== 'no error' ) {
				// translators: %1$s - JSON error message, %2$s API response body.
				throw new Exception( wp_sprintf( __( 'Failed to decode the JSON data: Error: %1$s, Body: %2$s', 'woongkir' ), $json_error, $body ) );
			}

			if ( isset( $json_data['rajaongkir']['status'] ) && 200 !== intval( $json_data['rajaongkir']['status']['code'] ) ) {
				$error_code        = $json_data['rajaongkir']['status']['code'];
				$error_description = isset( $json_data['rajaongkir']['status']['description'] ) ? $json_data['rajaongkir']['status']['description'] : '';

				// translators: %1$s - API error code, %2$s API error description.
				throw new Exception( wp_sprintf( __( 'Code: %1$s, Description: %2$s', 'woongkir' ), $error_code, $error_description ) );
			}

			if ( ! empty( $json_data['rajaongkir'] ) ) {
				return $json_data['rajaongkir'];
			}

			// translators: %1$s - API response body.
			throw new Exception( wp_sprintf( __( 'API response is invalid:  %1$s', 'woongkir' ), $body ) );
		} catch ( Exception $e ) {
			wc_get_logger()->log( 'error', wp_strip_all_tags( $e->getMessage(), true ), array( 'source' => 'woongkir_api_error' ) );

			// translators: %s - Error message from RajaOngkir.com.
			return new WP_Error( 'invalid_api_response', wp_sprintf( __( '<strong>Error from RajaOngkir.com</strong>: %s', 'woongkir' ), $e->getMessage() ) );
		}
	}

	/**
	 * Get API request full URL.
	 *
	 * @since ??
	 *
	 * @param string $endpoint API request endpoint.
	 *
	 * @return string
	 */
	public function api_request_url( $endpoint = '' ) {
		$account = $this->get_account();

		if ( ! $account ) {
			return $endpoint;
		}

		$request_url = rtrim( $account->get_api_url(), '/' );

		if ( ! $endpoint ) {
			return $request_url;
		}

		return $request_url . '/' . ltrim( $endpoint, '/' );
	}

	/**
	 * Populate API request parameters.
	 *
	 * @since ??
	 *
	 * @param array $custom_params Custom API request parameters.
	 *
	 * @return array
	 */
	public function api_request_params( $custom_params = array() ) {
		$args = array(
			'timeout' => 10,
			'headers' => array(
				'key' => $this->get_option( 'api_key' ),
			),
		);

		return array_merge_recursive( $args, $custom_params );
	}

	/**
	 * POST method API request
	 *
	 * @since ??
	 *
	 * @param string $endpoint API request endpoint.
	 * @param array  $body Body API request parameters.
	 * @param array  $custom_params Custom API request parameters.
	 *
	 * @return (WP_Error|array) The response or WP_Error on failure.
	 */
	public function api_request_post( $endpoint = '', $body = array(), $custom_params = array() ) {
		$response = apply_filters( 'woongkir_api_request_post_pre', false, $endpoint, $body, $custom_params, $this );

		if ( false === $response ) {
			$response = wp_remote_post(
				$this->api_request_url( $endpoint ),
				array_merge(
					$this->api_request_params( $custom_params ),
					array(
						'body' => $body,
					)
				)
			);
		}

		return $response;
	}

	/**
	 * GET method API request
	 *
	 * @since ??
	 *
	 * @param string $endpoint API request endpoint.
	 * @param array  $query_string API request Query string URL parameters.
	 * @param array  $custom_params Custom API request parameters.
	 *
	 * @return (WP_Error|array) The response or WP_Error on failure.
	 */
	public function api_request_get( $endpoint = '', $query_string = array(), $custom_params = array() ) {
		$response = apply_filters( 'woongkir_api_request_get_pre', false, $endpoint, $query_string, $custom_params, $this );

		if ( false === $response ) {
			$response = wp_remote_get( add_query_arg( $query_string, $this->api_request_url( $endpoint ) ), $this->api_request_params( $custom_params ) );
		}

		return $response;
	}

	/**
	 * Calculate domestic shipping cost
	 *
	 * @since ??
	 *
	 * @param array $params API request parameters.
	 *
	 * @return (WP_Error|array) The response or WP_Error on failure.
	 */
	public function calculate_shipping( $params = array() ) {
		$endpoint = '/cost';

		$parsed_params = $this->get_account()->api_request_parser( $params, $endpoint );

		if ( is_wp_error( $parsed_params ) ) {
			return $parsed_params;
		}

		$raw_response    = $this->api_request_post( $endpoint, $parsed_params );
		$parsed_response = $this->api_response_parser( $raw_response );

		if ( ! $parsed_response || is_wp_error( $parsed_response ) ) {
			return $parsed_response;
		}

		$rates = array();

		foreach ( $parsed_response['results'] as $result ) {
			if ( empty( $result['code'] ) || empty( $result['costs'] ) ) {
				continue;
			}

			$courier = $this->get_courier_by_response( $result['code'] );

			if ( ! $courier ) {
				// Add unregistered courier to log.
				wc_get_logger()->log(
					'info',
					wp_strip_all_tags(
						wp_json_encode(
							array_merge(
								$result,
								array(
									'query' => $parsed_response['query'],
								)
							)
						),
						true
					),
					array( 'source' => 'woongkir_api_unregistered_domestic_courier' )
				);

				continue;
			}

			$courier_services = $courier->get_services_domestic();

			foreach ( $result['costs'] as $rate ) {
				if ( empty( $rate['service'] ) || empty( $rate['cost'][0]['value'] ) ) {
					continue;
				}

				if ( ! isset( $courier_services[ $rate['service'] ] ) ) {
					// Add unregistered service to log.
					wc_get_logger()->log(
						'info',
						wp_strip_all_tags(
							wp_json_encode(
								array_merge(
									$rate,
									array(
										'courier' => $courier->get_code(),
										'query'   => $parsed_response['query'],
									)
								)
							)
						),
						array( 'source' => 'woongkir_api_unregistered_domestic_service' )
					);
				}

				$etd  = isset( $rate['cost'][0]['etd'] ) ? $this->parse_etd( $rate['cost'][0]['etd'] ) : '';
				$cost = $rate['cost'][0]['value'];

				$rates[] = array(
					'courier'  => $courier->get_code(),
					'service'  => $rate['service'],
					'etd'      => $etd,
					'cost'     => $cost,
					'currency' => 'IDR',
				);
			}
		}

		return array(
			'formatted' => $rates,
			'raw'       => $parsed_response,
		);
	}

	/**
	 * Calculate international shipping cost
	 *
	 * @since ??
	 *
	 * @param array $params API request parameters.
	 *
	 * @return (WP_Error|array) The response or WP_Error on failure.
	 */
	public function calculate_shipping_international( $params = array() ) {
		$account  = $this->get_account();
		$endpoint = '/v2/internationalCost';

		$parsed_params = $account->api_request_parser( $params, $endpoint );

		if ( is_wp_error( $parsed_params ) ) {
			return $parsed_params;
		}

		$raw_response    = $this->api_request_post( $endpoint, $parsed_params );
		$parsed_response = $this->api_response_parser( $raw_response );

		if ( ! $parsed_response || is_wp_error( $parsed_response ) ) {
			return $parsed_response;
		}

		$rates = array();

		foreach ( $parsed_response['results'] as $result ) {
			if ( empty( $result['code'] ) || empty( $result['costs'] ) ) {
				continue;
			}

			$courier = $this->get_courier_by_response( $result['code'] );

			if ( ! $courier ) {
				// Add unregistered courier to log.
				wc_get_logger()->log(
					'info',
					wp_strip_all_tags(
						wp_json_encode(
							array_merge(
								$result,
								array(
									'query' => $parsed_response['query'],
								)
							)
						),
						true
					),
					array( 'source' => 'woongkir_api_unregistered_international_courier' )
				);

				continue;
			}

			$courier_services = $courier->get_services_international();

			foreach ( $result['costs'] as $rate ) {
				if ( empty( $rate['service'] ) || empty( $rate['cost'] ) ) {
					continue;
				}

				if ( ! isset( $courier_services[ $rate['service'] ] ) ) {
					// Add unregistered service to log.
					wc_get_logger()->log(
						'info',
						wp_strip_all_tags(
							wp_json_encode(
								array_merge(
									$rate,
									array(
										'courier' => $courier->get_code(),
										'query'   => $parsed_response['query'],
									)
								)
							)
						),
						array( 'source' => 'woongkir_api_unregistered_international_service' )
					);
				}

				$etd      = isset( $rate['etd'] ) ? $this->parse_etd( $rate['etd'] ) : '';
				$cost     = $rate['cost'];
				$currency = isset( $rate['currency'] ) ? $rate['currency'] : 'IDR';

				if ( 'IDR' !== $currency && ! empty( $parsed_response['currency']['value'] ) ) {
					$cost     = $cost * $parsed_response['currency']['value'];
					$currency = 'IDR';
				}

				$rates[] = array(
					'courier'  => $courier->get_code(),
					'service'  => $rate['service'],
					'etd'      => $etd,
					'cost'     => $cost,
					'currency' => $currency,
				);
			}
		}

		return array(
			'formatted' => $rates,
			'raw'       => $parsed_response,
		);
	}

	/**
	 * Parse API response ETD data.
	 *
	 * @since ??
	 *
	 * @param string $etd API response ETD data.
	 *
	 * @return string
	 */
	private function parse_etd( $etd ) {
		if ( ! $etd ) {
			return '';
		}

		$etd = str_replace(
			array( 'jam', 'hari' ),
			array( __( 'hours', 'woongkir' ), __( 'days', 'woongkir' ) ),
			strtolower( $etd )
		);

		if ( false === strpos( $etd, 'hours' ) && false === strpos( $etd, 'days' ) ) {
			$etd = trim( $etd ) . ' ' . __( 'days', 'woongkir' );
		}

		// Trim the etd data.
		$etd = array_map( 'trim', explode( '-', $etd ) );

		// Join the etd data.
		$etd = implode( ' - ', $etd );

		return $etd;
	}
}
