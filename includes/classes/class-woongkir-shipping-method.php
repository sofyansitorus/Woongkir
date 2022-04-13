<?php
/**
 * Woongkir Shipping Method Class.
 *
 * @link       https://github.com/sofyansitorus
 * @since      1.0.0
 *
 * @package    Woongkir
 * @subpackage Woongkir/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woongkir Shipping Method Class.
 *
 * @since      1.0.0
 * @package    Woongkir
 * @subpackage Woongkir/includes
 * @author     Sofyan Sitorus <sofyansitorus@gmail.com>
 */
class Woongkir_Shipping_Method extends WC_Shipping_Method {

	/**
	 * Woongkir_API API Class Object
	 *
	 * @since 1.0.0
	 * @var Woongkir_API
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
	 * Supported features.
	 *
	 * @since 1.0.0
	 * @var string[]
	 */
	public $supports = array(
		'shipping-zones',
		'instance-settings',
		'instance-settings-modal',
	);

	/**
	 * Options restore on setting update failure.
	 *
	 * @since 1.3
	 *
	 * @var array
	 */
	private $restore_instance_settings = array();

	/**
	 * Constructor for your shipping class
	 *
	 * @since 1.0.0
	 * @param int $instance_id ID of settings instance.
	 * @return void
	 */
	public function __construct( $instance_id = 0 ) {
		$this->api                = new Woongkir_API();
		$this->instance_id        = absint( $instance_id );
		$this->id                 = WOONGKIR_METHOD_ID;
		$this->method_title       = woongkir_get_plugin_data( 'Name' );
		$this->title              = woongkir_get_plugin_data( 'Name' );
		$this->method_description = woongkir_get_plugin_data( 'Description' );

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
		$this->register_hooks(); // Register actions and filters hooks.		// Define user set variables.

		// Define user set variables.
		foreach ( $this->instance_form_fields as $field_id => $field ) {
			$option = $this->get_option( $field_id );

			if ( $option && in_array( $field_id, array( 'api_key', 'account_type' ), true ) ) {
				$this->api->set_option( $field_id, $option );
			}

			$restore = isset( $field['restore'] ) ? $field['restore'] : false;

			if ( $restore ) {
				$this->restore_instance_settings[ $field_id ] = $option;
			}
		}
	}

