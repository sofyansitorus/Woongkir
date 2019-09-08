<?php
/**
 * The file that defines the Woongkir_Courier_SAP class
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
 * The Woongkir_Courier_SAP class.
 *
 * @since      ??
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Courier_SAP extends Woongkir_Courier {

	/**
	 * Courier ID
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $id = 'sap';

	/**
	 * Courier Name
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $name = 'SAP Express';

	/**
	 * Courier Website
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $website = 'http://sap-express.id';

	/**
	 * Get courier services for domestic shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_services_domestic() {
		return array(
			'REG' => 'Regular Service',
			'SDS' => 'Same Day Service',
			'ODS' => 'One Day Service',
		);
	}

	/**
	 * Get courier account for domestic shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_account_domestic() {
		return array(
			'pro',
		);
	}
}
