<?php
/**
 * The file that defines the Woongkir_Courier_EXPEDITO class
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
 * The Woongkir_Courier_EXPEDITO class.
 *
 * @since      1.2.12
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Courier_EXPEDITO extends Woongkir_Courier {

	/**
	 * Courier Code
	 *
	 * @since 1.2.12
	 *
	 * @var string
	 */
	public $code = 'expedito';

	/**
	 * Courier Label
	 *
	 * @since 1.2.12
	 *
	 * @var string
	 */
	public $label = 'Expedito';

	/**
	 * Courier Website
	 *
	 * @since 1.2.12
	 *
	 * @var string
	 */
	public $website = 'http://www.expedito.co.id';

	/**
	 * Get courier services for international shipping
	 *
	 * @since 1.2.12
	 *
	 * @return array
	 */
	public function get_services_international_default() {
		return array(
			'ARAMEX Indonesia'             => 'ARAMEX Indonesia',
			'CityLink Express'             => 'CityLink Express',
			'CityLink'                     => 'CityLink',
			'DHL  JKT'                     => 'DHL JKT',
			'DHL Singapore'                => 'DHL Singapore',
			'DPEX'                         => 'DPEX',
			'Moon Forwarder'               => 'Moon Forwarder',
			'NetworkCourierSG'             => 'NetworkCourierSG',
			'SF EXPRESS'                   => 'SF EXPRESS',
			'SkyNet Worldwide'             => 'SkyNet Worldwide',
			'SkySaver by Skynet Worldwide' => 'SkySaver by Skynet Worldwide',
			'TNT | Fedex'                  => 'TNT | Fedex',
		);
	}

	/**
	 * Get courier account for international shipping
	 *
	 * @since 1.2.12
	 *
	 * @return array
	 */
	public function get_account_international() {
		return array(
			'pro',
		);
	}
}
