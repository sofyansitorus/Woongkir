<?php
/**
 * The file that defines the Woongkir_Courier_POS class
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
 * The Woongkir_Courier_POS class.
 *
 * @since      1.2.12
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Courier_POS extends Woongkir_Courier {

	/**
	 * Courier Code
	 *
	 * @since 1.2.12
	 *
	 * @var string
	 */
	public $code = 'pos';

	/**
	 * Courier Label
	 *
	 * @since 1.2.12
	 *
	 * @var string
	 */
	public $label = 'POS Indonesia';

	/**
	 * Courier Website
	 *
	 * @since 1.2.12
	 *
	 * @var string
	 */
	public $website = 'http://www.posindonesia.co.id';

	/**
	 * Get courier services for domestic shipping
	 *
	 * @since 1.2.12
	 *
	 * @return array
	 */
	public function get_services_domestic_default() {
		return array(
			'Express Next Day Barang'  => 'Express Next Day Barang',
			'Express Next Day Dokumen' => 'Express Next Day Dokumen',
			'Express Sameday Barang'   => 'Express Sameday Barang',
			'Express Sameday Dokumen'  => 'Express Sameday Dokumen',
			'Kargopos Ritel Train'     => 'Kargopos Ritel Train',
			'Kargopos Ritel Udara Dn'  => 'Kargopos Ritel Udara Dn',
			'Paket Jumbo Ekonomi'      => 'Paket Jumbo Ekonomi',
			'Paket Kilat Khusus'       => 'Paket Kilat Khusus',
			'Paketpos Biasa'           => 'Paketpos Biasa',
			'Paketpos Dangerous Goods' => 'Paketpos Dangerous Goods',
			'Paketpos Valuable Goods'  => 'Paketpos Valuable Goods',
			'Surat Kilat Khusus'       => 'Surat Kilat Khusus',
		);
	}

	/**
	 * Get courier services for international shipping
	 *
	 * @since 1.2.12
	 *
	 * @return array
	 */
	public function get_services_international_default() {
		return array(
			'EMS BARANG'        => 'EMS BARANG',
			'ePacket LP APP'    => 'ePacket LP APP',
			'PAKETPOS BIASA LN' => 'PAKETPOS BIASA LN',
			'PAKETPOS CEPAT LN' => 'PAKETPOS CEPAT LN',
			'POS EKSPOR'        => 'POS EKSPOR',
			'R LN'              => 'R LN',
		);
	}

	/**
	 * Get courier account for domestic shipping
	 *
	 * @since 1.2.12
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
	 * @since 1.2.12
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
