<?php
/**
 * The file that defines the Woongkir_Courier_JTL class
 *
 * @link       https://github.com/sofyansitorus
 * @since      1.3.8
 *
 * @package    Woongkir
 * @subpackage Woongkir/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Woongkir_Courier_JTL class.
 *
 * @since      1.3.8
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Courier_JTL extends Woongkir_Courier {

	/**
	 * Courier Code
	 *
	 * @since 1.3.8
	 *
	 * @var string
	 */
	public $code = 'jtl';

	/**
	 * Courier Label
	 *
	 * @since 1.3.8
	 *
	 * @var string
	 */
	public $label = 'JTL Express';

	/**
	 * Courier Website
	 *
	 * @since 1.3.8
	 *
	 * @var string
	 */
	public $website = 'http://www.jtlexpress.co.id';

	/**
	 * Get courier services for domestic shipping
	 *
	 * @since 1.3.8
	 *
	 * @return array
	 */
	public function get_services_domestic_default() {
		return array(
			'EXPRESS ECONOMY'  => 'Express Economy',
			'EXPRESS STANDART' => 'Express Standart',
			'JTL EXTRA'        => 'JTL Extra',
			'JTL HAPPY'        => 'JTL Happy',
			'JTLOG'            => 'JTLog',
			'NON EXPRESS'      => 'Non Express',
		);
	}

	/**
	 * Get courier account for domestic shipping
	 *
	 * @since 1.3.8
	 *
	 * @return array
	 */
	public function get_account_domestic() {
		return array(
			'pro',
		);
	}
}
