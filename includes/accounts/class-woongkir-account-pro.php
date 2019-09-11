<?php
/**
 * The file that defines the Woongkir_Account_Pro class
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
 * The Woongkir_Account_Pro class.
 *
 * @since      ??
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Account_Pro extends Woongkir_Account {

	/**
	 * Account priority
	 *
	 * @since ??
	 *
	 * @var int
	 */
	public $priority = 3;

	/**
	 * Account type
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $type = 'pro';

	/**
	 * Account label
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $label = 'Pro';

	/**
	 * Account API URL
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $api_url = 'http://pro.rajaongkir.com/api';

	/**
	 * Account features
	 *
	 * @since ??
	 *
	 * @var array
	 */
	public $features = array(
		'subdistrict'      => true,
		'multiple_coriers' => true,
		'volumetric'       => true,
		'weight_over_30kg' => true,
		'dedicated_server' => true,
	);
}
