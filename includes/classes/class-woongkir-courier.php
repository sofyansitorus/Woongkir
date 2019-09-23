<?php
/**
 * The file that defines the Woongkir_Courier class
 *
 * @link       https://github.com/sofyansitorus
 * @since      1.2.12
 *
 * @package    Woongkir
 * @subpackage Woongkir/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Woongkir_Courier class.
 *
 * @since      1.2.12
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
abstract class Woongkir_Courier {

	/**
	 * Courier priority
	 *
	 * @since 1.2.12
	 *
	 * @var int
	 */
	public $priority = 0;

	/**
	 * Courier Code
	 *
	 * @since 1.2.12
	 *
	 * @var string
	 */
	public $code = '';

	/**
	 * API Response ID
	 *
	 * @since 1.2.12
	 *
	 * @var string
	 */
	public $response_code = '';

	/**
	 * Courier Label
	 *
	 * @since 1.2.12
	 *
	 * @var string
	 */
	public $label = '';

	/**
	 * Courier Website
	 *
	 * @since 1.2.12
	 *
	 * @var string
	 */
	public $website = '';

	/**
	 * Get courier priority
	 *
	 * @since 1.2.12
	 *
	 * @return int
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Get courier code
	 *
	 * @since 1.2.12
	 *
	 * @return string
	 */
	public function get_code() {
		return $this->code;
	}

	/**
	 * Get courier API response ID
	 *
	 * @since 1.2.12
	 *
	 * @return string
	 */
	public function get_response_code() {
		return $this->response_code ? $this->response_code : $this->code;
	}

	/**
	 * Get courier label
	 *
	 * @since 1.2.12
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Get courier website
	 *
	 * @since 1.2.12
	 *
	 * @return string
	 */
	public function get_website() {
		return $this->website;
	}

	/**
	 * Get courier services for domestic shipping
	 *
	 * @since 1.2.12
	 *
	 * @return array
	 */
	public function get_services_domestic() {
		return array();
	}

	/**
	 * Get courier services for international shipping
	 *
	 * @since 1.2.12
	 *
	 * @return array
	 */
	public function get_services_international() {
		return array();
	}

	/**
	 * Get courier account for domestic shipping
	 *
	 * @since 1.2.12
	 *
	 * @return array
	 */
	public function get_account_domestic() {
		return array();
	}

	/**
	 * Get courier account for international shipping
	 *
	 * @since 1.2.12
	 *
	 * @return array
	 */
	public function get_account_international() {
		return array();
	}

	/**
	 * Populate properties as array
	 *
	 * @since 1.2.12
	 *
	 * @param string $zone Couriers zone: domestic, international, all.
	 *
	 * @return array
	 */
	public function to_array( $zone = 'all' ) {
		if ( 'domestic' === $zone ) {
			return array(
				'priority'      => $this->get_priority(),
				'code'          => $this->get_code(),
				'response_code' => $this->get_response_code(),
				'label'         => $this->get_label(),
				'website'       => $this->get_website(),
				'services'      => $this->get_services_domestic(),
				'account'       => $this->get_account_domestic(),
			);
		}

		if ( 'international' === $zone ) {
			return array(
				'priority'      => $this->get_priority(),
				'code'          => $this->get_code(),
				'response_code' => $this->get_response_code(),
				'label'         => $this->get_label(),
				'website'       => $this->get_website(),
				'services'      => $this->get_services_international(),
				'account'       => $this->get_account_international(),
			);
		}

		return array(
			'priority'               => $this->get_priority(),
			'code'                   => $this->get_code(),
			'response_code'          => $this->get_response_code(),
			'label'                  => $this->get_label(),
			'website'                => $this->get_website(),
			'services_domestic'      => $this->get_services_domestic(),
			'services_international' => $this->get_services_international(),
			'account_domestic'       => $this->get_account_domestic(),
			'account_international'  => $this->get_account_international(),
		);
	}
}
