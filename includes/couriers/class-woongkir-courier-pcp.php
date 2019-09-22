<?php
/**
 * The file that defines the Woongkir_Courier_PCP class
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
 * The Woongkir_Courier_PCP class.
 *
 * @since      ??
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Courier_PCP extends Woongkir_Courier {

	/**
	 * Courier Code
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $code = 'pcp';

	/**
	 * Courier Label
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $label = 'PCP';

	/**
	 * Courier Website
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $website = 'http://www.pcpexpress.com';

	/**
	 * Get courier services for domestic shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_services_domestic() {
		return array(
			'TREX' => 'Titipan Regular Express',
			'JET'  => 'Jaminan Esok Tiba',
			'HIT'  => 'Hari Ini Tiba',
			'EXIS' => 'Express Ekonomi',
			'GODA' => 'Kargo Darat',
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
			'basic',
			'pro',
		);
	}
}
