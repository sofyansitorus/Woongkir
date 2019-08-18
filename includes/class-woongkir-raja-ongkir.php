<?php
/**
 * The file that defines the Woongkir_Raja_Ongkir class
 *
 * @link       https://github.com/sofyansitorus
 * @since      1.0.0
 *
 * @package    Woongkir
 * @subpackage Woongkir/includes
 */

/**
 * The Woongkir_Raja_Ongkir API class.
 *
 * This is used to make request to RajaOngkir.com API server.
 *
 * @since      1.0.0
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Raja_Ongkir {

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
	private $accounts = array(
		'starter' => array(
			'label'            => 'Starter',
			'api_url'          => 'http://api.rajaongkir.com/starter',
			'subdistrict'      => false,
			'multiple_coriers' => false,
		),
		'basic'   => array(
			'label'            => 'Basic',
			'api_url'          => 'http://api.rajaongkir.com/basic',
			'subdistrict'      => false,
			'multiple_coriers' => true,
		),
		'pro'     => array(
			'label'            => 'Pro',
			'api_url'          => 'http://pro.rajaongkir.com/api',
			'subdistrict'      => true,
			'multiple_coriers' => true,
		),
	);

	/**
	 * List of used delivery couriers and services.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $couriers = array(
		'domestic'      => array(
			'pos'     => array(
				'label'    => 'POS Indonesia',
				'website'  => 'http://www.posindonesia.co.id',
				'services' => array(
					'Surat Kilat Khusus',
					'Paketpos Biasa',
					'Paket Kilat Khusus',
					'Express Sameday Dokumen',
					'Express Sameday Barang',
					'Express Next Day Dokumen',
					'Express Next Day Barang',
					'Paketpos Dangerous Goods',
					'Paketpos Valuable Goods',
				),
				'account'  => array(
					'starter',
					'basic',
					'pro',
				),
			),
			'tiki'    => array(
				'label'    => 'TIKI',
				'website'  => 'http://tiki.id',
				'services' => array(
					'TRC',
					'REG',
					'ECO',
					'ONS',
					'SDS',
					'HDS',
				),
				'account'  => array(
					'starter',
					'basic',
					'pro',
				),
			),
			'jne'     => array(
				'label'    => 'JNE',
				'website'  => 'http://www.jne.co.id',
				'services' => array(
					'CTC',
					'CTCYES',
					'OKE',
					'REG',
					'YES',
				),
				'account'  => array(
					'starter',
					'basic',
					'pro',
				),
			),
			'rpx'     => array(
				'label'    => 'RPX',
				'website'  => 'http://www.rpx.co.id',
				'services' => array(
					'SDP',
					'MDP',
					'NDP',
					'RGP',
					'REP',
					'ERP',
				),
				'account'  => array(
					'basic',
					'pro',
				),
			),
			'pcp'     => array(
				'label'    => 'PCP Express',
				'website'  => 'http://www.pcpexpress.com',
				'services' => array(
					'ONS',
					'NFS',
					'REG',
				),
				'account'  => array(
					'basic',
					'pro',
				),
			),
			'star'    => array(
				'label'    => 'Star Cargo',
				'website'  => 'http://www.starcargo.co.id',
				'services' => array(
					'Express',
					'Reguler',
					'Dokumen',
					'MOTOR',
					'MOTOR 150 - 250 CC',
				),
				'account'  => array(
					'pro',
				),
			),
			'sicepat' => array(
				'label'    => 'SiCepat Express',
				'website'  => 'http://www.sicepat.com',
				'services' => array(
					'REG',
					'BEST',
					'Priority',
				),
				'account'  => array(
					'pro',
				),
			),
			'jet'     => array(
				'label'    => 'JET Express',
				'website'  => 'http://www.jetexpress.co.id',
				'services' => array(
					'CRG',
					'PRI',
					'REG',
				),
				'account'  => array(
					'pro',
				),
			),
			'sap'     => array(
				'label'    => 'SAP Express',
				'website'  => 'http://sap-express.id',
				'services' => array(
					'REG',
					'SDS',
					'ODS',
				),
				'account'  => array(
					'pro',
				),
			),
			'pahala'  => array(
				'label'    => 'Pahala Express',
				'website'  => 'http://www.pahalaexpress.co.id',
				'services' => array(
					'EXPRESS',
					'ONS',
				),
				'account'  => array(
					'pro',
				),
			),
			'slis'    => array(
				'label'    => 'Solusi Ekspres',
				'website'  => 'http://www.solusiekspres.com',
				'services' => array(
					'REGULAR',
					'EXPRESS',
				),
				'account'  => array(
					'pro',
				),
			),
			'jnt'     => array(
				'label'    => 'J&T Express',
				'website'  => 'http://www.jet.co.id',
				'services' => array(
					'EZ',
				),
				'account'  => array(
					'pro',
				),
			),
			'pandu'   => array(
				'label'    => 'Pandu Logistics',
				'website'  => 'http://www.pandulogistics.com',
				'services' => array(
					'REG',
				),
				'account'  => array(
					'pro',
				),
			),
			'wahana'  => array(
				'label'    => 'Wahana Express',
				'website'  => 'http://www.wahana.com',
				'services' => array(
					'DES',
				),
				'account'  => array(
					'pro',
				),
			),
		),
		'international' => array(
			'pos'      => array(
				'label'    => 'POS Indonesia',
				'website'  => 'http://www.posindonesia.co.id',
				'services' => array(
					'Surat R LN',
					'EMS BARANG',
					'PAKETPOS CEPAT LN',
					'PAKETPOS BIASA LN',
				),
				'account'  => array(
					'basic',
					'pro',
				),
			),
			'jne'      => array(
				'label'    => 'JNE',
				'website'  => 'http://www.jne.co.id',
				'services' => array(
					'INTL',
				),
				'account'  => array(
					'pro',
				),
			),
			'slis'     => array(
				'label'    => 'Solusi Ekspres',
				'website'  => 'http://www.solusiekspres.com',
				'services' => array(
					'PACKAGE',
					'COSMETIC/FOOD',
				),
				'account'  => array(
					'pro',
				),
			),
			'expedito' => array(
				'label'    => 'Expedito',
				'website'  => 'http://www.expedito.co.id',
				'services' => array(
					'CityLink',
					'DHL Indonesia',
					'DPEX',
					'FIRST FLIGHT',
					'TNT | Fedex',
				),
				'account'  => array(
					'pro',
				),
			),
		),
	);

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
		$results        = array();
		$account        = $this->get_account( $this->get_option( 'account_type' ) );
		$endpoint       = empty( $destination['country'] ) ? 'cost' : 'internationalCost';
		$courier        = $account['multiple_coriers'] ? $courier : array_slice( $courier, 1 );
		$courier_chunks = array_chunk( $courier, apply_filters( 'woongkir_api_courier_chunks', 3 ) );

		foreach ( $courier_chunks as $courier_chunk ) {
			switch ( $endpoint ) {
				case 'internationalCost':
					$params = array(
						'destination' => $destination['country'],
						'origin'      => $origin['city'],
						'courier'     => implode( ':', $courier_chunk ),
					);
					break;

				default:
					$params = array(
						'destination'     => ( $account['subdistrict'] && ! empty( $destination['subdistrict'] ) ) ? $destination['subdistrict'] : $destination['city'],
						'destinationType' => ( $account['subdistrict'] && ! empty( $destination['subdistrict'] ) ) ? 'subdistrict' : 'city',
						'origin'          => ( $account['subdistrict'] && ! empty( $origin['subdistrict'] ) ) ? $origin['subdistrict'] : $origin['city'],
						'originType'      => ( $account['subdistrict'] && ! empty( $origin['subdistrict'] ) ) ? 'subdistrict' : 'city',
						'courier'         => implode( ':', $courier_chunk ),
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
	 * Get account data.
	 *
	 * @since 1.0.0
	 * @param string $account_type Acoount type key.
	 */
	public function get_account( $account_type = null ) {
		if ( ! is_null( $account_type ) ) {
			return isset( $this->accounts[ $account_type ] ) ? $this->accounts[ $account_type ] : false;
		}
		return $this->accounts;
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
		$response = apply_filters( 'woongkir_api_remote_request_pre', false, $endpoint, $args, $param, $this );

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
				throw new Exception( $response->get_error_message(), 1 );
			}

			$body = wp_remote_retrieve_body( $response );

			if ( empty( $body ) ) {
				throw new Exception( __( 'API response is empty.', 'woongkir' ), 1 );
			}

			$data       = json_decode( $body );
			$json_error = json_last_error_msg();

			if ( strtolower( $json_error ) !== 'no error' ) {
				// Try to capture the JSON string for response that has output incorrect JSON format.
				preg_match( '/{"rajaongkir"(.*?)}}/m', $body, $matches, PREG_OFFSET_CAPTURE, 0 );

				if ( ! isset( $matches[0][0] ) || empty( $matches[0][0] ) ) {
					$body = is_string( $body ) ? $body : wp_json_encode( $body );
					// translators: %1$s - Error message from RajaOngkir.com, %2$s - API response body.
					throw new Exception( wp_sprintf( __( '%1$s -- %2$s', 'woongkir' ), $json_error, $body ), 1 );
				}

				$body       = $matches[0][0];
				$data       = json_decode( $matches[0][0] );
				$json_error = json_last_error_msg();

				if ( strtolower( $json_error ) !== 'no error' ) {
					$body = is_string( $body ) ? $body : wp_json_encode( $body );
					// translators: %1$s - Error message from RajaOngkir.com, %2$s - API response body.
					throw new Exception( wp_sprintf( __( '%1$s -- %2$s', 'woongkir' ), $json_error, $body ), 1 );
				}
			}

			if ( isset( $data->rajaongkir->status ) && 200 !== $data->rajaongkir->status->code ) {
				// translators: %s - Error message from RajaOngkir.com.
				throw new Exception( $data->rajaongkir->status->description, 1 );
			}

			if ( isset( $data->rajaongkir->results ) ) {
				return $data->rajaongkir->results;
			}

			if ( isset( $data->rajaongkir->result ) ) {
				return $data->rajaongkir->result;
			}

			throw new Exception( __( 'API response is invalid.', 'woongkir' ), 1 );
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
		$params = array(
			'destination'      => array(
				'country'     => 0,
				'province'    => 0,
				'city'        => 0,
				'subdistrict' => 0,
			),
			'origin'           => array(
				'province'    => 0,
				'city'        => 0,
				'subdistrict' => 0,
			),
			'dimension_weight' => array(
				'width'  => 0,
				'length' => 0,
				'height' => 0,
				'weight' => 1700,
			),
			'courier'          => array(
				'jne',
				'tiki',
				'pos',
			),
		);

		switch ( $this->get_option( 'account_type' ) ) {
			case 'pro':
				$params['destination']['subdistrict'] = 574;
				$params['origin']['subdistrict']      = 538;
				break;
			case 'basic':
				$params['destination']['city'] = 114;
				$params['origin']['city']      = 501;
				break;
			default:
				$params['destination']['city'] = 114;
				$params['origin']['city']      = 501;
				$params['courier']             = array( 'jne' );
				break;
		}

		return $this->get_cost( $params['destination'], $params['origin'], $params['dimension_weight'], $params['courier'] );
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
}
