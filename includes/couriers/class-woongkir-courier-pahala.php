<?php
/**
 * The file that defines the Woongkir_Courier_PAHALA class
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
 * The Woongkir_Courier_PAHALA class.
 *
 * @since      ??
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Courier_PAHALA extends Woongkir_Courier {

	/**
	 * Courier Code
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $code = 'pahala';

	/**
	 * Courier Label
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $label = 'Pahala Express';

	/**
	 * Courier Website
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $website = 'http://www.pahalaexpress.co.id';

	/**
	 * Get courier services for domestic shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_services_domestic() {
		return array(
			'EXPRESS'     => 'Express Service',
			'ONS'         => 'One Night Service',
			'SDS'         => 'Same Day Service',
			'SEPEDA'      => 'Paket Sepeda',
			'MOTOR SPORT' => 'Paket Motor Sport',
			'MOTOR BESAR' => 'Paket Motor Besar',
			'MOTOR BEBEK' => 'Paket Motor Bebek',
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
