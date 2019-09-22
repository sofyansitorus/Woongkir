<?php
/**
 * The file that defines the Woongkir_Account class
 *
 * @link       https://github.com/sofyansitorus
 * @since      ??
 *
 * @package    Woongkir
 * @subpackage Woongkir/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Woongkir_Account class.
 *
 * @since      ??
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
abstract class Woongkir_Account {

	/**
	 * Account priority
	 *
	 * @since ??
	 *
	 * @var int
	 */
	public $priority = 0;

	/**
	 * Account type
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $type = '';

	/**
	 * Account label
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $label = '';

	/**
	 * Account API URL
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $api_url = '';

	/**
	 * Account features
	 *
	 * @since ??
	 *
	 * @var array
	 */
	public $features = array(
		'subdistrict'       => false,
		'multiple_couriers' => false,
		'volumetric'        => false,
		'weight_over_30kg'  => false,
		'dedicated_server'  => false,
	);

	/**
	 * Allowed API Request parameters
	 *
	 * @since ??
	 *
	 * @var array
	 */
	protected $api_request_params = array(
		'origin'          => array(
			'type'          => 'string',
			'validate_type' => 'is_string',
		),
		'originType'      => array(
			'type'          => 'string',
			'validate_type' => 'is_string',
		),
		'destination'     => array(
			'type'          => 'string',
			'validate_type' => 'is_string',
		),
		'destinationType' => array(
			'type'          => 'string',
			'validate_type' => 'is_string',
		),
		'weight'          => array(
			'type'          => 'numeric',
			'validate_type' => 'is_numeric',
		),
		'courier'         => array(
			'type'          => 'array',
			'validate_type' => 'is_array',
		),
		'length'          => array(
			'type'          => 'numeric',
			'validate_type' => 'is_numeric',
		),
		'width'           => array(
			'type'          => 'numeric',
			'validate_type' => 'is_numeric',
		),
		'height'          => array(
			'type'          => 'numeric',
			'validate_type' => 'is_numeric',
		),
		'diameter'        => array(
			'type'          => 'numeric',
			'validate_type' => 'is_numeric',
		),
	);

	/**
	 * Required API Request parameters
	 *
	 * @since ??
	 *
	 * @var array
	 */
	protected $api_request_params_requireds = array();

	/**
	 * Optionals API Request parameters
	 *
	 * @since ??
	 *
	 * @var array
	 */
	protected $api_request_params_optionals = array();

	/**
	 * Get account priority
	 *
	 * @since ??
	 *
	 * @return int
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Get account type
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get account label
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Get account API URL
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function get_api_url() {
		return $this->api_url;
	}

	/**
	 * Get account features
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_features() {
		return $this->features;
	}

	/**
	 * Check is feature enabled
	 *
	 * @since ??
	 *
	 * @param string $feature_key Feature key.
	 *
	 * @return bool
	 */
	public function feature_enable( $feature_key ) {
		return isset( $this->features[ $feature_key ] ) ? $this->features[ $feature_key ] : false;
	}

	/**
	 * Parse API request parameters.
	 *
	 * @since ??
	 *
	 * @param array $params API request parameters to parse.
	 *
	 * @throws Exception Error message.
	 *
	 * @return (array|WP_Error)
	 */
	public function api_request_parser( $params = array() ) {
		try {
			$parsed = array();

			foreach ( $this->api_request_params as $allowed_key => $allowed ) {
				if ( ! in_array( $allowed_key, $this->api_request_params_requireds, true ) && ! in_array( $allowed_key, $this->api_request_params_optionals, true ) ) {
					continue;
				}

				$value     = isset( $params[ $allowed_key ] ) ? $params[ $allowed_key ] : null;
				$has_value = is_numeric( $value ) || is_string( $value ) || is_integer( $value ) ? strlen( $value ) : $value;

				if ( in_array( $allowed_key, $this->api_request_params_requireds, true ) && ! $has_value ) {
					// translators: %s API request parameter key.
					throw new Exception( sprintf( __( 'Required API request parameter is empty: %s.', 'woongkir' ), $allowed_key ) );
				}

				if ( ! is_null( $value ) && isset( $allowed['validate_type'] ) && is_callable( $allowed['validate_type'] ) && ! call_user_func( $allowed['validate_type'], $value ) ) {
					// translators: %1$s API request parameter key, %2$s Expected data type, %1$s Passed data type.
					throw new Exception( sprintf( __( 'Invalid API request parameter data type: %1$s. Passed %2$s instead %3$s', 'woongkir' ), $allowed_key, gettype( $value ), $allowed['type'] ) );
				}

				$value_modifier_callback = array( $this, 'api_request_param_' . $allowed_key . '_value_modifier' );
				if ( is_callable( $value_modifier_callback ) ) {
					$value = call_user_func( $value_modifier_callback, $value );
				}

				if ( is_wp_error( $value ) ) {
					throw new Exception( $value->get_error_message() );
				}

				if ( ! is_null( $value ) ) {
					$parsed[ $allowed_key ] = $value;
				}
			}

			if ( empty( $parsed ) ) {
				throw new Exception( __( 'API request parameters is empty.', 'woongkir' ) );
			}

			return $parsed;
		} catch ( Exception $e ) {
			return new WP_Error( 'invalid_request_params', $e->getMessage() );
		}
	}

	/**
	 * API Request parameter value modifier and validator: weight
	 *
	 * @since ??
	 *
	 * @param string $value weight parameter value.
	 *
	 * @return (float|int|double)
	 */
	protected function api_request_param_weight_value_modifier( $value ) {
		if ( ! $this->feature_enable( 'weight_over_30kg' ) && $value > 30000 ) {
			return new WP_Error( 'invalid_api_request_param_weight_value', __( 'Account type not support weight over 30 kg.', 'woongkir' ) );
		}

		return $value;
	}

	/**
	 * API Request parameter value modifier and validator: originType
	 *
	 * @since ??
	 *
	 * @param string $value originType parameter value.
	 *
	 * @return string
	 */
	protected function api_request_param_originType_value_modifier( $value ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		if ( ! $this->feature_enable( 'subdistrict' ) && 'subdistrict' === $value ) {
			return new WP_Error( 'invalid_api_request_param_originType_value', __( 'Account type not support subdistrict origin.', 'woongkir' ) );
		}

		return $value;
	}

	/**
	 * API Request parameter value modifier and validator: destinationType
	 *
	 * @since ??
	 *
	 * @param string $value destinationType parameter value.
	 *
	 * @return string
	 */
	protected function api_request_param_destinationType_value_modifier( $value ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		if ( ! $this->feature_enable( 'subdistrict' ) && 'subdistrict' === $value ) {
			return new WP_Error( 'invalid_api_request_param_destinationType_value', __( 'Account type not support subdistrict destination.', 'woongkir' ) );
		}

		return $value;
	}

	/**
	 * API Request parameter value modifier and validator: courier
	 *
	 * @since ??
	 *
	 * @param array $value courier parameter value.
	 *
	 * @return string
	 */
	protected function api_request_param_courier_value_modifier( $value ) {
		if ( ! $this->feature_enable( 'multiple_couriers' ) && count( $value ) > 1 ) {
			return new WP_Error( 'invalid_api_request_param_courier_value', __( 'Account type not support multiple couriers.', 'woongkir' ) );
		}

		return implode( ':', $value );
	}

	/**
	 * Populate properties as array
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function to_array() {
		return array_merge(
			array(
				'priority' => $this->get_priority(),
				'type'     => $this->get_type(),
				'label'    => $this->get_label(),
				'api_url'  => $this->get_api_url(),
			),
			$this->get_features()
		);
	}
}
