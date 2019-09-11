<?php
/**
 * The file that defines the Woongkir_Account_Basic class
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
 * The Woongkir_Account_Basic class.
 *
 * @since      ??
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Account_Basic extends Woongkir_Account {

	/**
	 * Account priority
	 *
	 * @since ??
	 *
	 * @var int
	 */
	public $priority = 2;

	/**
	 * Account type
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $type = 'basic';

	/**
	 * Account label
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $label = 'Basic';

	/**
	 * Account API URL
	 *
	 * @since ??
	 *
	 * @var string
	 */
	public $api_url = 'http://api.rajaongkir.com/basic';

	/**
	 * Account features
	 *
	 * @since ??
	 *
	 * @var array
	 */
	public $features = array(
		'subdistrict'       => false,
		'multiple_couriers' => true,
		'volumetric'        => false,
		'weight_over_30kg'  => false,
		'dedicated_server'  => false,
	);
}
