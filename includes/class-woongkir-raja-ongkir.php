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
			'volumetric'       => false,
			'dedicated_server' => false,
		),
		'basic'   => array(
			'label'            => 'Basic',
			'api_url'          => 'http://api.rajaongkir.com/basic',
			'subdistrict'      => false,
			'multiple_coriers' => true,
			'volumetric'       => false,
			'dedicated_server' => false,
		),
		'pro'     => array(
			'label'            => 'Pro',
			'api_url'          => 'http://pro.rajaongkir.com/api',
			'subdistrict'      => true,
			'multiple_coriers' => true,
			'volumetric'       => true,
			'dedicated_server' => true,
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
			'jne'     => array(
				'label'    => 'JNE',
				'website'  => 'http://www.jne.co.id',
				'services' => array(
					'CTC'    => 'City Courier',
					'CTCYES' => 'City Courier YES',
					'OKE'    => 'Ongkos Kirim Ekonomis',
					'REG'    => 'Layanan Reguler',
					'YES'    => 'Yakin Esok Sampai',
				),
				'account'  => array(
					'starter',
					'basic',
					'pro',
				),
			),
			'pos'     => array(
				'label'    => 'POS Indonesia',
				'website'  => 'http://www.posindonesia.co.id',
				'services' => array(
					'Surat Kilat Khusus'       => 'Surat Kilat Khusus',
					'Paketpos Biasa'           => 'Paketpos Biasa',
					'Paket Kilat Khusus'       => 'Paket Kilat Khusus',
					'Express Samedat Barang'   => 'Express Samedat Barang',
					'Express Samedat Dokumen'  => 'Express Samedat Dokumen',
					'Express Next Day Barang'  => 'Express Next Day Barang',
					'Express Next Day Dokumen' => 'Express Next Day Dokumen',
					'Paketpos Dangerous Goods' => 'Paketpos Dangerous Goods',
					'Paketpos Valuable Goods'  => 'Paketpos Valuable Goods',
					'Kargopos Ritel Train'     => 'Kargopos Ritel Train',
					'Kargopos Ritel Udara Dn'  => 'Kargopos Ritel Udara Dn',
					'Paket Jumbo Ekonomi'      => 'Paket Jumbo Ekonomi',
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
					'REG' => 'Regular Service',
					'ECO' => 'Economy Service',
					'ONS' => 'Over Night Service',
					'SDS' => 'Same Day Service',
					'HDS' => 'Holiday Services',
					'TRC' => 'Trucking Service',
				),
				'account'  => array(
					'starter',
					'basic',
					'pro',
				),
			),
			'pcp'     => array(
				'label'    => 'PCP Express',
				'website'  => 'http://www.pcpexpress.com',
				'services' => array(
					'TREX' => 'Titipan Regular Express',
					'JET'  => 'Jaminan Esok Tiba',
					'HIT'  => 'Hari Ini Tiba',
					'EXIS' => 'Express Ekonomi',
					'GODA' => 'Kargo Darat',
				),
				'account'  => array(
					'basic',
					'pro',
				),
			),
			'rpx'     => array(
				'label'    => 'RPX',
				'website'  => 'http://www.rpx.co.id',
				'services' => array(
					'SDP' => 'SameDay Package',
					'MDP' => 'MidDay Package',
					'NDP' => 'Next Day Package',
					'RGP' => 'Regular Package',
					'REP' => 'PAS Reguler',
					'PAS' => 'Paket Ambil Suka-Suka',
				),
				'account'  => array(
					'basic',
					'pro',
				),
			),
			'pandu'   => array(
				'label'    => 'Pandu Logistics',
				'website'  => 'http://www.pandulogistics.com',
				'services' => array(
					'REG' => 'Regular Package',
				),
				'account'  => array(
					'pro',
				),
			),
			'wahana'  => array(
				'label'    => 'Wahana Express',
				'website'  => 'http://www.wahana.com',
				'services' => array(
					'Normal' => 'Normal Service',
				),
				'account'  => array(
					'pro',
				),
			),
			'sicepat' => array(
				'label'    => 'SiCepat Express',
				'website'  => 'http://www.sicepat.com',
				'services' => array(
					'REG'   => 'Layanan Reguler',
					'BEST'  => 'Besok Sampai Tujuan',
					'Cargo' => 'Cargo',
				),
				'account'  => array(
					'pro',
				),
			),
			'jnt'     => array(
				'label'    => 'J&T Express',
				'website'  => 'http://www.jet.co.id',
				'services' => array(
					'EZ'  => 'Regular Service',
					'JSD' => 'Same Day Service',
				),
				'account'  => array(
					'pro',
				),
			),
			'pahala'  => array(
				'label'    => 'Pahala Express',
				'website'  => 'http://www.pahalaexpress.co.id',
				'services' => array(
					'EXPRESS'     => 'Express Service',
					'ONS'         => 'One Night Service',
					'SDS'         => 'Same Day Service',
					'SEPEDA'      => 'Paket Sepeda',
					'MOTOR SPORT' => 'Paket Motor Sport',
					'MOTOR BESAR' => 'Paket Motor Besar',
					'MOTOR BEBEK' => 'Paket Motor Bebek',
				),
				'account'  => array(
					'pro',
				),
			),
			'sap'     => array(
				'label'    => 'SAP Express',
				'website'  => 'http://sap-express.id',
				'services' => array(
					'REG' => 'Regular Service',
					'SDS' => 'Same Day Service',
					'ODS' => 'One Day Service',
				),
				'account'  => array(
					'pro',
				),
			),
			'jet'     => array(
				'label'    => 'JET Express',
				'website'  => 'http://www.jetexpress.co.id',
				'services' => array(
					'PRI' => 'Priority',
					'XPS' => 'Express',
					'REG' => 'Regular',
					'CRG' => 'Cargo',
				),
				'account'  => array(
					'pro',
				),
			),
			'slis'    => array(
				'label'    => 'Solusi Ekspres',
				'website'  => 'http://www.solusiekspres.com',
				'services' => array(
					'REGULAR' => 'Regular Service',
					'EXPRESS' => 'Express Service',
				),
				'account'  => array(
					'pro',
				),
			),
			'dse'     => array(
				'label'    => '21 Express',
				'website'  => 'http://21express.co.id',
				'services' => array(
					'ECO' => 'Regular Service',
					'ONS' => 'Over Night Service',
					'SDS' => 'Same Day Service',
				),
				'account'  => array(
					'pro',
				),
			),
			'ncs'     => array(
				'label'    => 'Nusantara Card Semesta',
				'website'  => 'http://www.ptncs.com',
				'services' => array(
					'NRS' => 'REGULAR SERVICE',
					'ONS' => 'OVERNIGHT SERVICE',
					'SDS' => 'SAME DAY SERVICE',
				),
				'account'  => array(
					'pro',
				),
			),
			'star'    => array(
				'label'    => 'Star Cargo',
				'website'  => 'http://www.starcargo.co.id',
				'services' => array(
					'Express'            => 'Express',
					'Reguler'            => 'Reguler',
					'Dokumen'            => 'Dokumen',
					'MOTOR'              => 'MOTOR',
					'MOTOR 150 - 250 CC' => 'MOTOR 150 - 250 CC',
				),
				'account'  => array(
					'pro',
				),
			),
			'lion'    => array(
				'label'    => 'Lion Parcel',
				'website'  => 'http://lionparcel.com',
				'services' => array(
					'ONEPACK'  => 'One Day Service',
					'LANDPACK' => 'Logistic Service',
					'REGPACK'  => 'Regular Service',
				),
				'account'  => array(
					'pro',
				),
			),
			'ninja'   => array(
				'label'    => 'Ninja Xpress',
				'website'  => 'https://www.ninjaxpress.co',
				'services' => array(
					'STANDARD' => 'Standard Service',
				),
				'account'  => array(
					'pro',
				),
			),
			'idl'     => array(
				'label'    => 'Indotama Domestik Lestari',
				'website'  => 'http://www.idlcargo.co.id',
				'services' => array(
					'iSDS' => 'SAME DAY SERVICES',
					'iONS' => 'OVERNIGHT SERVICES',
					'iSCF' => 'SPECIAL FLEET',
					'iREG' => 'REGULAR',
					'iCon' => 'EKONOMIS',
				),
				'account'  => array(
					'pro',
				),
			),
			'rex'     => array(
				'label'    => 'Royal Express Indonesia',
				'website'  => 'https://www.rex.co.id',
				'services' => array(
					'EXP'    => 'EXPRESS',
					'REX-1'  => 'REX-1',
					'REX-5'  => 'REX-5',
					'REX-10' => 'REX-10',
				),
				'account'  => array(
					'pro',
				),
			),
			'indah'   => array(
				'label'    => 'Indah Logistic',
				'website'  => 'http://www.indahonline.com',
				'services' => array(
					'DARAT' => 'Cargo Darat',
					'UDARA' => 'Cargo Udara',
				),
				'account'  => array(
					'pro',
				),
			),
		),
		'international' => array(
			'jne'      => array(
				'label'    => 'JNE',
				'website'  => 'http://www.jne.co.id',
				'services' => array(
					'INTL' => 'INTL',
				),
				'account'  => array(
					'pro',
				),
			),
			'pos'      => array(
				'label'    => 'POS Indonesia',
				'website'  => 'http://www.posindonesia.co.id',
				'services' => array(
					'R LN'              => 'R LN',
					'EMS BARANG'        => 'EMS BARANG',
					'PAKETPOS CEPAT LN' => 'PAKETPOS CEPAT LN',
					'PAKETPOS BIASA LN' => 'PAKETPOS BIASA LN',
				),
				'account'  => array(
					'basic',
					'pro',
				),
			),
			'slis'     => array(
				'label'    => 'Solusi Ekspres',
				'website'  => 'http://www.solusiekspres.com',
				'services' => array(
					'PACKAGE'       => 'PACKAGE',
					'COSMETIC/FOOD' => 'COSMETIC/FOOD',
				),
				'account'  => array(
					'pro',
				),
			),
			'expedito' => array(
				'label'    => 'Expedito',
				'website'  => 'http://www.expedito.co.id',
				'services' => array(
					'CityLink'         => 'CityLink',
					'DPEX'             => 'DPEX',
					'ARAMEX Indonesia' => 'ARAMEX Indonesia',
					'DHL  JKT'         => 'DHL  JKT',
					'DHL Singapore'    => 'DHL Singapore',
					'SF EXPRESS'       => 'SF EXPRESS',
					'SkyNet Worldwide' => 'SkyNet Worldwide',
					'TNT | Fedex'      => 'TNT | Fedex',
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
		$results  = array();
		$account  = $this->get_account( $this->get_option( 'account_type' ) );
		$endpoint = empty( $destination['country'] ) ? 'cost' : 'internationalCost';

		if ( $courier && ! $account['multiple_coriers'] ) {
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
}
