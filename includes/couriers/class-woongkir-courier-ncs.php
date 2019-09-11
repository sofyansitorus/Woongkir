<?php
/**
 * The file that defines the Woongkir_Courier_NCS class
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
 * The Woongkir_Courier_NCS class.
 *
 * @since      ??
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Courier_NCS extends Woongkir_Courier {

	/**
	 * Courier Code
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $code = 'ncs';

	/**
	 * Courier Label
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $label = 'Nusantara Card Semesta';

	/**
	 * Courier Website
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $website = 'http://www.ptncs.com';

	/**
	 * Get courier services for domestic shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_services_domestic() {
		return array(
			'NRS' => 'REGULAR SERVICE',
			'ONS' => 'OVERNIGHT SERVICE',
			'SDS' => 'SAME DAY SERVICE',
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
