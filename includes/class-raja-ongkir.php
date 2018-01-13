<?php
/**
 * The file that defines the Raja_Ongkir class
 *
 * @link       https://github.com/sofyansitorus
 * @since      1.0.0
 *
 * @package    Woongkir
 * @subpackage Woongkir/includes
 */

/**
 * The Raja_Ongkir API class.
 *
 * This is used to make request to RajaOngkir.com API server.
 *
 * @since      1.0.0
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Raja_Ongkir {

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
			'label'       => 'Starter',
			'api_url'     => 'http://api.rajaongkir.com/starter',
			'subdistrict' => false,
			'multiple'    => false,
		),
		'basic'   => array(
			'label'       => 'Basic',
			'api_url'     => 'http://api.rajaongkir.com/basic',
			'subdistrict' => false,
			'multiple'    => true,
		),
		'pro'     => array(
			'label'       => 'Pro',
			'api_url'     => 'http://pro.rajaongkir.com/api',
			'subdistrict' => true,
			'multiple'    => true,
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
			'pos'  => array(
				'label'    => 'POS Indonesia (POS)',
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
			'tiki' => array(
				'label'    => 'Citra Van Titipan Kilat (TIKI)',
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
			'jne'  => array(
				'label'    => 'Jalur Nugraha Ekakurir (JNE)',
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
			'rpx'  => array(
				'label'    => 'RPX Holding (RPX)',
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
			'pcp'  => array(
				'label'    => 'Priority Cargo Package (PCP)',
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
			'star'  => array(
				'label'    => 'Star Cargo (STAR)',
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
			'sicepat'  => array(
				'label'    => 'SiCepat Express (SICEPAT)',
				'services' => array(
					'REG',
					'BEST',
					'Priority',
				),
				'account'  => array(
					'pro',
				),
			),
			'jet'  => array(
				'label'    => 'JET Express (JET)',
				'services' => array(
					'CRG',
					'PRI',
					'REG',
				),
				'account'  => array(
					'pro',
				),
			),
			'jnt'  => array(
				'label'    => 'J&T Express (J&T)',
				'services' => array(
					'EZ',
				),
				'account'  => array(
					'pro',
				),
			),
			'pandu'  => array(
				'label'    => 'Pandu Logistics (PANDU)',
				'services' => array(
					'REG',
				),
				'account'  => array(
					'pro',
				),
			),
		),
		'international' => array(
			'pos' => array(
				'label'    => 'POS Indonesia (POS)',
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
			'tiki' => array(
				'label'    => 'Citra Van Titipan Kilat (TIKI)',
				'services' => array(
					'Dokumen',
					'Paket',
					'Dokumen / Paket',
				),
				'account'  => array(
					'basic',
					'pro',
				),
			),
			'jne' => array(
				'label'    => 'Jalur Nugraha Ekakurir (JNE)',
				'services' => array(
					'INTL',
				),
				'account'  => array(
					'pro',
				),
			),
			'slis' => array(
				'label'    => 'Solusi Ekspres (SLIS)',
				'services' => array(
					'PACKAGE',
					'COSMETIC/FOOD',
				),
				'account'  => array(
					'pro',
				),
			),
			'expedito' => array(
				'label'    => 'Expedito (EXPEDITO)',
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
		$account  = $this->get_account( $this->get_option( 'account_type' ) );
		$endpoint = empty( $destination['country'] ) ? 'cost' : 'internationalCost';

		switch ( $endpoint ) {
			case 'internationalCost':
				$params = array(
					'destination' => $destination['country'],
					'origin'      => $origin['city'],
					'courier'     => ( $account['multiple'] ) ? implode( ':', $courier ) : $courier[0],
				);
				break;

			default:
				$params = array(
					'destination'     => ( $account['subdistrict'] && ! empty( $destination['subdistrict'] ) ) ? $destination['subdistrict'] : $destination['city'],
					'destinationType' => ( $account['subdistrict'] && ! empty( $destination['subdistrict'] ) ) ? 'subdistrict' : 'city',
					'origin'          => ( $account['subdistrict'] && ! empty( $origin['subdistrict'] ) ) ? $origin['subdistrict'] : $origin['city'],
					'originType'      => ( $account['subdistrict'] && ! empty( $origin['subdistrict'] ) ) ? 'subdistrict' : 'city',
					'courier'         => ( $account['multiple'] ) ? implode( ':', $courier ) : $courier[0],
				);
				break;
		}

		$params = array_merge( $params, $dimension_weight );
		return $this->remote_post( $endpoint, $params );
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
		return $this;
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
				'headers' => array(
					'key' => $this->get_option( 'api_key' ),
				),
			)
		);

		$response = wp_remote_request( $this->url( $endpoint ), $args );

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
			'headers' => array(
				'key'          => $this->get_option( 'api_key' ),
				'content-type' => 'application/x-www-form-urlencoded',
			),
			'body'    => $body,
		);

		$response = wp_remote_post( $this->url( $endpoint ), $args );

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
			'headers' => array(
				'key' => $this->get_option( 'api_key' ),
			),
		);

		$url = $this->url( $endpoint );

		if ( $query_url ) {
			$url = add_query_arg( $query_url, $url );
		}

		$response = wp_remote_get( $url, $args );

		return $this->validate_api_response( $response );

	}

	/**
	 * Validate API request response.
	 *
	 * @since 1.0.0
	 * @param mixed $response API request response data.
	 */
	private function validate_api_response( $response ) {

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return new WP_Error( 'api_response_empty', __( 'API response is empty.', 'woongkir' ) );
		}

		$data = json_decode( $body );

		if ( json_last_error() !== JSON_ERROR_NONE || ! $data ) {
			return new WP_Error( 'api_response_invalid', __( 'API response is invalid.', 'woongkir' ) );
		}

		if ( isset( $data->rajaongkir->status ) && 200 !== $data->rajaongkir->status->code ) {
			return new WP_Error( 'api_response_error_' . $data->rajaongkir->status->code, $data->rajaongkir->status->description );
		}

		if ( isset( $data->rajaongkir->results ) ) {
			return $data->rajaongkir->results;
		}

		if ( isset( $data->rajaongkir->result ) ) {
			return $data->rajaongkir->result;
		}

		return new WP_Error( 'unknown_error', __( 'Unknown error', 'woongkir' ) );
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
	private function url( $endpoint ) {
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
