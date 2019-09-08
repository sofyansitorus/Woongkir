<?php
/**
 * The file that defines the Woongkir_Courier class
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
 * The Woongkir_Courier class.
 *
 * @since      ??
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
abstract class Woongkir_Courier {

	/**
	 * Courier ID
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * API Response ID
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $response_id = '';

	/**
	 * Courier Name
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * Courier Website
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $website = '';

	/**
	 * Get courier ID
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get courier API response ID
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function get_response_id() {
		return $this->response_id ? $this->response_id : $this->id;
	}

	/**
	 * Get courier name
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get courier website
	 *
	 * @since ??
	 *
	 * @return string
	 */
	public function get_website() {
		return $this->website;
	}

	/**
	 * Get courier services for domestic shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_services_domestic() {
		return array();
	}

	/**
	 * Get courier services for international shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_services_international() {
		return array();
	}

	/**
	 * Get courier account for domestic shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_account_domestic() {
		return array();
	}

	/**
	 * Get courier account for international shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_account_international() {
		return array();
	}
}
