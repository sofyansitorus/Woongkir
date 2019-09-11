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
