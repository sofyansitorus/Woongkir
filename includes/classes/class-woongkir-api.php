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
	 *
	 * @var Woongkir_Account[]
	 */
	private $accounts = array();

	/**
	 * List of used delivery couriers and services.
	 *
	 * @since 1.0.0
	 *
	 * @var Woongkir_Courier[]
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
	 * @since 1.2.12
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
			uasort( $this->accounts, 'woongkir_sort_by_priority' );
		}
	}

	/**
	 * Populate couriers list
	 *
	 * @since 1.2.12
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
			uasort( $this->couriers, 'woongkir_sort_by_priority' );
		}
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
	 * Validate API account.
	 *
	 * @since 1.0.0
	 */
	public function validate_account() {
		$account_type = $this->get_option( 'account_type', 'starter' );

		$params = array(
			'weight'          => 1700,
			'courier'         => array(
				'starter' => 'jne',
				'basic'   => 'jne:pos',
				'pro'     => 'jne:pos:tiki',
			),
			'origin'          => array(
				'starter' => 501,
				'basic'   => 501,
				'pro'     => 501,
			),
			'originType'      => array(
				'pro' => 'city',
			),
			'destination'     => array(
				'starter' => 114,
				'basic'   => 114,
				'pro'     => 574,
			),
			'destinationType' => array(
				'pro' => 'subdistrict',
			),
		);

		$normalized = array();

		foreach ( $params as $key => $value ) {
			if ( is_array( $value ) ) {
				if ( isset( $value[ $account_type ] ) ) {
					$normalized[ $key ] = $value[ $account_type ];
				}
			} else {
				$normalized[ $key ] = $value;
			}
		}

		return $this->calculate_shipping_by_zone( 'domestic', $normalized );
	}

	/**
	 * Get accounts object or data.
	 *
	 * @since 1.2.12
	 *
	 * @param bool $as_array Wether to return data as array or not.
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
	 * @param bool   $as_array Wether to return data as array or not.
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
	 * @since 1.2.12
	 *
	 * @param string  $zone Couriers zone: domestic, international, all.
	 * @param string  $account_type Filters couriers allowed for specific account type: starter, basic, prop, all.
	 * @param boolean $as_array Wether to return data as array or not.
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
	 * @param bool   $as_array Wether to return data as array or not.
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
	 *
	 * @return (Woongkir_Courier|bool) Courier object or False on failure.
	 */
	public function get_courier_by_response( $code ) {
		if ( isset( $this->couriers[ $code ] ) ) {
			return $this->couriers[ $code ];
		}

		foreach ( $this->couriers as $courier ) {
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
	 * Get shipping zones.
	 *
	 * @return string[]
	 */
	public function get_zones() {
		return array(
			'domestic'      => __( 'Domestic Shipping Couriers', 'woongkir' ),
			'international' => __( 'International Shipping Couriers', 'woongkir' ),
		);
	}

	/**
	 * Get zone by country
	 *
	 * @param string $country Country code ALPHA-2.
	 *
	 * @return string
	 */
	public function get_zone_by_country( $country ) {
		if ( 'ID' === $country ) {
			return 'domestic';
		}

		return 'international';
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

			$status_code        = isset( $json_data['rajaongkir']['status']['code'] ) ? intval( $json_data['rajaongkir']['status']['code'] ) : 0;
			$status_description = isset( $json_data['rajaongkir']['status']['description'] ) ? $json_data['rajaongkir']['status']['description'] : '';

			if ( 200 !== $status_code ) {
				// translators: %1$s - API error code, %2$s API error description.
				throw new Exception( wp_sprintf( __( 'Code: %1$s, Description: %2$s', 'woongkir' ), $status_code, $status_description ) );
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
	 * @since 1.2.12
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
	 * @since 1.2.12
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
	 * @since 1.2.12
	 *
	 * @param string $endpoint API request endpoint.
	 * @param array  $body Body API request parameters.
	 * @param array  $custom_params Custom API request parameters.
	 *
	 * @return (WP_Error|array) The response or WP_Error on failure.
	 */
	public function api_request_post( $endpoint = '', $body = array(), $custom_params = array() ) {
		/**
		 * Filter POST method API request.
		 *
		 * Allows modification of the POST method API request before the actual API request is made.
		 *
		 * @since 1.2.12
		 *
		 * @param bool         $response      API response data. Default is false.
		 * @param string       $endpoint      API request endpoint.
		 * @param array        $body Body     API request parameters.
		 * @param array        $custom_params Custom API request parameters.
		 * @param Woongkir_API $object        Current class object.
		 *
		 * @return bool
		 */
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
	 * @since 1.2.12
	 *
	 * @param string $endpoint API request endpoint.
	 * @param array  $query_string API request Query string URL parameters.
	 * @param array  $custom_params Custom API request parameters.
	 *
	 * @return (WP_Error|array) The response or WP_Error on failure.
	 */
	public function api_request_get( $endpoint = '', $query_string = array(), $custom_params = array() ) {
		/**
		 * Filter GET method API request.
		 *
		 * Allows modification of the GET method API request before the actual API request is made.
		 *
		 * @since 1.2.12
		 *
		 * @param bool         $response      API response data. Default is false.
		 * @param string       $endpoint      API request endpoint.
		 * @param array        $query_string  API request Query string URL parameters.
		 * @param array        $custom_params Custom API request parameters.
		 * @param Woongkir_API $object        Current class object.
		 *
		 * @return bool
		 */
		$response = apply_filters( 'woongkir_api_request_get_pre', false, $endpoint, $query_string, $custom_params, $this );

		if ( false === $response ) {
			$response = wp_remote_get( add_query_arg( $query_string, $this->api_request_url( $endpoint ) ), $this->api_request_params( $custom_params ) );
		}

		return $response;
	}

	/**
	 * Calculate domestic shipping cost
	 *
	 * @since 1.2.12
	 *
	 * @param array $params API request parameters.
	 *
	 * @throws Exception Error message.
	 *
	 * @return (WP_Error|array) The response or WP_Error on failure.
	 */
	public function calculate_shipping( $params = array() ) {
		_deprecated_function( __METHOD__, '1.3', __CLASS__ . '::calculate_shipping_by_zone' );

		$zone = isset( $params['zone'] ) ? $params['zone'] : '';

		if ( isset( $params['zone'] ) ) {
			unset( $params['zone'] );
		}

		return $this->calculate_shipping_by_zone( $zone, $params );
	}

	/**
	 * Calculate international shipping cost
	 *
	 * @since 1.2.12
	 *
	 * @param array $params API request parameters.
	 *
	 * @return (WP_Error|array) The response or WP_Error on failure.
	 */
	public function calculate_shipping_international( $params = array() ) {
		_deprecated_function( __METHOD__, '1.3', __CLASS__ . '::calculate_shipping_by_zone' );

		$zone = isset( $params['zone'] ) ? $params['zone'] : '';

		if ( isset( $params['zone'] ) ) {
			unset( $params['zone'] );
		}

		return $this->calculate_shipping_by_zone( $zone, $params );
	}

	/**
	 * Calculate shipping cost by zone
	 *
	 * @since 1.3
	 *
	 * @param string $zone Shipping zone.
	 * @param array  $params API request parameters.
	 *
	 * @throws Exception Error message.
	 *
	 * @return (array|WP_Error) The rates data array or WP_Error on failure.
	 */
	public function calculate_shipping_by_zone( $zone, $params = array() ) {
		$account = $this->get_account();

		if ( ! $zone ) {
			return new WP_Error( 'api_calculate_shipping_empty_zone', __( 'Shipping zone parameter is empty.', 'woongkir' ) );
		}

		if ( ! array_key_exists( $zone, $this->get_zones() ) ) {
			return new WP_Error( 'api_calculate_shipping_invalid_zone', __( 'Shipping zone parameter is invalid.', 'woongkir' ) );
		}

		$endpoint = 'international' === $zone ? '/v2/internationalCost' : '/cost';

		$courier = isset( $params['courier'] ) ? $params['courier'] : array();

		if ( $courier && ! is_array( $courier ) ) {
			$courier = explode( ':', $courier );
		}

		if ( count( $courier ) > 1 && ! $account->can_do( 'multiple_couriers' ) ) {
			$courier = array( $courier[0] );
		}

		/**
		 * Filter POST method API request.
		 *
		 * Allows modification of the POST method API request before the actual API request is made.
		 *
		 * @since 1.2.12
		 *
		 * @param integer      $chunk_count API request courier max count. Default is 7.
		 * @param array        $zone        API request parameters.
		 * @param array        $params      API request parameters.
		 * @param Woongkir_API $object      API class object.
		 *
		 * @return integer
		 */
		$chunk_count = apply_filters( 'woongkir_api_courier_chunk_count', 7, $zone, $params, $this );

		$raw                = array();
		$api_request_errors = new WP_Error();
		$retry_requests     = array();

		foreach ( array_chunk( $courier, $chunk_count ) as $chunk ) {
			try {
				$api_response = $this->api_response_parser(
					$this->api_request_post(
						$endpoint,
						array_merge(
							$params,
							array(
								'courier' => implode( ':', $chunk ),
							)
						)
					)
				);

				if ( is_wp_error( $api_response ) ) {
					if ( ! in_array( $api_response->get_error_code(), $api_request_errors->get_error_codes(), true ) ) {
						$api_request_errors->add( $api_response->get_error_code(), $api_response->get_error_message() );
					}

					if ( 1 < count( $chunk ) ) {
						$retry_requests = array_merge( $retry_requests, $chunk );
					}

					throw new Exception( $api_response->get_error_message() );
				}

				foreach ( $api_response as $key => $value ) {
					if ( 'results' === $key ) {
						if ( ! isset( $raw[ $key ] ) ) {
							$raw[ $key ] = array();
						}

						foreach ( $value as $result ) {
							$raw[ $key ][] = $result;
						}
					} else {
						$raw[ $key ] = $value;
					}
				}
			} catch ( Exception $e ) {
				wc_get_logger()->log( 'error', wp_strip_all_tags( $e->getMessage(), true ), array( 'source' => 'woongkir_api_error' ) );
			}
		}

		// Retry failed chunk requests as single courier per request.
		if ( $retry_requests ) {
			foreach ( $retry_requests as $chunk ) {
				$api_response = $this->api_response_parser(
					$this->api_request_post(
						$endpoint,
						array_merge(
							$params,
							array(
								'courier' => $chunk,
							)
						)
					)
				);

				if ( ! is_wp_error( $api_response ) ) {
					foreach ( $api_response as $key => $value ) {
						if ( 'results' === $key ) {
							if ( ! isset( $raw[ $key ] ) ) {
								$raw[ $key ] = array();
							}

							foreach ( $value as $result ) {
								$raw[ $key ][] = $result;
							}
						} else {
							$raw[ $key ] = $value;
						}
					}
				}
			}
		}

		if ( ! $raw ) {
			if ( $api_request_errors->has_errors() ) {
				return $api_request_errors;
			}

			return new WP_Error( 'api_calculate_shipping_empty_response', __( 'API response is empty.', 'woongkir' ) );
		}

		$parsed = array();

		foreach ( $raw['results'] as $result ) {
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
									'query' => $raw['query'],
								)
							)
						),
						true
					),
					array( 'source' => 'woongkir_api_unregistered_courier' )
				);

				continue;
			}

			$courier_services = $courier->get_services( $zone );

			foreach ( $result['costs'] as $rate ) {
				if ( ! isset( $rate['service'] ) ) {
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
										'query'   => $raw['query'],
									)
								)
							)
						),
						array( 'source' => 'woongkir_api_unregistered_service' )
					);

					if ( isset( $rate['description'] ) ) {
						$courier->add_service( $rate['service'], $rate['description'], $zone );
					} else {
						$courier->add_service( $rate['service'], $rate['service'], $zone );
					}

					continue;
				}

				$rate_normalized = array();

				foreach ( $rate as $key => $value ) {
					if ( 'cost' === $key ) {
						if ( ! is_array( $value ) ) {
							$value = array(
								array(
									'value' => $value,
								),
							);
						}

						foreach ( $value[0] as $cost_key => $cost_value ) {
							if ( 'value' === $cost_key ) {
								$cost_key = 'cost';
							}

							$rate_normalized[ $cost_key ] = $cost_value;
						}
					} else {
						$rate_normalized[ $key ] = $value;
					}
				}

				$rate_normalized = wp_parse_args(
					$rate_normalized,
					array(
						'service'         => '',
						'description'     => '',
						'cost'            => 0,
						'currency'        => 'IDR',
						'etd'             => '',
						'note'            => '',
						'cost_conversion' => false,
					)
				);

				if ( 'IDR' !== $rate_normalized['currency'] ) {
					$exchange_rate = ! empty( $raw['currency']['value'] ) ? $raw['currency']['value'] : $this->get_exchange_rate();

					if ( 0 === intval( $exchange_rate ) ) {
						continue;
					}

					$cost_conversion = array(
						'currency'      => $rate_normalized['currency'],
						'cost'          => $rate_normalized['cost'],
						'exchange_rate' => $exchange_rate,
					);

					$rate_normalized = array_merge(
						$rate_normalized,
						array(
							'currency'        => 'IDR',
							'cost'            => ( $rate_normalized['cost'] * $exchange_rate ),
							'cost_conversion' => $cost_conversion,
						)
					);
				}

				if ( ! $rate_normalized['cost'] ) {
					continue;
				}

				if ( ! empty( $rate_normalized['etd'] ) ) {
					$rate_normalized['etd'] = woongkir_parse_etd( $rate_normalized['etd'] );
				}

				$parsed[] = array_merge(
					$rate_normalized,
					array(
						'courier' => $courier->get_code(),
					)
				);
			}
		}

		if ( ! $parsed ) {
			return new WP_Error( 'api_calculate_shipping_empty_parsed', __( 'Failed to parse API response data.', 'woongkir' ) );
		}

		return array(
			'parsed' => $parsed,
			'raw'    => $raw,
		);
	}

	/**
	 * Get currency exchange rate.
	 *
	 * @since 1.3
	 *
	 * @return integer
	 */
	public function get_exchange_rate() {
		static $exchange_rate = null;

		if ( ! is_null( $exchange_rate ) ) {
			return $exchange_rate;
		}

		$api_response = $this->api_response_parser( $this->api_request_get( '/currency' ) );

		if ( is_array( $api_response ) && isset( $api_response['result']['value'] ) ) {
			$exchange_rate = $api_response['result']['value'];
		} else {
			$exchange_rate = 0;
		}

		return $exchange_rate;
	}
}
