<?php
/**
 * The file that defines the Woongkir_Courier_IDE class
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
 * The Woongkir_Courier_IDE class.
 *
 * @since      1.3.8
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Courier_IDE extends Woongkir_Courier {

	/**
	 * Courier Code
	 *
	 * @since 1.3.8
	 *
	 * @var string
	 */
	public $code = 'ide';

	/**
	 * Courier Label
	 *
	 * @since 1.3.8
	 *
	 * @var string
	 */
	public $label = 'IDexpress Service Solution';

	/**
	 * Courier Website
	 *
	 * @since 1.3.8
	 *
	 * @var string
	 */
	public $website = 'http://idexpress.com';

	/**
	 * Get courier services for domestic shipping
	 *
	 * @since 1.3.8
	 *
	 * @return array
	 */
	public function get_services_domestic_default() {
		return array(
			'STD' => 'Standard Service',
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
