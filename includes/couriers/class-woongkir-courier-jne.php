<?php
/**
 * The file that defines the Woongkir_Courier_JNE class
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
 * The Woongkir_Courier_JNE class.
 *
 * @since      ??
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Courier_JNE extends Woongkir_Courier {

	/**
	 * Courier ID
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $id = 'jne';

	/**
	 * Courier Name
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $name = 'JNE';

	/**
	 * Courier Website
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $website = 'http://www.jne.co.id';

	/**
	 * Get courier services for domestic shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_services_domestic() {
		return array(
			'CTC'    => 'City Courier',
			'CTCYES' => 'City Courier YES',
			'OKE'    => 'Ongkos Kirim Ekonomis',
			'REG'    => 'Layanan Reguler',
			'YES'    => 'Yakin Esok Sampai',
		);
	}

	/**
	 * Get courier services for international shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_services_international() {
		return array(
			'INTL' => 'INTL',
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
			'starter',
			'basic',
			'pro',
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