	/**
	 * Init form fields.
	 *
	 * @since 1.0.0
	 */
	public function init_form_fields() {
		if ( 'ID' !== WC()->countries->get_base_country() ) {
			$this->instance_form_fields = array(
				'error' => array(
					'title'       => __( 'Error', 'woongkir' ),
					'type'        => 'title',
					'description' => __( 'This plugin only work for Store Address based in Indonesia.', 'woongkir' ),
				),
			);

			return;
		}

		$settings = array(
			'origin_location_state'     => array(
				'title' => __( 'Shipping Origin Province', 'woongkir' ),
				'type'  => 'origin',
			),
			'origin_location_city'      => array(
				'title' => __( 'Shipping Origin City', 'woongkir' ),
				'type'  => 'origin',
			),
			'origin_location_address_2' => array(
				'title' => __( 'Shipping Origin Subdistrict', 'woongkir' ),
				'type'  => 'origin',
			),
			'sort_shipping'             => array(
				'title'       => __( 'Sort Shipping', 'woongkir' ),
				'type'        => 'select',
				'default'     => 'no',
				'options'     => array(
					'cost'      => __( 'By Cost - Ascending', 'woongkir' ),
					'cost_desc' => __( 'By Cost - Descending', 'woongkir' ),
					'name'      => __( 'By Name - A to Z', 'woongkir' ),
					'name_desc' => __( 'By Name - Z to A', 'woongkir' ),
					'no'        => __( 'No', 'woongkir' ),
				),
				'description' => __( 'Sort the shipping couriers list in the cart and checkout page.', 'woongkir' ),
			),
			'show_eta'                  => array(
				'title'       => __( 'Show ETA', 'woongkir' ),
				'label'       => __( 'Yes', 'woongkir' ),
				'type'        => 'checkbox',
				'description' => __( 'Show estimated time of arrival during checkout.', 'woongkir' ),
			),
			'base_weight'               => array(
				'title'             => __( 'Base Cart Contents Weight (gram)', 'woongkir' ),
				'type'              => 'number',
				'description'       => __( 'The base cart contents weight will be calculated. If the value is blank or zero, the couriers list will not displayed when the actual cart contents weight is empty.', 'woongkir' ),
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '100',
				),
			),
			'api_key'                   => array(
				'title'       => __( 'RajaOngkir API Key', 'woongkir' ),
				'type'        => 'api_key',
				'placeholder' => '',
				'description' => __( '<a href="http://www.rajaongkir.com?utm_source=woongkir.com" target="_blank">Click here</a> to get RajaOngkir.com API Key. It is FREE.', 'woongkir' ),
				'default'     => '',
				'restore'     => true,
			),
			'account_type'              => array(
				'title'             => __( 'RajaOngkir Account Type', 'woongkir' ),
				'type'              => 'account_type',
				'default'           => 'starter',
				'options'           => array(),
				'custom_attributes' => array(
					'data-accounts' => wp_json_encode( $this->api->get_accounts( true ) ),
					'data-couriers' => wp_json_encode(
						array(
							'domestic'      => $this->api->get_couriers( 'domestic', 'all', true ),
							'international' => $this->api->get_couriers( 'international', 'all', true ),
						)
					),
				),
				'restore'           => true,
			),
			'selected_couriers'         => array(
				'title'   => __( 'Couriers', 'woongkir' ),
				'type'    => 'selected_couriers',
				'restore' => true,
			),
			'volumetric_calculator'     => array(
				'title'       => __( 'Volumetric Converter', 'woongkir' ),
				'label'       => __( 'Enable', 'woongkir' ),
				'type'        => 'checkbox',
				'description' => __( 'Convert volumetric to weight before send request to API server.', 'woongkir' ),
				'restore'     => true,
			),
			'volumetric_divider'        => array(
				'title'             => __( 'Volumetric Converter Divider', 'woongkir' ),
				'type'              => 'number',
				'description'       => __( 'The formula to convert volumetric to weight: <code>Width(cm) &#215; Length(cm) &#215; Height(cm) &#247; Divider</code>.', 'woongkir' ),
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '100',
				),
				'default'           => '6000',
				'restore'           => true,
			),
			'tax_status'                => array(
				'title'   => __( 'Tax Status', 'woongkir' ),
				'type'    => 'select',
				'default' => 'none',
				'options' => array(
					'taxable' => __( 'Taxable', 'woongkir' ),
					'none'    => _x( 'None', 'Tax status', 'woongkir' ),
				),
			),
		);

		$features = array_merge(
			$this->api->get_zones(),
			Woongkir_Account::get_features_label()
		);

		$accounts = $this->api->get_accounts();

		foreach ( $features as $feature_key => $feature_label ) {
			$account_features = array(
				'label' => $feature_label,
				'value' => array(),
			);

			foreach ( $accounts as $type => $account ) {
				if ( in_array( $feature_key, array( 'domestic', 'international' ), true ) ) {
					$account_features['value'][ $type ] = count( $this->api->get_couriers( $feature_key, $type ) );
				} else {
					$account_features['value'][ $type ] = $account->feature_enable( $feature_key ) ? __( 'Yes', 'woongkir' ) : __( 'No', 'woongkir' );
				}
			}

			$settings['account_type']['features'][ $feature_key ] = $account_features;
		}

		$this->instance_form_fields = $settings;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_filter( 'woocommerce_shipping_' . $this->id . '_instance_option', array( $this, 'instance_option_mapping' ), 10, 3 );
		add_filter( 'woocommerce_shipping_' . $this->id . '_instance_settings_values', array( $this, 'restore_instance_settings_values' ), 10, 2 );
	}

	/**
	 * Filter option value mapping.
	 *
	 * @param mixed                    $option Original setting value.
	 * @param string                   $key Setting key.
	 * @param Woongkir_Shipping_Method $instance Instance class object.
	 *
	 * @return mixed
	 */
	public function instance_option_mapping( $option, $key, $instance ) {
		if ( $instance->instance_id !== $this->instance_id ) {
			return $option;
		}

		switch ( $key ) {
			case 'origin_location_state':
			case 'origin_location_city':
			case 'origin_location_address_2':
				if ( ! $option ) {
					$origin_mapping = array(
						'state'     => 'origin_province',
						'city'      => 'origin_city',
						'address_2' => 'origin_subdistrict',
					);

					$key_short = str_replace( 'origin_location_', '', $key );
					$key_old   = $origin_mapping[ $key_short ];

					$origin_id = isset( $this->instance_settings[ $key_old ] ) ? $this->instance_settings[ $key_old ] : false;

					if ( $origin_id ) {
						$match = woongkir_get_json_data( $key_short, array( 'id' => (int) $origin_id ) );

						if ( $match ) {
							return $match['value'];
						}
					}
				}
				break;

			case 'selected_couriers':
				if ( ! $option ) {
					$selected_couriers = array();

					foreach ( array_keys( $this->api->get_zones() ) as $zone_id ) {
						$match = isset( $this->instance_settings[ $zone_id ] ) ? $this->instance_settings[ $zone_id ] : false;

						if ( $match ) {
							$selected_couriers[ $zone_id ] = $match;
						}
					}

					if ( $selected_couriers ) {
						return $selected_couriers;
					}
				}
				break;

			case 'api_key':
				$api_key_hardcoded = apply_filters( 'woongkir_api_key_hardcoded', false );

				if ( false !== $api_key_hardcoded ) {
					return $api_key_hardcoded;
				}
				break;
		}

		return $option;
	}

	/**
	 * Restore instance settings value to previous state on settings update error.
	 *
	 * @param array                    $instance_settings New instance settings values.
	 * @param Woongkir_Shipping_Method $instance Instance class object.
	 *
	 * @return array
	 */
	public function restore_instance_settings_values( $instance_settings, $instance ) {
		if ( ! $this->get_errors() || $instance->instance_id !== $this->instance_id ) {
			return $instance_settings;
		}

		foreach ( $this->restore_instance_settings as $key => $value ) {
			$instance_settings[ $key ] = $value;
		}

		return $instance_settings;
	}

	/**
	 * Get instance for field data.
	 *
	 * @since 1.3
	 *
	 * @param string      $key Field key.
	 * @param string|null $property Selected field data property.
	 * @param string|null $default_value Default value.
	 *
	 * @return mixed
	 */
	protected function get_instance_form_field_data( $key, $property = null, $default_value = null ) {
		static $form_fields = null;

		if ( is_null( $form_fields ) ) {
			$form_fields = $this->get_instance_form_fields();
		}

		if ( isset( $form_fields[ $key ] ) ) {
			if ( $property ) {
				return isset( $form_fields[ $key ][ $property ] ) ? $form_fields[ $key ][ $property ] : $default_value;
			}

			return $form_fields[ $key ];
		}

		return null;
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
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
			</th>
			<td class="forminp">
				<fieldset style="max-width: 50%;min-width: 250px;">
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<input class="input-text regular-input <?php echo esc_attr( $data['class'] ); ?>" type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" value="<?php echo esc_attr( $this->get_option( $key ) ); ?>" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
					<?php echo $this->get_description_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	/**
	 * Generate API Key HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @since  1.3.1
	 * @return string
	 */
	public function generate_api_key_html( $key, $data ) {
		$api_key_hardcoded = apply_filters( 'woongkir_api_key_hardcoded', false );

		if ( false !== $api_key_hardcoded ) {
			return;
		}

		return $this->generate_password_html( $key, $data );
	}

	/**
	 * Generate Account Type HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @since  1.0.0
	 * @return string
	 */
	public function generate_account_type_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
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
			'features'          => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>">
					<?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</label>
			</th>
			<td class="forminp">
				<div class="woongkir-account-features-wrap">
					<table id="woongkir-account-features" class="woongkir-account-features form-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Features', 'woongkir' ); ?></th>
								<?php foreach ( $this->api->get_accounts() as $account ) { ?>
									<th class="woongkir-account-features-col-<?php echo esc_attr( $account->get_type() ); ?>">
										<a href="https://rajaongkir.com/dokumentasi?utm_source=woongkir.com" target="_blank">
											<?php echo esc_html( $account->get_label() ); ?>
										</a>
									</th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( (array) $data['features'] as $feature ) : ?>
							<tr>
								<th><?php echo esc_html( $feature['label'] ); ?></th>
								<?php foreach ( $feature['value'] as $account_type => $feature_value ) : ?>
									<td class="woongkir-account-features-col-<?php echo esc_attr( $account_type ); ?>">
									<?php
									if ( 'yes' === strtolower( $feature_value ) ) {
										?>
									<span class="dashicons dashicons-yes"></span>
										<?php
									} elseif ( 'no' === strtolower( $feature_value ) ) {
										?>
									<span class="dashicons dashicons-no-alt"></span>
										<?php
									} else {
										echo esc_html( $feature_value );
									}
									?>
								</td>
								<?php endforeach; ?>
							</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot>
							<tr>
								<th></th>
								<?php foreach ( array_keys( $feature['value'] ) as $account_type ) : ?>
									<td class="woongkir-account-features-col-<?php echo esc_attr( $account_type ); ?>" data-title="<?php echo esc_attr( $this->api->get_account( $account_type )->get_label() ); ?>">
										<input type="radio" name="<?php echo esc_attr( $field_key ); ?>" value="<?php echo esc_attr( $account_type ); ?>" id="<?php echo esc_attr( $field_key ); ?>--<?php echo esc_attr( $account_type ); ?>" class="woongkir-account-type" <?php checked( $account_type, $this->get_option( $key ) ); ?> <?php echo $this->get_custom_attribute_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
									</td>
								<?php endforeach; ?>
							</tr>
						</tfoot>
					</table>
				</div>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	/**
	 * Generate selected couriers list table.
	 *
	 * @since  1.0.0
	 * @param  mixed $key Field key.
	 * @param  mixed $data Field data.
	 * @return string
	 */
	public function generate_selected_couriers_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$data      = wp_parse_args(
			$data,
			array(
				'title'             => '',
				'class'             => '',
				'disabled'          => false,
				'class'             => '',
				'css'               => '',
				'placeholder'       => '',
				'type'              => 'text',
				'desc_tip'          => false,
				'description'       => '',
				'custom_attributes' => array(),
			)
		);

		$selected_couriers = $this->get_option( $key );

		$zones = array();

		foreach ( $this->api->get_zones() as $zone_id => $zone_label ) {
			$zone_data = array(
				'label'    => $zone_label,
				'couriers' => array(),
			);

			$all_couriers = $this->api->get_couriers( $zone_id, 'all', true );

			if ( ! empty( $selected_couriers[ $zone_id ] ) ) {
				foreach ( $selected_couriers[ $zone_id ] as $courier_id => $selected_services ) {
					$courier = isset( $all_couriers[ $courier_id ] ) ? $all_couriers[ $courier_id ] : false;

					if ( ! $courier ) {
						continue;
					}

					$count_selected  = count( $selected_services );
					$count_available = count( $courier['services'] );
					$website         = ! empty( $courier['website'] ) ? add_query_arg( 'utm_source', 'woongkir.com', $courier['website'] ) : '';

					$zone_data['couriers'][ $courier_id ] = array_merge(
						$courier,
						array(
							'website'         => $website,
							'selected'        => $count_selected > 0,
							'count_selected'  => $count_selected,
							'count_available' => $count_available,
						)
					);

					foreach ( $courier['services'] as $service_id => $service_label ) {
						$value    = $zone_id . '_' . $courier_id . '_' . $service_id;
						$label    = $service_id === $service_label ? $service_label : $service_id . ' - ' . $service_label;
						$selected = in_array( $service_id, $selected_services, true );

						$zone_data['couriers'][ $courier_id ]['services'][ $service_id ] = array(
							'value'    => $value,
							'label'    => $label,
							'selected' => $selected,
						);
					}
				}
			}

			foreach ( $all_couriers as $courier_id => $courier ) {
				if ( isset( $zone_data['couriers'][ $courier_id ] ) ) {
					continue;
				}

				$website = ! empty( $courier['website'] ) ? add_query_arg( 'utm_source', 'woongkir.com', $courier['website'] ) : '';

				$zone_data['couriers'][ $courier_id ] = array_merge(
					$courier,
					array(
						'website'         => $website,
						'selected'        => false,
						'count_selected'  => 0,
						'count_available' => count( $courier['services'] ),
					)
				);

				foreach ( $courier['services'] as $service_id => $service_label ) {
					$value = $zone_id . '_' . $courier_id . '_' . $service_id;
					$label = $service_id === $service_label ? $service_label : $service_id . ' - ' . $service_label;

					$zone_data['couriers'][ $courier_id ]['services'][ $service_id ] = array(
						'value'    => $value,
						'label'    => $label,
						'selected' => false,
					);
				}
			}

			$zones[ $zone_id ] = $zone_data;
		}

		ob_start();

		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>">
					<?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</label>
			</th>
			<td class="forminp">
				<div class="woongkir-tab-container">
				<ul class="woongkir-tab-nav">
					<?php foreach ( $zones as $zone_id => $zone ) : ?>
					<li>
						<a href="#woongkir-tab-content-<?php echo esc_attr( $zone_id ); ?>" class="woongkir-tab-nav-item woongkir-tab-nav-item--<?php echo esc_attr( $zone_id ); ?>">
							<?php echo esc_html( $zone['label'] ); ?>
						</a>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php foreach ( $zones as $zone_id => $zone ) : ?>
				<div id="woongkir-tab-content-<?php echo esc_attr( $zone_id ); ?>" class="woongkir-tab-content woongkir-tab-content--<?php echo esc_attr( $zone_id ); ?>">
					<ul class="woongkir-couriers" id="woongkir-couriers-<?php echo esc_attr( $zone_id ); ?>">
						<?php foreach ( $zone['couriers'] as $courier_id => $courier ) : ?>
						<li class="woongkir-couriers-item" data-id="<?php echo esc_attr( $courier_id ); ?>" data-zone="<?php echo esc_attr( $zone_id ); ?>">
							<div class="woongkir-couriers-item-inner">
								<div class="woongkir-couriers-item-info">
									<label class="woongkir-couriers-item-info-title">
										<input type="checkbox" class="woongkir-service woongkir-service--bulk" <?php checked( $courier['selected'], true ); ?> />
										<span class="woongkir-couriers--label"><?php echo wp_kses_post( $courier['label'] ); ?></span>
										<span class="woongkir-couriers--selected"><?php echo esc_html( $courier['count_selected'] ); ?></span>
										<span class="woongkir-couriers--available"><?php echo esc_html( $courier['count_available'] ); ?></span>
									</label>
									<?php if ( $courier['website'] ) : ?>
									<div class="woongkir-couriers-item-info-link">
										<a href="<?php echo esc_url( $courier['website'] ); ?>" target="blank" title="<?php esc_attr_e( 'Visit courier\'s website', 'woongkir' ); ?>">
											<span class="dashicons dashicons-admin-links"></span>
										</a>
									</div>
									<?php endif; ?>
									<div class="woongkir-couriers-item-info-toggle">
										<a href="#" class="woongkir-couriers-toggle" title="<?php esc_attr_e( 'Toggle', 'woongkir' ); ?>">
											<span class="dashicons dashicons-admin-generic"></span>
										</a>
									</div>
								</div>
								<ul class="woongkir-services">
									<?php foreach ( $courier['services'] as $service_id => $service ) : ?>
									<li class="woongkir-services-item">
										<label>
											<input type="checkbox" class="woongkir-service woongkir-service--single" name="<?php echo esc_attr( $field_key ); ?>[]" value="<?php echo esc_attr( $service['value'] ); ?>" <?php checked( $service['selected'], true ); ?>>
											<span><?php echo wp_kses_post( $service['label'] ); ?></span>
										</label>
									</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endforeach; ?>
				</div>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	/**
	 * Validate api_key settings field.
	 *
	 * @since 1.0.0
	 * @param string $key Input field key.
	 * @param string $value Input field current value.
	 * @throws Exception Error message.
	 */
	public function validate_api_key_field( $key, $value ) {
		$field_label       = $this->get_instance_form_field_data( $key, 'title', $key );
		$api_key_hardcoded = apply_filters( 'woongkir_api_key_hardcoded', false );
		$api_key           = false !== $api_key_hardcoded ? $api_key_hardcoded : $value;

		if ( empty( $api_key ) ) {
			// translators: %s is setting field label.
			throw new Exception( wp_sprintf( __( '%s is required.', 'woongkir' ), $field_label ) );
		}

		$account_type = $this->validate_account_type_field( 'account_type', $this->posted_field_value( 'account_type' ) );

		if ( $account_type ) {
			$this->api->set_option( 'api_key', $api_key );
			$this->api->set_option( 'account_type', $account_type );

			$results = $this->api->validate_account();

			if ( is_wp_error( $results ) ) {
				throw new Exception( $results->get_error_message() );
			}
		}

		if ( false !== $api_key_hardcoded && isset( $this->instance_settings[ $key ] ) ) {
			return $this->instance_settings[ $key ];
		}

		return $value;
	}

	/**
	 * Validate account_type settings field.
	 *
	 * @since 1.0.0
	 * @param string $key Input field key.
	 * @param string $value Input field current value.
	 * @throws Exception If field value is not valid.
	 */
	public function validate_account_type_field( $key, $value ) {
		$field_label = $this->get_instance_form_field_data( $key, 'title', $key );

		if ( empty( $value ) ) {
			// translators: %s is setting field label.
			throw new Exception( wp_sprintf( __( '%s is required.', 'woongkir' ), $field_label ) );
		}

		if ( ! $this->api->get_account( $value ) ) {
			// translators: %s is setting field label.
			throw new Exception( wp_sprintf( __( '%s is invalid.', 'woongkir' ), $field_label ) );
		}

		return $value;
	}

	/**
	 * Validate settings field type origin.
	 *
	 * @since 1.0.0
	 * @param string $key Input field key.
	 * @param string $value Input field current value.
	 * @throws Exception If field value is not valid.
	 * @return string
	 */
	public function validate_origin_field( $key, $value ) {
		$field_label = $this->get_instance_form_field_data( $key, 'title', $key );

		if ( empty( $value ) ) {
			// translators: %s is setting field label.
			throw new Exception( wp_sprintf( __( '%s is required.', 'woongkir' ), $field_label ) );
		}

		return $value;
	}

	/**
	 * Validate settings field type selected couriers.
	 *
	 * @since 1.0.0
	 * @param  string $key Settings field key.
	 * @param  string $value Posted field value.
	 * @throws Exception If the field value is invalid.
	 * @return array
	 */
	public function validate_selected_couriers_field( $key, $value ) {
		if ( is_string( $value ) ) {
			$value = array_map( 'trim', explode( ',', $value ) );
		}

		$field_label = $this->get_instance_form_field_data( $key, 'title', $key );

		// Format the value as associative array courier_data => services.
		if ( $value && is_array( $value ) ) {
			$format_value = array();

			foreach ( $value as $val ) {
				$parts = explode( '_', $val );

				if ( count( $parts ) === 3 ) {
					$format_value[ $parts[0] ][ $parts[1] ][] = $parts[2];
				}
			}

			$value = $format_value;
		}

		if ( ! $value ) {
			// Translators: %1$s Setting field label.
			throw new Exception( wp_sprintf( __( '%1$s is required.', 'woongkir' ), $field_label ) );
		}

		$account = $this->api->get_account( $this->posted_field_value( 'account_type' ) );

		if ( $account ) {
			foreach ( $value as $zone => $couriers ) {
				if ( ! $couriers ) {
					continue;
				}

				if ( count( $couriers ) > 1 && ! $account->feature_enable( 'multiple_couriers' ) ) {
					// Translators: %1$s Setting field label, %2$s Account label.
					throw new Exception( wp_sprintf( __( '%1$s: Account type %2$s is not allowed to select multiple couriers.', 'woongkir' ), $field_label, $account->get_label( 'label' ) ) );
				}

				$not_allowed = array_diff_key( $couriers, $this->api->get_couriers( $zone, $account->get_type() ) );

				if ( $not_allowed ) {
					// Translators: %1$s Setting field label, %2$s Account label, %3$s Couriers name.
					throw new Exception( wp_sprintf( __( '%1$s Shipping: Account type %2$s is not allowed to select courier_data %3$s.', 'woongkir' ), $field_label, $account->get_label( 'label' ), strtoupper( implode( ', ', array_keys( $not_allowed ) ) ) ) );
				}
			}
		}

		return $value;
	}

	/**
	 * Calculate the shipping cost.
	 *
	 * @param array $package Cart data.
	 * @throws Exception If the field value is invalid.
	 * @since 1.0.0
	 */
	public function calculate_shipping( $package = array() ) {
		try {
			$api_request_data = $this->calculate_shipping_api_request_data( $package );

			$this->show_debug(
				wp_json_encode(
					array(
						'calculate_shipping.$api_request_data' => $api_request_data,
					)
				)
			);

			if ( is_wp_error( $api_request_data ) ) {
				throw new Exception( $api_request_data->get_error_message() );
			}

			$cache_key    = $this->generate_cache_key( $api_request_data );
			$cache_enable = woongkir_is_enable_cache();

			$this->show_debug(
				wp_json_encode(
					array(
						'calculate_shipping.$cache' => array(
							'enable' => $cache_enable,
							'key'    => $cache_key,
						),
					)
				)
			);

			$results = false;

			if ( woongkir_is_enable_cache() ) {
				$results = get_transient( $cache_key );
			}

			if ( false === $results ) {
				$results = $this->api->calculate_shipping_by_zone( $api_request_data['zone'], $api_request_data['params'] );

				if ( $cache_enable && $results && ! is_wp_error( $results ) ) {
					set_transient( $cache_key, $results, HOUR_IN_SECONDS ); // Store response data for 1 hour.
				}
			}

			/**
			 * Filter the shipping calculation results.
			 *
			 * @since 1.2.12
			 *
			 * @param array|WP_Error           $results API shipping calculation results.
			 * @param array                    $package Current order package data.
			 * @param Woongkir_Shipping_Method $object  Current class object.
			 *
			 * @return array
			 */
			$results = apply_filters( 'woongkir_shipping_results', $results, $package, $this );

			if ( is_wp_error( $results ) ) {
				throw new Exception( $results->get_error_message() );
			}

			if ( ! $results ) {
				throw new Exception( __( 'No couriers data found', 'woongkir' ) );
			}

			if ( ! is_array( $results ) ) {
				// translators: % s Encoded data response .
				throw new Exception( wp_sprintf( __( 'Couriers data is invalid: %s', 'woongkir' ), wp_json_encode( $results ) ) );
			}

			$this->show_debug(
				wp_json_encode(
					array(
						'calculate_shipping.$results.parsed' => $results['parsed'],
					)
				)
			);

			$this->show_debug(
				wp_json_encode(
					array(
						'calculate_shipping.$results.raw' => $results['raw'],
					)
				)
			);

			$selected_couriers = $this->get_selected_couriers( $api_request_data['zone'] );

			$this->show_debug(
				wp_json_encode(
					array(
						'calculate_shipping.$selected_couriers' => $selected_couriers,
					)
				)
			);

			$sort_shipping = $this->get_option( 'sort_shipping' );
			$show_eta      = $this->get_option( 'show_eta' );

			if ( 'no' !== $sort_shipping && is_callable( array( $this, 'sort_results_by_' . $sort_shipping ) ) ) {
				usort( $results['parsed'], array( $this, 'sort_results_by_' . $sort_shipping ) );
			}

			foreach ( $results['parsed'] as $result ) {
				$selected_services = isset( $selected_couriers[ $result['courier'] ] ) ? $selected_couriers[ $result['courier'] ] : array();

				if ( ! $selected_services || ! in_array( $result['service'], $selected_services, true ) ) {
					continue;
				}

				$label = strtoupper( $result['courier'] ) . ' - ' . $result['service'];

				if ( 'yes' === $show_eta && $result['etd'] ) {
					$label = wp_sprintf( '%1$s (%2$s)', $label, $result['etd'] );
				}

				/**
				 * Filter the shipping rate label.
				 *
				 * @since 1.2.12
				 *
				 * @param string                   $label The default shipping rate label.
				 * @param bool                     $result     Shipping rate result data.
				 * @param array                    $package    Current order package data.
				 * @param Woongkir_Shipping_Method $object     Current class object.
				 *
				 * @return string
				 */
				$label = apply_filters( 'woongkir_shipping_rate_label', $label, $result, $package, $this );

				$this->add_rate(
					array(
						'id'        => $this->get_rate_id( $result['courier'] . ':' . $result['service'] ),
						'label'     => $label,
						'cost'      => $result['cost'],
						'meta_data' => array(
							'_woongkir_data' => $result,
						),
					)
				);
			}
		} catch ( Exception $e ) {
			$this->show_debug( $e->getMessage() );
		}
	}

	/**
	 * Get selected couriers setting.
	 *
	 * @param string $zone Couriers zone ID.
	 *
	 * @return array
	 */
	private function get_selected_couriers( $zone ) {
		$selected_couriers = $this->get_option( 'selected_couriers' );

		if ( $selected_couriers && isset( $selected_couriers[ $zone ] ) ) {
			return $selected_couriers[ $zone ];
		}

		return array();
	}

	/**
	 * Get shipping origin info
	 *
	 * @param array $shipping_address Shipping address data in associative array format: address, city, state, postcode, country.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private function get_origin_info( $shipping_address = array() ) {
		if ( ! isset( $shipping_address['country'] ) ) {
			return false;
		}

		$origin_location_city = woongkir_get_json_data(
			'city',
			array(
				'value' => $this->get_option( 'origin_location_city' ),
			)
		);

		if ( ! $origin_location_city ) {
			return false;
		}

		$account = $this->api->get_account( $this->get_option( 'account_type' ) );

		if ( ! $account ) {
			return false;
		}

		if ( 'ID' === $shipping_address['country'] ) {
			if ( $account->feature_enable( 'subdistrict' ) ) {
				$origin_location_address_2 = woongkir_get_json_data(
					'address_2',
					array(
						'city'  => $this->get_option( 'origin_location_city' ),
						'value' => $this->get_option( 'origin_location_address_2' ),
					)
				);

				if ( ! $origin_location_address_2 ) {
					return false;
				}

				return array(
					'origin'     => $origin_location_address_2['id'],
					'originType' => 'subdistrict',
				);
			}

			return array(
				'origin'     => $origin_location_city['id'],
				'originType' => 'city',
			);
		}

		return array(
			'origin' => $origin_location_city['id'],
		);
	}

	/**
	 * Populate API request data.
	 *
	 * @since 1.2.12
	 *
	 * @param array $package Current order package data.
	 *
	 * @throws Exception If the request parameters is incomplete.
	 *
	 * @return array|WP_Error
	 */
	private function calculate_shipping_api_request_data( $package = array() ) {
		try {
			if ( ! isset( $package['destination']['country'] ) ) {
				throw new Exception( __( 'Shipping destination country is empty.', 'woongkir' ) );
			}

			/**
			 * Shipping origin info .
			 *
			 * @since 1.2.9
			 *
			 * @param array $origin_info Original origin info .
			 * @param array $package Current order package data .
			 *
			 * @return array
			 */
			$origin_info = apply_filters( 'woongkir_shipping_origin_info', $this->get_origin_info( $package['destination'] ), $package );

			if ( empty( $origin_info ) ) {
				throw new Exception( __( 'Shipping origin info is empty or invalid', 'woongkir' ) );
			}

			/**
			 * Shipping destination info .
			 *
			 * @since 1.2.9
			 *
			 * @param array $destination_info Original destination info .
			 * @param array $package Current order package data .
			 *
			 * @return array
			 */
			$destination_info = apply_filters( 'woongkir_shipping_destination_info', $this->get_destination_info( $package['destination'] ), $package );

			if ( ! $destination_info || ! array_filter( $destination_info ) ) {
				throw new Exception( __( 'Shipping destination info is empty or invalid', 'woongkir' ) );
			}

			/**
			 * Shipping dimension & weight info .
			 *
			 * @since 1.2.9
			 *
			 * @param array $dimension_weight Original dimension & weight info .
			 * @param array $package Current order package data .
			 *
			 * @return array
			 */
			$dimension_weight = apply_filters( 'woongkir_shipping_dimension_weight', $this->get_dimension_weight( $package['contents'] ), $package );

			if ( ! $dimension_weight || ! array_filter( $dimension_weight ) ) {
				throw new Exception( __( 'Cart weight or dimension is empty or invalid', 'woongkir' ) );
			}

			$zone    = $this->api->get_zone_by_country( $package['destination']['country'] );
			$courier = $this->get_selected_couriers( $zone );

			if ( ! $courier || ! array_filter( $courier ) ) {
				throw new Exception( __( 'No couriers selected', 'woongkir' ) );
			}

			return array(
				'zone'   => $zone,
				'params' => array_merge(
					$origin_info,
					$destination_info,
					$dimension_weight,
					array(
						'courier' => array_keys( $courier ),
					)
				),
			);
		} catch ( Exception $e ) {
			return new WP_Error( 'api_request_params_error', $e->getMessage() );
		}
	}

	/**
	 * Get shipping destination info
	 *
	 * @since 1.0.0
	 *
	 * @param array $shipping_address Shipping address data in associative array format: address, city, state, postcode, country.
	 *
	 * @return array
	 */
	private function get_destination_info( $shipping_address = array() ) {
		// Bail early when the shipping destination country is empty.
		if ( empty( $shipping_address['country'] ) ) {
			return false;
		}

		$zone = $this->api->get_zone_by_country( $shipping_address['country'] );

		if ( 'international' === $zone ) {
			$country = woongkir_get_json_data(
				'country',
				array(
					'value' => $shipping_address['country'],
				)
			);

			if ( ! $country ) {
				return false;
			}

			return array(
				'destination' => $country['id'],
			);
		}

		// Bail early when the shipping destination state is empty.
		if ( empty( $shipping_address['city'] ) ) {
			return false;
		}

		// Get province ID data.
		$state = woongkir_get_json_data(
			'state',
			array(
				'value' => $shipping_address['state'],
			)
		);

		if ( ! $state ) {
			return false;
		}

		// Bail early when the shipping destination city is empty.
		if ( empty( $shipping_address['city'] ) ) {
			return false;
		}

		$city = woongkir_get_json_data(
			'city',
			array(
				'value'    => $shipping_address['city'],
				'state_id' => $state['id'],
			)
		);

		if ( ! $city ) {
			return false;
		}

		// Get current API account.
		$account = $this->api->get_account( $this->get_option( 'account_type' ) );

		if ( $account && $account->feature_enable( 'subdistrict' ) && ! empty( $shipping_address['address_2'] ) ) {
			$address_2 = woongkir_get_json_data(
				'address_2',
				array(
					'value'    => $shipping_address['address_2'],
					'city_id'  => $city['id'],
					'state_id' => $state['id'],
				)
			);

			if ( $address_2 ) {
				return array(
					'destination'     => $address_2['id'],
					'destinationType' => 'subdistrict',
				);
			}
		}

		return array(
			'destination'     => $city['id'],
			'destinationType' => 'city',
		);
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
			if ( ! $item['data']->needs_shipping() ) {
				continue;
			}

			// Validate cart item quantity value.
			$item_quantity = absint( $item['quantity'] );
			if ( ! $item_quantity ) {
				continue;
			}

			// Validate cart item weight value.
			$item_weight = is_numeric( $item['data']->get_weight() ) ? $item['data']->get_weight() : 0;
			array_push( $weight, $item_weight * $item_quantity );

			// Validate cart item width value.
			$item_width = is_numeric( $item['data']->get_width() ) ? $item['data']->get_width() : 0;
			array_push( $width, $item_width * 1 );

			// Validate cart item length value.
			$item_length = is_numeric( $item['data']->get_length() ) ? $item['data']->get_length() : 0;
			array_push( $length, $item_length * 1 );

			// Validate cart item height value.
			$item_height = is_numeric( $item['data']->get_height() ) ? $item['data']->get_height() : 0;
			array_push( $height, $item_height * $item_quantity );
		}

		$data['weight'] = wc_get_weight( array_sum( $weight ), 'g' );

		// Convert the volumetric to weight.
		$account = $this->api->get_account( $this->get_option( 'account_type' ) );

		if ( $account && $account->feature_enable( 'volumetric' ) ) {
			$width  = wc_get_dimension( max( $width ), 'cm' );
			$length = wc_get_dimension( max( $length ), 'cm' );
			$height = wc_get_dimension( array_sum( $height ), 'cm' );

			$data['width']  = $width;
			$data['length'] = $length;
			$data['height'] = $height;

			if ( 'yes' === $this->get_option( 'volumetric_calculator' ) && $this->get_option( 'volumetric_divider' ) ) {
				$data['weight'] = max( $data['weight'], $this->convert_volumetric( $width, $length, $height ) );
			}
		}

		// Set the package weight to based on base_weight setting value.
		$base_weight = absint( $this->get_option( 'base_weight' ) );

		if ( $base_weight && $data['weight'] < $base_weight ) {
			$data['weight'] = $base_weight;
		}

		return $data;
	}

	/**
	 * Convert volume metric to weight.
	 *
	 * @since 1.2.9
	 *
	 * @param int $width  Package width in cm.
	 * @param int $length Package width in cm.
	 * @param int $height Package height in cm.
	 *
	 * @return int Weight in gram units.
	 */
	public function convert_volumetric( $width, $length, $height ) {
		return ceil( ( ( $width * $length * $height ) / $this->get_option( 'volumetric_divider' ) ) * 1000 );
	}

	/**
	 * Generate cache key
	 *
	 * @since 1.2.12
	 *
	 * @param array $api_request_params API request parameters.
	 *
	 * @return boolean
	 */
	private function generate_cache_key( $api_request_params = array() ) {
		$cache_keys = array(
			'cart_hash' => WC()->cart->get_cart_hash(),
		);

		foreach ( array_keys( $this->instance_form_fields ) as $cache_key ) {
			if ( isset( $cache_keys[ $cache_key ] ) ) {
				continue;
			}

			$cache_keys[ $cache_key ] = $this->get_option( $cache_key );
		}

		return $this->id . '_' . $this->instance_id . '_' . md5(
			wp_json_encode(
				array_merge(
					$api_request_params,
					$cache_keys
				)
			)
		);
	}

	/**
	 * Sort couriers services by cost ascending
	 *
	 * @param array $a Value to compare.
	 * @param array $b Value to compare.
	 * @return int
	 */
	protected function sort_results_by_cost( $a, $b ) {
		$a_cost = isset( $a['cost'] ) ? $a['cost'] : 0;
		$b_cost = isset( $b['cost'] ) ? $b['cost'] : 0;

		if ( $a_cost === $b_cost ) {
			return 0;
		}

		return ( $a_cost > $b_cost ) ? 1 : -1;
	}

	/**
	 * Sort couriers services by cost descending
	 *
	 * @param array $a Value to compare.
	 * @param array $b Value to compare.
	 * @return int
	 */
	protected function sort_results_by_cost_desc( $a, $b ) {
		$a_cost = isset( $a['cost'] ) ? $a['cost'] : 0;
		$b_cost = isset( $b['cost'] ) ? $b['cost'] : 0;

		if ( $a_cost === $b_cost ) {
			return 0;
		}

		return ( $a_cost < $b_cost ) ? 1 : -1;
	}

	/**
	 * Sort couriers services by name ascending
	 *
	 * @param array $a Value to compare.
	 * @param array $b Value to compare.
	 * @return int
	 */
	protected function sort_results_by_name( $a, $b ) {
		$a_name = strtoupper( $a['courier'] ) . ' - ' . $a['service'];
		$b_name = strtoupper( $b['courier'] ) . ' - ' . $b['service'];

		if ( $a_name === $b_name ) {
			return 0;
		}

		return ( $a_name > $b_name ) ? 1 : -1;
	}

	/**
	 * Sort couriers services by name descending
	 *
	 * @param array $a Value to compare.
	 * @param array $b Value to compare.
	 * @return int
	 */
	protected function sort_results_by_name_desc( $a, $b ) {
		$a_name = strtoupper( $a['courier'] ) . ' - ' . $a['service'];
		$b_name = strtoupper( $b['courier'] ) . ' - ' . $b['service'];

		if ( $a_name === $b_name ) {
			return 0;
		}

		return ( $a_name < $b_name ) ? 1 : -1;
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
	 * @param string $message     The text to display in the notice.
	 * @param string $notice_type The name of the notice type - either error, success or notice.
	 * @return void
	 */
	private function show_debug( $message, $notice_type = 'notice' ) {
		$message = $this->id . '_' . $this->instance_id . ' : ' . $message;

		if (
			defined( 'WC_DOING_AJAX' ) && WC_DOING_AJAX
			|| 'yes' !== get_option( 'woocommerce_shipping_debug_mode', 'no' )
			|| ! current_user_can( 'manage_options' )
			|| wc_has_notice( $message )
		) {
			return;
		}

		wc_add_notice( $message, $notice_type );
	}
}
