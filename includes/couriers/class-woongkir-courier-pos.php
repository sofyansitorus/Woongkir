<?php
/**
 * The file that defines the Woongkir_Courier_POS class
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
 * The Woongkir_Courier_POS class.
 *
 * @since      ??
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Courier_POS extends Woongkir_Courier {

	/**
	 * Courier ID
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $id = 'pos';

	/**
	 * Courier Name
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $name = 'POS Indonesia';

	/**
	 * Courier Website
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $website = 'http://www.posindonesia.co.id';

	/**
	 * Get courier services for domestic shipping
	 *
	 * @since ??
	 *
	 * @return array
	 */
	public function get_services_domestic() {
		return array(
			'Surat Kilat Khusus'       => 'Surat Kilat Khusus',
			'Paketpos Biasa'           => 'Paketpos Biasa',
			'Paket Kilat Khusus'       => 'Paket Kilat Khusus',
			'Express Samedat Barang'   => 'Express Samedat Barang',
			'Express Samedat Dokumen'  => 'Express Samedat Dokumen',
			'Express Next Day Barang'  => 'Express Next Day Barang',
			'Express Next Day Dokumen' => 'Express Next Day Dokumen',
			'Paketpos Dangerous Goods' => 'Paketpos Dangerous Goods',
			'Paketpos Valuable Goods'  => 'Paketpos Valuable Goods',
			'Kargopos Ritel Train'     => 'Kargopos Ritel Train',
			'Kargopos Ritel Udara Dn'  => 'Kargopos Ritel Udara Dn',
			'Paket Jumbo Ekonomi'      => 'Paket Jumbo Ekonomi',
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
			'R LN'              => 'R LN',
			'EMS BARANG'        => 'EMS BARANG',
			'PAKETPOS CEPAT LN' => 'PAKETPOS CEPAT LN',
			'PAKETPOS BIASA LN' => 'PAKETPOS BIASA LN',
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
			'basic',
			'pro',
		);
	}
}
