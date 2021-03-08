<?php
/**
 * The file that defines the Woongkir_Courier class
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
 * The Woongkir_Courier class.
 *
 * @since      1.2.12
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
abstract class Woongkir_Courier {

	/**
	 * Courier priority
	 *
	 * @since 1.2.12
	 *
	 * @var int
	 */
	public $priority = 0;

	/**
	 * Courier Code
	 *
	 * @since 1.2.12
	 *
	 * @var string
	 */
	public $code = '';

	/**
	 * API Response ID
	 *
	 * @since 1.2.12
	 *
	 * @var string
	 */
	public $response_code = '';

	/**
	 * Courier Label
	 *
	 * @since 1.2.12
	 *
	 * @var string
	 */
	public $label = '';

	/**
	 * Courier Website
	 *
	 * @since 1.2.12
	 *
	 * @var string
	 */
	public $website = '';

	/**
	 * Get courier priority
	 *
	 * @since 1.2.12
	 *
	 * @return int
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Get courier code
	 *
	 * @since 1.2.12
	 *
	 * @return string
	 */
	public function get_code() {
		return $this->code;
	}

	/**
	 * Get courier API response ID
	 *
	 * @since 1.2.12
	 *
	 * @return string
	 */
	public function get_response_code() {
		return $this->response_code ? $this->response_code : $this->code;
	}

	/**
	 * Get courier label
	 *
	 * @since 1.2.12
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Get courier website
	 *
	 * @since 1.2.12
	 *
	 * @return string
	 */
	public function get_website() {
		return $this->website;
	}

	/**
	 * Get courier services
	 *
	 * @since 1.3.0
	 *
	 * @param string $zone Shipping zone.
	 *
	 * @return array
	 */
	public function get_services( $zone ) {
		if ( 'domestic' === $zone ) {
			return $this->get_services_domestic();
		}

		if ( 'international' === $zone ) {
			return $this->get_services_international();
		}

		return array();
	}

	/**
	 * Get default courier services
	 *
	 * @since 1.3.0
	 *
	 * @param string $zone Shipping zone.
	 *
	 * @return array
	 */
	public function get_services_default( $zone ) {
		if ( 'domestic' === $zone ) {
			return $this->get_services_domestic_default();
		}

		if ( 'international' === $zone ) {
			return $this->get_services_international_default();
		}

		return array();
	}

	/**
	 * Get courier services for domestic shipping
	 *
	 * @since 1.2.12
	 *
	 * @return array
	 */
	public function get_services_domestic() {
		$default_data = $this->get_services_domestic_default();

		if ( ! $default_data ) {
			return array();
		}

		$data_key   = $this->get_services_data_key( 'domestic' );
		$saved_data = get_option( $data_key );

		if ( false === $saved_data ) {
			update_option( $data_key, $default_data, true );
		} else {
			return $saved_data;
		}

		return $default_data;
	}

	/**
	 * Get default courier services for domestic shipping
	 *
	 * @since 1.3.0
	 *
	 * @return array
	 */
	public function get_services_domestic_default() {
		return array();
	}

	/**
	 * Get courier services for international shipping
	 *
	 * @since 1.2.12
	 *
	 * @return array
	 */
	public function get_services_international() {
		$default_data = $this->get_services_international_default();

		if ( ! $default_data ) {
			return array();
		}

		$data_key   = $this->get_services_data_key( 'international' );
		$saved_data = get_option( $data_key );

		if ( false === $saved_data ) {
			update_option( $data_key, $default_data, true );
		} else {
			return $saved_data;
		}

		return $default_data;
	}

	/**
	 * Get default courier services for international shipping
	 *
	 * @since 1.3.0
	 *
	 * @return array
	 */
	public function get_services_international_default() {
		return array();
	}

	/**
	 * Add new service
	 *
	 * @since 1.3.0
	 *
	 * @param string $id Service ID.
	 * @param string $label Service label.
	 * @param string $zone Shipping zone.
	 *
	 * @return bool
	 */
	public function add_service( $id, $label, $zone ) {
		$services = $this->get_services( $zone );

		if ( isset( $services[ $id ] ) ) {
			return;
		}

		if ( ! $label ) {
			$label = $id;
		}

		$services[ $id ] = $label;

		return update_option( $this->get_services_data_key( $zone ), $services, true );
	}

	/**
	 * Update service
	 *
	 * @since 1.3.0
	 *
	 * @param string $id Service ID.
	 * @param string $label Service label.
	 * @param string $zone Shipping zone.
	 *
	 * @return bool
	 */
	public function update_service( $id, $label, $zone ) {
		$services = $this->get_services( $zone );

		if ( ! isset( $services[ $id ] ) ) {
			return;
		}

		$services[ $id ] = $label;

		return update_option( $this->get_services_data_key( $zone ), $services, true );
	}

	/**
	 * Delete service
	 *
	 * @since 1.3.0
	 *
	 * @param string $id Service ID.
	 * @param string $zone Shipping zone.
	 *
	 * @return bool
	 */
	public function delete_service( $id, $zone ) {
		$services = $this->get_services( $zone );

		if ( ! isset( $services[ $id ] ) ) {
			return;
		}

		unset( $services[ $id ] );

		return update_option( $this->get_services_data_key( $zone ), $services, true );
	}

	/**
	 * Get courier services data option name
	 *
	 * @since 1.3.0
	 *
	 * @param string $zone Shipping zone.
	 *
	 * @return string
	 */
	public function get_services_data_key( $zone ) {
		return sprintf( 'woongkir_couriers_data_%s_%s', $zone, $this->get_code() );
	}

	/**
	 * Get courier account for domestic shipping
	 *
	 * @since 1.2.12
	 *
	 * @return array
	 */
	public function get_account_domestic() {
		return array();
	}

	/**
	 * Get courier account for international shipping
	 *
	 * @since 1.2.12
	 *
	 * @return array
	 */
	public function get_account_international() {
		return array();
	}

	/**
	 * Get courier account for international shipping
	 *
	 * @since 1.2.12
	 *
	 * @param {string} $zone Zone ID.
	 *
	 * @return array
	 */
	public function get_account_by_zone( $zone ) {
		if ( 'domestic' === $zone ) {
			return $this->get_account_domestic();
		}

		if ( 'international' === $zone ) {
			return $this->get_account_international();
		}

		return array();
	}

	/**
	 * Populate properties as array
	 *
	 * @since 1.2.12
	 *
	 * @param string $zone Couriers zone: domestic, international, all.
	 *
	 * @return array
	 */
	public function to_array( $zone = 'all' ) {
		if ( 'domestic' === $zone ) {
			return array(
				'priority'      => $this->get_priority(),
				'code'          => $this->get_code(),
				'response_code' => $this->get_response_code(),
				'label'         => $this->get_label(),
				'website'       => $this->get_website(),
				'services'      => $this->get_services_domestic(),
				'account'       => $this->get_account_domestic(),
			);
		}

		if ( 'international' === $zone ) {
			return array(
				'priority'      => $this->get_priority(),
				'code'          => $this->get_code(),
				'response_code' => $this->get_response_code(),
				'label'         => $this->get_label(),
				'website'       => $this->get_website(),
				'services'      => $this->get_services_international(),
				'account'       => $this->get_account_international(),
			);
		}

		return array(
			'priority'               => $this->get_priority(),
			'code'                   => $this->get_code(),
			'response_code'          => $this->get_response_code(),
			'label'                  => $this->get_label(),
			'website'                => $this->get_website(),
			'services_domestic'      => $this->get_services_domestic(),
			'services_international' => $this->get_services_international(),
			'account_domestic'       => $this->get_account_domestic(),
			'account_international'  => $this->get_account_international(),
		);
	}
}
