<?php
/**
 * The file that defines the Woongkir_Courier_EXPEDITO class
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
 * The Woongkir_Courier_EXPEDITO class.
 *
 * @since      ??
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Courier_EXPEDITO extends Woongkir_Courier {

	/**
	 * Courier ID
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $id = 'expedito';

	/**
	 * Courier Name
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $name = 'Expedito';

	/**
	 * Courier Website
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $website = 'http://www.expedito.co.id';

	/**
	 * Get courier services for international shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_services_international() {
		return array(
			'CityLink'         => 'CityLink',
			'DPEX'             => 'DPEX',
			'ARAMEX Indonesia' => 'ARAMEX Indonesia',
			'DHL  JKT'         => 'DHL  JKT',
			'DHL Singapore'    => 'DHL Singapore',
			'SF EXPRESS'       => 'SF EXPRESS',
			'SkyNet Worldwide' => 'SkyNet Worldwide',
			'TNT | Fedex'      => 'TNT | Fedex',
		);
	}

	/**
	 * Get courier account for international shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_account_international() {
		return array(
			'pro',
		);
	}
}
