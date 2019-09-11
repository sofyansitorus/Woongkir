<?php
/**
 * The file that defines the Woongkir_Courier_DSE class
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
 * The Woongkir_Courier_DSE class.
 *
 * @since      ??
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Courier_DSE extends Woongkir_Courier {

	/**
	 * Courier Code
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $code = 'dse';

	/**
	 * Courier Label
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $label = '21 Express';

	/**
	 * Courier Website
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $website = 'http://21express.co.id';

	/**
	 * Get courier services for domestic shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_services_domestic() {
		return array(
			'ECO' => 'Regular Service',
			'ONS' => 'Over Night Service',
			'SDS' => 'Same Day Service',
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
