<?php
/**
 * The file that defines the Woongkir_Courier_REX class
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
 * The Woongkir_Courier_REX class.
 *
 * @since      ??
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Courier_REX extends Woongkir_Courier {

	/**
	 * Courier Code
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $code = 'rex';

	/**
	 * Courier Label
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $label = 'Royal Express Indonesia';

	/**
	 * Courier Website
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $website = 'https://www.rex.co.id';

	/**
	 * Get courier services for domestic shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_services_domestic() {
		return array(
			'EXP'    => 'EXPRESS',
			'REX-1'  => 'REX-1',
			'REX-5'  => 'REX-5',
			'REX-10' => 'REX-10',
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
