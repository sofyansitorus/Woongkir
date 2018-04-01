<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/sofyansitorus
 * @since      1.0.0
 *
 * @package    Woongkir
 * @subpackage Woongkir/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir extends WC_Shipping_Method {


	/**
	 * Raja_Ongkir API Class Object
	 *
	 * @since 1.0.0
	 * @var object
	 */
	private $api;

	/**
	 * Posted values of settings fields.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $posted_field_values;

	/**
	 * Constructor for your shipping class
	 *
	 * @since 1.0.0
	 * @param int $instance_id ID of settings instance.
	 * @return void
	 */
	public function __construct( $instance_id = 0 ) {
		$this->instance_id        = absint( $instance_id );
		$this->id                 = WOONGKIR_METHOD_ID;
		$this->method_title       = WOONGKIR_METHOD_TITLE;
		$this->title              = WOONGKIR_METHOD_TITLE;
		$this->method_description = __( 'Shipping rates calculator using Indonesia shipping couriers JNE, TIKI, POS, PCP, RPX, STAR, SICEPAT, JET, PANDU, J&T, SLIS, EXPEDITO for Domestic and International shipment.', 'woongkir' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		add_filter( 'woocommerce_default_address_fields', array( $this, 'default_address_fields_priority' ) );
		add_filter( 'woocommerce_billing_fields', array( $this, 'billing_fields_priority' ), 10, 2 );
		add_filter( 'woocommerce_shipping_fields', array( $this, 'shipping_fields_priority' ), 10, 2 );

		// Check if this shipping method is availbale for current order.
		add_filter( 'woocommerce_shipping_' . $this->id . '_is_available', array( $this, 'check_is_available' ), 10, 2 );

		// Set the base weight for cart contents.
		add_filter( 'woocommerce_cart_contents_weight', array( $this, 'set_cart_contents_base_weight' ), 10 );

		$this->api = new Raja_Ongkir();

		$this->init();
	}

	/**
	 * Initialize user set variables.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->init_form_fields(); // This is part of the settings API. Loads instance form fields .
		$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

		// Define user set variables.
		foreach ( $this->instance_form_fields as $field_id => $field ) {
			$type = array_key_exists( 'type', $field ) ? $field['type'] : false;
			if ( ! $type || in_array( $type, array( 'title' ), true ) ) {
				continue;
			}

			$default = array_key_exists( 'default', $field ) ? $field['default'] : null;
			$option  = $this->get_option( $field_id, $default );

			$this->{$field_id} = $option;
		}

		$this->api->set_option( 'api_key', $this->api_key );
		$this->api->set_option( 'account_type', $this->account_type );
	}

	/**
	 * Init form fields.
	 *
	 * @since 1.0.0
	 */
	public function init_form_fields() {
		if ( 'ID' !== WC()->countries->get_base_country() ) {
			$settings                   = array(
				'error' => array(
					'title'       => __( 'Error', 'woongkir' ),
					'type'        => 'title',
					'description' => __( 'This plugin only work for Store Address based in Indonesia.', 'woongkir' ),
				),
			);
			$this->instance_form_fields = $settings;
			return;
		}
		$settings = array(
			'origin_province'    => array(
				'title' => __( 'Shipping Origin Province', 'woongkir' ),
				'type'  => 'origin',
			),
			'origin_city'        => array(
				'title' => __( 'Shipping Origin City', 'woongkir' ),
				'type'  => 'origin',
			),
			'origin_subdistrict' => array(
				'title' => __( 'Shipping Origin Subdistrict', 'woongkir' ),
				'type'  => 'origin',
			),
			'tax_status'         => array(
				'title'   => __( 'Tax Status', 'woongkir' ),
				'type'    => 'select',
				'default' => 'none',
				'options' => array(
					'taxable' => __( 'Taxable', 'woongkir' ),
					'none'    => _x( 'None', 'Tax status', 'woongkir' ),
				),
			),
			'show_eta'           => array(
				'title'       => __( 'Show ETA', 'woongkir' ),
				'label'       => __( 'Yes', 'woongkir' ),
				'type'        => 'checkbox',
				'description' => __( 'Show estimated time of arrival during checkout.', 'woongkir' ),
			),
			'base_weight'        => array(
				'title'             => __( 'Base Cart Contents Weight (gram)', 'woongkir' ),
				'type'              => 'number',
				'description'       => __( 'The base cart contents weight will be calculated. If the value is blank or zero, the couriers list will not displayed when the actual cart contents weight is empty.', 'woongkir' ),
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '100',
				),
			),
			'api_key'            => array(
				'title'       => __( 'RajaOngkir API Key', 'woongkir' ),
				'type'        => 'text',
				'placeholder' => '',
				'description' => __( '<a href="http://www.rajaongkir.com" target="_blank">Click here</a> to get RajaOngkir.com API Key. It is free.', 'woongkir' ),
				'default'     => '',
			),
			'account_type'       => array(
				'title'             => __( 'RajaOngkir Account Type', 'woongkir' ),
				'type'              => 'select',
				'default'           => 'starter',
				'options'           => array(),
				'class'             => 'woongkir-account-type',
				'custom_attributes' => array(
					'data-accounts' => wp_json_encode( $this->api->get_account() ),
					'data-couriers' => wp_json_encode( $this->api->get_courier() ),
				),
			),
			'domestic'           => array(
				'title' => __( 'Domestic Shipping', 'woongkir' ),
				'type'  => 'couriers_list',
			),
			'international'      => array(
				'title' => __( 'International Shipping', 'woongkir' ),
				'type'  => 'couriers_list',
			),
		);

		$couriers = $this->api->get_courier();

		foreach ( $this->api->get_account() as $account_type => $data ) {
			$zone_data = array();
			$label     = $data['label'];
			foreach ( $couriers as $zone_id => $courier ) {
				$zone_data[ $zone_id ] = 0;
				foreach ( $courier as $courier_id => $courier_data ) {
					if ( in_array( $account_type, $courier_data['account'], true ) ) {
						$zone_data[ $zone_id ]++;
					}
				}
			}
			$infos = array();

			foreach ( $zone_data as $zone_id => $count ) {
				$infos[] = $zone_id . ': ' . $count;
			}

			if ( $data['multiple'] ) {
				$infos[] = 'multiple';
			}

			if ( $data['subdistrict'] ) {
				$infos[] = 'subdistrict';
			}

			$label .= ' (' . implode( ', ', $infos ) . ')';
			$settings['account_type']['options'][ $account_type ] = $label;
		}

		$this->instance_form_fields = $settings;
	}

	/**
	 * Generate origin settings field.
	 *
	 * @since 1.0.0
	 * @param string $key Settings field key.
	 * @param array  $data Settings field data.
	 */
	public function generate_origin_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );

		$defaults = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
			'options'           => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start(); ?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php echo esc_html( $this->get_tooltip_html( $data ) ); ?>
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<select class="select wc-enhanced-select woongkir-<?php echo esc_attr( str_replace( '_', '-', $key ) ); ?>-select <?php echo esc_attr( $data['class'] ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo esc_html( $this->get_custom_attribute_html( $data ) ); ?>>
					</select>
					<?php echo esc_html( $this->get_description_html( $data ) ); ?>
				</fieldset>
				<input type="hidden" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" class="woongkir-<?php echo esc_attr( str_replace( '_', '-', $key ) ); ?>" value="<?php echo esc_attr( $this->get_option( $key ) ); ?>">
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * Generate couriers list table.
	 *
	 * @since  1.0.0
	 * @param  mixed $key Field key.
	 * @param  mixed $data Field dat.
	 * @return string
	 */
	public function generate_couriers_list_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title' => '',
			'class' => '',
		);

		$data = wp_parse_args( $data, $defaults );

		$couriers = $this->api->get_courier( $key );

		$selected = $this->{$key};

		ob_start();
		?>
		</table>
		<div id="woongkir-couriers-list-<?php echo esc_attr( $key ); ?>" class="woongkir-couriers-list <?php echo esc_attr( $key ); ?>" data-id="<?php echo esc_attr( $key ); ?>">
			<h3 class="wc-settings-sub-title <?php echo esc_attr( $data['class'] ); ?>" id="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></h3>
			<?php if ( ! empty( $data['description'] ) ) : ?>
				<p><?php echo wp_kses_post( $data['description'] ); ?></p>
			<?php endif; ?>
			<table class="form-table" width="100%">
				<tr>
				<?php
				$i = 0;
				foreach ( $couriers as $courier_id => $courier ) :
					if ( empty( $courier['services'] ) ) :
						continue;
					endif;
					if ( $i && 0 === $i % 5 ) {
						echo '</tr><tr>';
					}
				?>
				<td style="vertical-align:top !important; width: 20%;" id="woongkir-courier-box-<?php echo esc_attr( $key ); ?>-<?php echo esc_attr( $courier_id ); ?>" class="woongkir-courier-box <?php echo esc_attr( $courier_id ); ?>" data-id="<?php echo esc_attr( $courier_id ); ?>">
					<table class="form-table" width="100%" style="border:1px solid #f8f8f8;">
						<thead style="background-color:#f1f1f1;">
							<tr>
								<td>
									<?php if ( file_exists( WOONGKIR_PATH . 'assets/img/' . $courier_id . '.png' ) ) : ?>
									<img src="<?php echo esc_attr( WOONGKIR_URL ); ?>assets/img/<?php echo esc_attr( $courier_id ); ?>.png" style="display: block;margin:0 auto; max-width: 96px; height: auto;">
									<?php endif; ?>
									<input type="checkbox" id="<?php echo esc_attr( $field_key ); ?>_<?php echo esc_attr( $courier_id ); ?>_toggle" class="woongkir-service bulk" <?php checked( ( isset( $selected[ $courier_id ] ) && count( $selected[ $courier_id ] ) ? 1 : 0 ), 1 ); ?>>
									<label for="<?php echo esc_attr( $field_key ); ?>_<?php echo esc_attr( $courier_id ); ?>_toggle"><?php echo wp_kses_post( $courier['label'] ); ?></label>
								</td>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $courier['services'] as $index => $service ) : ?>
							<tr>
								<td>
									<input type="checkbox" class="woongkir-service single" id="<?php echo esc_attr( $field_key ); ?>_<?php echo esc_attr( $courier_id ); ?>_<?php echo esc_attr( $index ); ?>" name="<?php echo esc_attr( $field_key ); ?>[]" value="<?php echo esc_attr( $courier_id ); ?>_<?php echo esc_attr( $service ); ?>" <?php checked( ( isset( $selected[ $courier_id ] ) && in_array( $service, $selected[ $courier_id ], true ) ? $service : false ), $service ); ?>>
									<label for="<?php echo esc_attr( $field_key ); ?>_<?php echo esc_attr( $courier_id ); ?>_<?php echo esc_attr( $index ); ?>"><?php echo wp_kses_post( $service ); ?></label>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</td>
				<?php
					$i++;
				endforeach;
				?>
				</tr>
			</table>
		</div>
		<table class="form-table">
		<?php
		return ob_get_clean();
	}

	/**
	 * Validate api_key settings field.
	 *
	 * @since 1.0.0
	 * @param string $key Input field key.
	 * @param string $value Input field currenet value.
	 * @throws Exception Error message.
	 */
	public function validate_api_key_field( $key, $value ) {
		if ( empty( $value ) ) {
			throw new Exception( __( 'API Key is required.', 'woongkir' ) );
		}

		$account_type = $this->validate_account_type_field( 'account_type', $this->posted_field_value( 'account_type' ) );
		if ( $account_type ) {
			$this->api->set_option( 'api_key', $value );
			$this->api->set_option( 'account_type', $account_type );
			$account_valid = $this->api->validate_account();
			if ( is_wp_error( $account_valid ) ) {
				throw new Exception( $account_valid->get_error_message(), 1 );
			}
			if ( ! $account_valid ) {
				throw new Exception( 'API Key or Account type is invalid', 1 );
			}
		}
		return $value;
	}

	/**
	 * Validate account_type settings field.
	 *
	 * @since 1.0.0
	 * @param string $key Input field key.
	 * @param string $value Input field currenet value.
	 * @throws Exception If field value is not valid.
	 */
	public function validate_account_type_field( $key, $value ) {
		if ( empty( $value ) ) {
			throw new Exception( __( 'Account type field is required.', 'woongkir' ) );
		}
		if ( ! $this->api->get_account( $value ) ) {
			throw new Exception( __( 'Account type field is invalid.', 'woongkir' ) );
		}
		return $value;
	}

	/**
	 * Validate settings field type origin.
	 *
	 * @since 1.0.0
	 * @param string $key Input field key.
	 * @param string $value Input field currenet value.
	 * @throws Exception If field value is not valid.
	 * @return string
	 */
	public function validate_origin_field( $key, $value ) {
		if ( empty( $value ) ) {
			// Translators: Shipping origin location type.
			throw new Exception( sprintf( __( 'Shipping origin %s field is required.', 'woongkir' ), str_replace( 'origin_', '', $key ) ) );
		}
		return $value;
	}

	/**
	 * Validate settings field type couriers_list.
	 *
	 * @since 1.0.0
	 * @param  string $key Settings field key.
	 * @param  string $value Posted field value.
	 * @throws Exception If the field value is invalid.
	 * @return array
	 */
	public function validate_couriers_list_field( $key, $value ) {

		if ( is_string( $value ) ) {
			$value = array_map( 'trim', explode( ',', $value ) );
		}

		// Format the value as associative array courier => services.
		if ( $value && is_array( $value ) ) {
			$format_value = array();
			foreach ( $value as $val ) {
				$parts = explode( '_', $val );
				if ( count( $parts ) === 2 ) {
					$format_value[ $parts[0] ][] = $parts[1];
				}
			}
			$value = $format_value;
		}

		if ( $value ) {
			$account_type = $this->posted_field_value( 'account_type' );

			$account = $this->api->get_account( $account_type );
			if ( ! $account ) {
				throw new Exception( __( 'Account type field is invalid.', 'woongkir' ) );
			}

			$couriers    = $this->api->get_courier( $key );
			$not_allowed = array();
			foreach ( $value as $courier_id => $courier ) {
				if ( ! in_array( $account_type, $couriers[ $courier_id ]['account'], true ) ) {
					array_push( $not_allowed, strtoupper( $courier_id ) );
				}
			}

			$field = $this->instance_form_fields[ $key ];

			if ( ! empty( $not_allowed ) ) {
				// Translators: %1$s Shipping zone name, %2$s Account label, %3$s Couriers name.
				throw new Exception( sprintf( __( '%1$s Shipping: Account type %2$s is not allowed to select courier %3$s.', 'woongkir' ), $field['title'], $account['label'], implode( ', ', $not_allowed ) ) );
			}

			if ( ! $account['multiple'] && count( $value ) > 1 ) {
				// Translators: %1$s Shipping zone name, %2$s Account label.
				throw new Exception( sprintf( __( '%1$s Shipping: Account type %2$s is not allowed to select multiple couriers.', 'woongkir' ), $field['title'], $account['label'] ) );
			}
		}

		return $value;
	}

	/**
	 * Check if this method available
	 *
	 * @since 1.0.0
	 * @param boolean $available Current status is available.
	 * @param array   $package Current order package data.
	 * @return bool
	 */
	public function check_is_available( $available, $package ) {
		if ( ! $available || empty( $package['contents'] ) || empty( $package['destination'] ) ) {
			return false;
		}

		if ( 'ID' !== WC()->countries->get_base_country() ) {
			return false;
		}

		return $available;
	}

	/**
	 * Calculate the shipping cost.
	 *
	 * @since 1.0.0
	 * @param array $package Order package data.
	 */
	public function calculate_shipping( $package = array() ) {
		$params = array();

		$params['origin'] = $this->get_origin_info();
		if ( empty( $params['origin'] ) ) {
			return;
		}

		$params['destination'] = $this->get_destination_info( $package['destination'] );
		if ( ! $params['destination'] || ! array_filter( $params['destination'] ) ) {
			return;
		}

		$params['dimension_weight'] = $this->get_dimension_weight( $package['contents'] );
		if ( ! $params['dimension_weight'] || ! array_filter( $params['dimension_weight'] ) ) {
			return;
		}

		$params['courier'] = $params['destination']['country'] ? array_keys( $this->international ) : array_keys( $this->domestic );
		if ( empty( $params['courier'] ) ) {
			return;
		}

		$cache_key = preg_replace( '/[^\da-z]/i', '_', wp_json_encode( $params ) );

		$couriers = wp_cache_get( $cache_key, $this->id );
		if ( false === $couriers ) {
			$couriers = $this->api->get_cost( $params['destination'], $params['origin'], $params['dimension_weight'], $params['courier'] );
			wp_cache_set( $cache_key, $couriers, $this->id, 3600 ); // Store response data for 1 hour.
		}
		if ( ! $couriers || ! is_array( $couriers ) || is_wp_error( $couriers ) ) {
			if ( is_wp_error( $couriers ) ) {
				$this->show_debug( $couriers->get_error_message() );
			}
			return;
		}

		$zone = empty( $params['destination']['country'] ) ? 'domestic' : 'international';

		$currency_exchange = false;

		foreach ( $couriers as $courier ) {
			if ( empty( $courier->costs ) ) {
				continue;
			}
			$courier_code = strtolower( str_replace( '&', 'n', $courier->code ) );
			$selected     = isset( $this->{$zone}[ $courier_code ] ) ? $this->{$zone}[ $courier_code ] : array();
			foreach ( $courier->costs as $service ) {
				if ( ! in_array( $service->service, $selected, true ) || empty( $service->cost ) ) {
					continue;
				}

				$currency_code = isset( $service->currency ) ? $service->currency : 'IDR';

				if ( 'IDR' !== $currency_code && empty( $currency_exchange ) ) {
					$currency_exchange = $this->api->get_currency();
				}

				if ( 'IDR' !== $currency_code && ! isset( $currency_exchange->value ) ) {
					continue;
				}

				$rate = is_array( $service->cost ) ? $service->cost[0]->value : $service->cost;
				$cost = ( 'IDR' === $currency_code ) ? $rate : ( $currency_exchange->value * $rate );

				$rate_id    = $this->get_rate_id( $courier_code . ':' . $service->service );
				$rate_label = sprintf( '%s - %s', strtoupper( $courier->code ), $service->service );

				if ( 'yes' === $this->show_eta ) {
					$eta = isset( $service->etd ) ? $service->etd : false;
					if ( ! $eta && is_array( $service->cost ) && isset( $service->cost[0]->etd ) ) {
						$eta = $service->cost[0]->etd;
					}
					if ( $eta ) {
						$eta = strtoupper( $eta );
						if ( '1-1' === $eta ) {
							$eta = '1';
						}
						if ( false === strpos( $eta, 'HARI' ) && false === strpos( $eta, 'JAM' ) ) {
							$eta = ( '1' === $eta ) ? $eta . ' {day}' : $eta . ' {days}';
						}
						if ( false !== strpos( $eta, 'HARI' ) ) {
							$eta = ( str_replace( ' HARI', '', $eta ) === '1' ) ? str_replace( 'HARI', '{day}', $eta ) : str_replace( 'HARI', '{days}', $eta );
						}
						if ( false !== strpos( $eta, 'JAM' ) ) {
							$eta = ( str_replace( ' JAM', '', $eta ) === '1' ) ? str_replace( 'JAM', '{hour}', $eta ) : str_replace( 'JAM', '{hours}', $eta );
						}
						$eta = str_replace( array( '{hour}', '{hours}', '{day}', '{days}' ), array( __( 'Hour', 'woongkir' ), __( 'Hours', 'woongkir' ), __( 'Day', 'woongkir' ), __( 'Days', 'woongkir' ) ), $eta );

						$rate_label = sprintf( '%s (%s)', $rate_label, $eta );
					}
				}

				$this->add_rate(
					array(
						'id'        => $rate_id,
						'label'     => $rate_label,
						'cost'      => $cost,
						'meta_data' => $couriers,
					)
				);
			}
		}
	}

	/**
	 * Get shipping origin info
	 *
	 * @since 1.0.0
	 * @return array
	 */
	private function get_origin_info() {
		if ( empty( $this->origin_province ) || empty( $this->origin_city ) || empty( $this->origin_subdistrict ) ) {
			return false;
		}

		return array(
			'province'    => absint( $this->origin_province ),
			'city'        => absint( $this->origin_city ),
			'subdistrict' => absint( $this->origin_subdistrict ),
		);
	}

	/**
	 * Get shipping destination info
	 *
	 * @since 1.0.0
	 * @param array $data Shipping destination data in associative array format: address, city, state, postcode, country.
	 * @return array
	 */
	private function get_destination_info( $data = array() ) {
		// Default destination data.
		$destination = array(
			'country'     => 0,
			'province'    => 0,
			'city'        => 0,
			'subdistrict' => 0,
		);

		// Get country ID data.
		if ( ! empty( $data['country'] ) && 'ID' !== $data['country'] ) {
			$country = $this->get_json_data(
				'country',
				array(
					'country_code' => $data['country'],
				)
			);
			if ( $country && isset( $country['country_id'] ) ) {
				$destination['country'] = absint( $country['country_id'] );
			}
		}

		// Check if international shipping or data not complete.
		if ( ! empty( $destination['country'] ) || empty( $data['state'] ) || empty( $data['city'] ) ) {
			return $destination;
		}

		// Get province ID data.
		$province = $this->get_json_data(
			'province',
			array(
				'code' => $data['state'],
			)
		);

		// Check if province ID found.
		if ( empty( $province ) || ! isset( $province['province_id'] ) ) {
			return $destination;
		}

		$destination['province'] = absint( $province['province_id'] );

		// Get city ID data.
		$city_parts = explode( ' ', $data['city'] );
		$city_type  = $city_parts[0];
		$city_name  = str_replace( $city_type . ' ', '', $data['city'] );

		$city = $this->get_json_data(
			'city',
			array(
				'type'        => $city_type,
				'city_name'   => $city_name,
				'province_id' => $destination['province'],
			)
		);

		// Check if city ID found.
		if ( empty( $city ) || ! isset( $city['city_id'] ) ) {
			return $destination;
		}

		$destination['city'] = absint( $city['city_id'] );

		// Get subdistrict ID data.
		if ( ! empty( $data['address_2'] ) ) {
			$subdistrict = $this->get_json_data(
				'subdistrict',
				array(
					'subdistrict_name' => $data['address_2'],
					'city_id'          => $destination['city'],
					'province_id'      => $destination['province'],
				)
			);

			if ( $subdistrict && isset( $subdistrict['subdistrict_id'] ) ) {
				$destination['subdistrict'] = $subdistrict['subdistrict_id'];
			}
		}

		return $destination;
	}

	/**
	 * Get package dimension and weight data.
	 *
	 * @since 1.0.0
	 * @param array $contents Current order package contents.
	 * @return array
	 */
	private function get_dimension_weight( $contents ) {
		$data = array(
			'width'  => 0,
			'length' => 0,
			'height' => 0,
			'weight' => 0,
		);

		$length = array();
		$width  = array();
		$height = array();
		$weight = array();

		foreach ( $contents as $item ) {
			// Validate cart item quantity value.
			$item_quantity = absint( $item['quantity'] );
			if ( ! $item_quantity ) {
				continue;
			}

			// Validate cart item width value.
			$item_width = is_numeric( $item['data']->get_width() ) ? $item['data']->get_width() : 0;
			array_push( $width, $item_width * 1 );

			// Validate cart item length value.
			$item_length = is_numeric( $item['data']->get_length() ) ? $item['data']->get_length() : 0;
			array_push( $length, $item_length * 1 );

			// Validate cart item height value.
			$item_height = is_numeric( $item['data']->get_height() ) ? $item['data']->get_height() : 0;
			array_push( $height, $item_height * $item_quantity );

			// Validate cart item weight value.
			$item_weight = is_numeric( $item['data']->get_weight() ) ? $item['data']->get_weight() : 0;
			array_push( $weight, $item_weight * $item_quantity );

		}

		$data['width']  = wc_get_dimension( max( $width ), 'cm' );
		$data['length'] = wc_get_dimension( max( $length ), 'cm' );
		$data['height'] = wc_get_dimension( array_sum( $height ), 'cm' );
		$data['weight'] = wc_get_weight( array_sum( $weight ), 'g' );

		// Set the package weight to based on base_weight setting value.
		if ( absint( $this->base_weight ) && $data['weight'] < absint( $this->base_weight ) ) {
			$data['weight'] = absint( $this->base_weight );
		}

		/**
		 * Developers can modify the dimension and weight data via filter hooks.
		 *
		 * @since 1.0.1
		 *
		 * This example shows how you can modify the shipping destination data via custom function:
		 *
		 *      add_action( 'woocommerce_woongkir_shipping_dimension_weight', 'modify_shipping_dimension_weight', 10, 2 );
		 *
		 *      function modify_shipping_dimension_weight( $data, $method ) {
		 *          return array(
		 *              'width' => 0,
		 *              'length' => 0,
		 *              'height' => 0,
		 *              'weight' => 0,
		 *           );
		 *      }
		 */
		return apply_filters( 'woocommerce_' . $this->id . '_shipping_dimension_weight', $data, $this );
	}

	/**
	 * Set the base weight for cart contents.
	 *
	 * @since 1.1.4
	 * @param int $weight Current cart contents weight.
	 * @return int
	 */
	public function set_cart_contents_base_weight( $weight ) {
		if ( absint( $this->base_weight ) && $weight < absint( $this->base_weight ) ) {
			return wc_get_weight( absint( $this->base_weight ), get_option( 'woocommerce_weight_unit', 'kg' ), 'g' );
		}
		return $weight;
	}

	/**
	 * Get json file data.
	 *
	 * @since 1.0.0
	 * @param array $file_name File name for the json data.
	 * @param array $search Serach keyword data.
	 * @throws  Exception If WordPress Filesystem Abstraction classes is not available.
	 * @return array
	 */
	public function get_json_data( $file_name, $search = array() ) {
		global $wp_filesystem;

		$file_url  = WOONGKIR_URL . 'data/' . $file_name . '.json';
		$file_path = WOONGKIR_PATH . 'data/' . $file_name . '.json';

		try {
			require_once ABSPATH . 'wp-admin/includes/file.php';

			if ( is_null( $wp_filesystem ) ) {
				WP_Filesystem();
			}

			if ( ! $wp_filesystem instanceof WP_Filesystem_Base || ( is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) ) {
				throw new Exception( 'WordPress Filesystem Abstraction classes is not available', 1 );
			}

			if ( ! $wp_filesystem->exists( $file_path ) ) {
				throw new Exception( 'JSON file is not exists or unreadable', 1 );
			}

			$json = $wp_filesystem->get_contents( $file_path );
		} catch ( Exception $e ) {
			// Get JSON data by HTTP if the WP_Filesystem API procedure failed.
			$json = wp_remote_retrieve_body( wp_remote_get( esc_url_raw( $file_url ) ) );
		}

		if ( ! $json ) {
			return false;
		}

		$data = json_decode( $json, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! $data ) {
			return false;
		}

		// Search JSON data by associative array. Return the match row or false if not found.
		if ( $search ) {
			foreach ( $data as $row ) {
				if ( array_intersect_assoc( $search, $row ) === $search ) {
					return $row;
				}
			}
			return false;
		}

		return $data;
	}

	/**
	 * Modify default ddress fields priority.
	 *
	 * @param array $fields Address fields used by default.
	 */
	public function default_address_fields_priority( $fields ) {
		if ( isset( $fields['state'] ) ) {
			$fields['state']['priority'] = 41;
		}

		if ( isset( $fields['city'] ) ) {
			$fields['city']['priority'] = 42;
		}

		return $fields;
	}

	/**
	 * Modify billing fields priority.
	 *
	 * @since 1.0.0
	 * @param array  $fields Address fields used by default.
	 * @param string $country Selected country.
	 */
	public function billing_fields_priority( $fields, $country ) {
		if ( 'ID' !== $country ) {
			return $fields;
		}

		$need_sort = false;

		if ( isset( $fields['billing_state'] ) ) {
			$fields['billing_state']['priority'] = 41;
			$need_sort                           = true;
		}

		if ( isset( $fields['billing_city'] ) ) {
			$fields['billing_city']['priority'] = 42;
			$need_sort                          = true;
		}

		if ( ! $need_sort ) {
			return $fields;
		}

		$priority_offset = count( $fields ) * 10;
		$billing_fields  = array();

		foreach ( $fields as $key => $value ) {
			$billing_fields[ $key ] = isset( $value['priority'] ) ? $value['priority'] : $priority_offset;
			$priority_offset       += 10;
		}

		// Sort fields by priority.
		asort( $billing_fields );

		$billing_field_keys = array_keys( $billing_fields );

		foreach ( $billing_field_keys as $billing_field_key ) {
			$billing_fields[ $billing_field_key ] = $fields[ $billing_field_key ];
		}

		return $billing_fields;
	}

	/**
	 * Modify shipping fields priority.
	 *
	 * @since 1.0.0
	 * @param array  $fields Address fields used by default.
	 * @param string $country Selected country.
	 */
	public function shipping_fields_priority( $fields, $country ) {
		if ( 'ID' !== $country ) {
			return $fields;
		}

		$need_sort = false;

		if ( isset( $fields['shipping_state'] ) ) {
			$fields['shipping_state']['priority'] = 41;

			$need_sort = true;
		}

		if ( isset( $fields['shipping_city'] ) ) {
			$fields['shipping_city']['priority'] = 42;

			$need_sort = true;
		}

		if ( ! $need_sort ) {
			return $fields;
		}

		$priority_offset = count( $fields ) * 10;
		$shipping_fields = array();

		foreach ( $fields as $key => $value ) {
			$shipping_fields[ $key ] = isset( $value['priority'] ) ? $value['priority'] : $priority_offset;
			$priority_offset        += 10;
		}

		// Sort fields by priority.
		asort( $shipping_fields );

		$shipping_field_keys = array_keys( $shipping_fields );

		foreach ( $shipping_field_keys as $shipping_field_key ) {
			$shipping_fields[ $shipping_field_key ] = $fields[ $shipping_field_key ];
		}

		return $shipping_fields;
	}

	/**
	 * Get posted settings field value.
	 *
	 * @since 1.0.0
	 * @param string $key Settings field key.
	 * @param string $default Default value if the settings field is not exists.
	 * @return mixed
	 */
	private function posted_field_value( $key, $default = null ) {
		if ( is_null( $this->posted_field_values ) ) {
			$this->posted_field_values = $this->get_post_data();
		}

		$field_key = $this->get_field_key( $key );

		return array_key_exists( $field_key, $this->posted_field_values ) ? $this->posted_field_values[ $field_key ] : $default;
	}

	/**
	 * Show debug info
	 *
	 * @since 1.0.0
	 * @param string $message The text to display in the notice.
	 * @return void
	 */
	private function show_debug( $message ) {
		$debug_mode = 'yes' === get_option( 'woocommerce_shipping_debug_mode', 'no' );

		if ( $debug_mode && ! defined( 'WOOCOMMERCE_CHECKOUT' ) && ! defined( 'WC_DOING_AJAX' ) && ! wc_has_notice( $message ) ) {
			wc_add_notice( $message );
		}
	}
}
