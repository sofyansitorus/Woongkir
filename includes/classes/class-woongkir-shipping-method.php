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

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

		// Define user set variables.
		foreach ( $this->instance_form_fields as $field_id => $field ) {
			$default = isset( $field['default'] ) ? $field['default'] : null;
			$type    = isset( $field['type'] ) ? $field['type'] : false;

			if ( ! $type || in_array( $type, array( 'title' ), true ) ) {
				continue;
			}

			$option = $this->get_option( $field_id, $default );

			if ( $option && in_array( $field_id, array( 'api_key', 'account_type' ), true ) ) {
				$this->api->set_option( $field_id, $option );
			}

			$this->{$field_id} = $option;
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
			'origin_province'           => array(
				'title' => __( 'Shipping Origin Province', 'woongkir' ),
				'type'  => 'origin',
			),
			'origin_city'               => array(
				'title' => __( 'Shipping Origin City', 'woongkir' ),
				'type'  => 'origin',
			),
			'origin_subdistrict'        => array(
				'title' => __( 'Shipping Origin Subdistrict', 'woongkir' ),
				'type'  => 'origin',
			),
			'origin_location_state'     => array(
				'title' => __( 'Shipping Origin Province', 'woongkir' ),
				'type'  => 'text',
			),
			'origin_location_city'      => array(
				'title' => __( 'Shipping Origin City', 'woongkir' ),
				'type'  => 'text',
			),
			'origin_location_address_2' => array(
				'title' => __( 'Shipping Origin Subdistrict', 'woongkir' ),
				'type'  => 'text',
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
				'type'        => 'text',
				'placeholder' => '',
				'description' => __( '<a href="http://www.rajaongkir.com?utm_source=woongkir.com" target="_blank">Click here</a> to get RajaOngkir.com API Key. It is FREE.', 'woongkir' ),
				'default'     => '',
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
			),
			'couriers'                  => array(
				'title' => __( 'Couriers', 'woongkir' ),
				'type'  => 'select_couriers',
			),
			'volumetric_calculator'     => array(
				'title'       => __( 'Volumetric Converter', 'woongkir' ),
				'label'       => __( 'Enable', 'woongkir' ),
				'type'        => 'checkbox',
				'description' => __( 'Convert volumetric to weight before send request to API server.', 'woongkir' ),
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
			),
		);

		$features = array_merge(
			array(
				'domestic'      => __( 'Domestic Shipping Couriers', 'woongkir' ),
				'international' => __( 'International Shipping Couriers', 'woongkir' ),
			),
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
	 * Generate Select HTML.
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
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
			</th>
			<td class="forminp">
				<div class="woongkir-account-features-wrap">
					<table id="woongkir-account-features" class="woongkir-account-features form-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Features', 'woongkir' ); ?></th>
								<?php foreach ( $this->api->get_accounts() as $account ) { ?>
									<th class="woongkir-account-features-col-<?php echo esc_attr( $account->get_type() ); ?>"><a href="https://rajaongkir.com/dokumentasi?utm_source=woongkir.com" target="_blank"><?php echo esc_html( $account->get_label() ); ?></a></th>
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
	 * Generate couriers list table.
	 *
	 * @since  1.0.0
	 * @param  mixed $key Field key.
	 * @param  mixed $data Field data.
	 * @return string
	 */
	public function generate_select_couriers_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );

		$defaults = array(
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
			'zones'             => array(
				'domestic'      => __( 'Domestic Shipping', 'woongkir' ),
				'international' => __( 'International Shipping', 'woongkir' ),
			),
		);

		$data            = wp_parse_args( $data, $defaults );
		$couriers_stored = $this->get_option( $key );

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
					<?php foreach ( $data['zones'] as $zone => $zone_label ) : ?>
					<li>
						<a href="#woongkir-tab-content-<?php echo esc_attr( $zone ); ?>" class="woongkir-tab-nav-item woongkir-tab-nav-item--<?php echo esc_attr( $zone ); ?>">
							<?php echo esc_html( $zone_label ); ?>
						</a>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php
				foreach ( $data['zones'] as $zone => $zone_label ) :
					$couriers = $this->api->get_couriers( $zone, 'all', true );
					?>
					<div id="woongkir-tab-content-<?php echo esc_attr( $zone ); ?>" class="woongkir-tab-content woongkir-tab-content--<?php echo esc_attr( $zone ); ?>">
						<ul class="woongkir-couriers" id="woongkir-couriers-<?php echo esc_attr( $zone ); ?>">
						<?php
						$i = 0;
						foreach ( $couriers as $courier_id => $courier ) :
							if ( empty( $courier['services'] ) ) :
								continue;
							endif;

							$service_stored               = isset( $couriers_stored[ $zone ][ $courier_id ] ) ? $couriers_stored[ $zone ][ $courier_id ] : array();
							$service_bulk_count_available = isset( $courier['services'] ) ? count( $courier['services'] ) : 0;
							$service_bulk_count_selected  = count( $service_stored );
							$service_bulk_checked         = $service_bulk_count_selected && $service_bulk_count_selected === $service_bulk_count_available;
							$courier_website              = ! empty( $courier['website'] ) ? add_query_arg( 'utm_source', 'woongkir.com', $courier['website'] ) : '';
							?>
							<li class="woongkir-couriers-item" data-id="<?php echo esc_attr( $courier_id ); ?>" data-zone="<?php echo esc_attr( $zone ); ?>">
								<div class="woongkir-couriers-item-inner">
									<div class="woongkir-couriers-item-info">
										<label class="woongkir-couriers-item-info-title">
											<input type="checkbox" class="woongkir-service woongkir-service--bulk" <?php checked( $service_bulk_checked, true ); ?> />
											<span class="woongkir-couriers--label"><?php echo wp_kses_post( $courier['label'] ); ?></span>
											<span class="woongkir-couriers--selected"><?php echo esc_html( $service_bulk_count_selected ); ?></span>
											<span class="woongkir-couriers--available"><?php echo esc_html( $service_bulk_count_available ); ?></span>
										</label>
										<?php if ( $courier_website ) : ?>
										<div class="woongkir-couriers-item-info-link">
											<a href="<?php echo esc_url( $courier_website ); ?>" target="blank" title="<?php esc_attr_e( 'Visit courier\'s website', 'woongkir' ); ?>">
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
										<?php
										foreach ( $courier['services'] as $index => $service ) :
											$service_label   = $index !== $service ? $index . ' - ' . $service : $service;
											$service_value   = $zone . '_' . $courier_id . '_' . $index;
											$service_checked = $service_stored && in_array( $index, $service_stored, true ) ? $index : false;
											?>
										<li class="woongkir-services-item">
											<label>
												<input type="checkbox" class="woongkir-service woongkir-service--single" name="<?php echo esc_attr( $field_key ); ?>[]" value="<?php echo esc_attr( $service_value ); ?>" <?php checked( $service_checked, $index ); ?>>
												<span><?php echo wp_kses_post( $service_label ); ?></span>
											</label>
										</li>
											<?php
										endforeach;
										?>
									</ul>
								</div>
							</li>
							<?php
							$i++;
						endforeach;
						?>
						</ul>
					</div>
					<?php
				endforeach;
				?>
				</div>
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
		);

		$data = wp_parse_args( $data, $defaults );

		$couriers = $this->api->get_couriers( $key, 'all', true );

		uasort( $couriers, array( $this, 'sort_couriers_list_' . $key ) );

		$selected = $this->{$key};

		ob_start();
		?>
		<tr valign="top" class="woongkir-couriers-wrap woongkir-couriers-wrap--<?php echo esc_attr( $key ); ?>">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
			</th>
			<td class="forminp">
				<ul class="woongkir-couriers">
					<?php
					$i = 0;
					foreach ( $couriers as $courier_id => $courier ) :
						if ( empty( $courier['services'] ) ) :
							continue;
						endif;
						?>
						<li class="woongkir-couriers-item woongkir-couriers-item--<?php echo esc_attr( $key ); ?>--<?php echo esc_attr( $courier_id ); ?>" data-id="<?php echo esc_attr( $courier_id ); ?>" data-zone="<?php echo esc_attr( $key ); ?>">
							<div class="woongkir-couriers-item-inner">
								<div class="woongkir-couriers-item-info">
									<label class="woongkir-couriers-item-info-title" for="<?php echo esc_attr( $field_key ); ?>_<?php echo esc_attr( $courier_id ); ?>_toggle">
										<input type="checkbox" id="<?php echo esc_attr( $field_key ); ?>_<?php echo esc_attr( $courier_id ); ?>_toggle" class="woongkir-service woongkir-service--bulk" <?php checked( ( isset( $selected[ $courier_id ] ) && count( $selected[ $courier_id ] ) ? 1 : 0 ), 1 ); ?>>
										<?php echo wp_kses_post( $courier['label'] ); ?> (<span class="woongkir-couriers--selected"><?php echo esc_html( ( isset( $selected[ $courier_id ] ) ? count( $selected[ $courier_id ] ) : 0 ) ); ?></span> / <span class="woongkir-couriers--available"><?php echo esc_html( count( $courier['services'] ) ); ?></span>)
									</label>
									<?php
									if ( isset( $courier['website'] ) ) {
										?>
									<div class="woongkir-couriers-item-info-link"><a href="<?php echo esc_attr( $courier['website'] ); ?>?utm_source=woongkir.com" target="blank" title="<?php esc_attr_e( 'Visit courier\'s website', 'woongkir' ); ?>"><span class="dashicons dashicons-admin-links"></span></a></div>
										<?php
									}
									?>
									<div class="woongkir-couriers-item-info-toggle">
										<a href="#" class="woongkir-couriers-toggle" title="<?php esc_attr_e( 'Toggle', 'woongkir' ); ?>"><span class="dashicons dashicons-admin-generic"></span></a>
									</div>
								</div>
								<ul class="woongkir-services">
									<?php
									foreach ( $courier['services'] as $index => $service ) :
										$service_label = $index !== $service ? wp_sprintf( '%1$s - %2$s', $index, $service ) : $service;
										?>
									<li class="woongkir-services-item">
										<label for="<?php echo esc_attr( $field_key ); ?>_<?php echo esc_attr( $courier_id ); ?>_<?php echo esc_attr( $index ); ?>">
											<input type="checkbox" class="woongkir-service woongkir-service--single" id="<?php echo esc_attr( $field_key ); ?>_<?php echo esc_attr( $courier_id ); ?>_<?php echo esc_attr( $index ); ?>" name="<?php echo esc_attr( $field_key ); ?>[]" value="<?php echo esc_attr( $courier_id ); ?>_<?php echo esc_attr( $index ); ?>" <?php checked( ( isset( $selected[ $courier_id ] ) && in_array( $index, $selected[ $courier_id ], true ) ? $index : false ), $index ); ?>><?php echo wp_kses_post( $service_label ); ?>
										</label>
									</li>
										<?php
									endforeach;
									?>
								</ul>
							</div>
						</li>
						<?php
						$i++;
					endforeach;
					?>
				</ul>
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
		if ( empty( $value ) ) {
			throw new Exception( __( 'API Key is required.', 'woongkir' ) );
		}

		$account_type = $this->validate_account_type_field( 'account_type', $this->posted_field_value( 'account_type' ) );
		if ( $account_type ) {
			$this->api->set_option( 'api_key', $value );
			$this->api->set_option( 'account_type', $account_type );

			$results = $this->api->validate_account();

			if ( is_wp_error( $results ) ) {
				throw new Exception( $results->get_error_message(), 1 );
			}

			if ( ! isset( $results['parsed'], $results['raw'] ) || ! $results['raw'] || ! $results['raw'] ) {
				throw new Exception( __( 'API Key or Account type is invalid.', 'woongkir' ), 1 );
			}

			foreach ( $results as $result ) {
				if ( is_wp_error( $result ) ) {
					throw new Exception( $result->get_error_message(), 1 );
				}
			}
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
	 * @param string $value Input field current value.
	 * @throws Exception If field value is not valid.
	 * @return string
	 */
	public function validate_origin_field( $key, $value ) {
		if ( empty( $value ) ) {
			// Translators: Shipping origin location type.
			throw new Exception( wp_sprintf( __( 'Shipping origin %s field is required.', 'woongkir' ), str_replace( 'origin_', '', $key ) ) );
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
	public function validate_select_couriers_field( $key, $value ) {
		if ( is_string( $value ) ) {
			$value = array_map( 'trim', explode( ',', $value ) );
		}

		// Format the value as associative array courier => services.
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

		$field = $this->instance_form_fields[ $key ];

		if ( ! $value ) {
			// Translators: %1$s Setting field label.
			throw new Exception( wp_sprintf( __( '%1$s cannot be empty.', 'woongkir' ), $field['title'] ) );
		}

		$account = $this->api->get_account( $this->posted_field_value( 'account_type' ) );

		if ( ! $account ) {
			throw new Exception( __( 'Account type field is invalid.', 'woongkir' ) );
		}

		foreach ( $value as $zone => $couriers ) {
			if ( ! $couriers ) {
				continue;
			}

			if ( count( $couriers ) > 1 && ! $account->feature_enable( 'multiple_couriers' ) ) {
				// Translators: %1$s Setting field label, %2$s Account label.
				throw new Exception( wp_sprintf( __( '%1$s: Account type %2$s is not allowed to select multiple couriers.', 'woongkir' ), $field['title'], $account->get_label( 'label' ) ) );
			}

			if ( array_diff_key( $couriers, $this->api->get_couriers( $zone, $account->get_type() ) ) ) {
				// Translators: %1$s Setting field label, %2$s Account label, %3$s Couriers name.
				throw new Exception( wp_sprintf( __( '%1$s Shipping: Account type %2$s is not allowed to select courier %3$s.', 'woongkir' ), $field['title'], $account->get_label( 'label' ), strtoupper( implode( ', ', array_keys( $not_allowed ) ) ) ) );
			}
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
			$field   = $this->instance_form_fields[ $key ];
			$account = $this->api->get_account( $this->posted_field_value( 'account_type' ) );

			if ( ! $account ) {
				throw new Exception( __( 'Account type field is invalid.', 'woongkir' ) );
			}

			if ( ! $account->feature_enable( 'multiple_couriers' ) && count( $value ) > 1 ) {
				// Translators: %1$s Shipping zone name, %2$s Account label.
				throw new Exception( wp_sprintf( __( '%1$s Shipping: Account type %2$s is not allowed to select multiple couriers.', 'woongkir' ), $field['title'], $account->get_label( 'label' ) ) );
			}

			$not_allowed = array_diff_key( $value, $this->api->get_couriers( $key, $account->get_type() ) );

			if ( ! empty( $not_allowed ) ) {
				// Translators: %1$s Shipping zone name, %2$s Account label, %3$s Couriers name.
				throw new Exception( wp_sprintf( __( '%1$s Shipping: Account type %2$s is not allowed to select courier %3$s.', 'woongkir' ), $field['title'], $account->get_label( 'label' ), strtoupper( implode( ', ', array_keys( $not_allowed ) ) ) ) );
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
			$api_request_params = $this->calculate_shipping_api_request_params( $package );

			if ( is_wp_error( $api_request_params ) ) {
				throw new Exception( $api_request_params->get_error_message() );
			}

			$cache_key = $this->generate_cache_key( $api_request_params );

			if ( $this->is_enable_cache() ) {
				$this->show_debug(
					wp_json_encode(
						array(
							'calculate_shipping.$cache_key' => $cache_key,
						)
					)
				);
			}

			$results = $this->is_enable_cache() ? get_transient( $cache_key ) : false;

			if ( false === $results ) {
				if ( 'domestic' === $api_request_params['zone'] ) {
					$results = $this->api->calculate_shipping( $api_request_params );
				} else {
					$results = $this->api->calculate_shipping_international( $api_request_params );
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

				if ( $results && ! is_wp_error( $results ) && $this->is_enable_cache() ) {
					set_transient( $cache_key, $results, HOUR_IN_SECONDS ); // Store response data for 1 hour.
				}
			}

			if ( ! is_wp_error( $results ) ) {
				$this->show_debug(
					wp_json_encode(
						array(
							'calculate_shipping.$results' => $results,
						)
					)
				);
			}

			$allowed_services = isset( $this->{$api_request_params['zone']} ) ? $this->{$api_request_params['zone']} : array();

			$this->show_debug(
				wp_json_encode(
					array(
						'calculate_shipping.$allowed_services' => $allowed_services,
					)
				)
			);

			if ( is_wp_error( $results ) ) {
				throw new Exception( $results->get_error_message() );
			}

			if ( ! $results ) {
				throw new Exception( __( 'No couriers data found', 'woongkir' ) );
			}

			if ( ! is_array( $results ) ) {
				// translators: %s Encoded data response.
				throw new Exception( wp_sprintf( __( 'Couriers data is invalid: %s', 'woongkir' ), wp_json_encode( $results ) ) );
			}

			$couriers = array();

			if ( 'no' !== $this->sort_shipping && is_callable( array( $this, 'sort_results_by_' . $this->sort_shipping ) ) ) {
				usort( $results['parsed'], array( $this, 'sort_results_by_' . $this->sort_shipping ) );
			}

			foreach ( $results['parsed'] as $result ) {
				if ( ! isset( $couriers[ $result['courier'] ] ) ) {
					$couriers[ $result['courier'] ] = array();
				}

				$couriers[ $result['courier'] ][] = $result;
			}

			if ( in_array( $this->sort_shipping, array( 'name', 'name_desc' ), true ) ) {
				foreach ( $couriers as $courier => $services ) {
					usort( $services, array( $this, 'sort_results_by_cost' ) );

					$couriers[ $courier ] = $services;
				}
			}

			foreach ( $couriers as $courier => $services ) {
				foreach ( $services as $result ) {
					if ( ! isset( $allowed_services[ $courier ] ) ) {
						continue;
					}

					if ( ! in_array( $result['service'], $allowed_services[ $courier ], true ) ) {
						continue;
					}

					$rate_label = wp_sprintf( '%s - %s', strtoupper( $courier ), $result['service'] );

					if ( 'yes' === $this->show_eta && $result['etd'] ) {
						$rate_label = wp_sprintf( '%1$s (%2$s)', $rate_label, $result['etd'] );
					}

					/**
					 * Filter the shipping rate label.
					 *
					 * @since 1.2.12
					 *
					 * @param string                   $rate_label The default shipping rate label.
					 * @param bool                     $result     Shipping rate result data.
					 * @param array                    $package    Current order package data.
					 * @param Woongkir_Shipping_Method $object     Current class object.
					 *
					 * @return string
					 */
					$rate_label = apply_filters( 'woongkir_shipping_rate_label', $rate_label, $result, $package, $this );

					$this->add_rate(
						array(
							'id'        => $this->get_rate_id( $courier . ':' . $result['service'] ),
							'label'     => $rate_label,
							'cost'      => $result['cost'],
							'meta_data' => array(
								'_woongkir_data' => $result,
							),
						)
					);
				}
			}
		} catch ( Exception $e ) {
			$this->show_debug( $e->getMessage() );
		}
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

		$domestic = 'ID' === $shipping_address['country'];

		if ( $domestic ) {
			$account = $this->api->get_account( $this->account_type );

			return array(
				'origin'     => $account && $account->feature_enable( 'subdistrict' ) ? $this->origin_subdistrict : $this->origin_city,
				'originType' => $account && $account->feature_enable( 'subdistrict' ) ? 'subdistrict' : 'city',
			);
		}

		return array(
			'origin' => $this->origin_city,
		);
	}

	/**
	 * Populate API request parameters.
	 *
	 * @since 1.2.12
	 *
	 * @param array $package Current order package data.
	 *
	 * @throws Exception If the request parameters is incomplete.
	 *
	 * @return array|WP_Error
	 */
	private function calculate_shipping_api_request_params( $package = array() ) {
		try {
			$domestic = isset( $package['destination']['country'] ) && 'ID' === $package['destination']['country'];

			/**
			 * Shipping origin info.
			 *
			 * @since 1.2.9
			 *
			 * @param array $origin_info Original origin info.
			 * @param array $package Current order package data.
			 *
			 * @return array
			 */
			$origin_info = apply_filters( 'woongkir_shipping_origin_info', $this->get_origin_info( $package['destination'] ), $package );

			$this->show_debug(
				wp_json_encode(
					array(
						'api_request_params.$origin_info' => $origin_info,
					)
				)
			);

			if ( empty( $origin_info ) ) {
				throw new Exception( __( 'Shipping origin info is empty or invalid', 'woongkir' ) );
			}

			/**
			 * Shipping destination info.
			 *
			 * @since 1.2.9
			 *
			 * @param array $destination_info Original destination info.
			 * @param array $package Current order package data.
			 *
			 * @return array
			 */
			$destination_info = apply_filters( 'woongkir_shipping_destination_info', $this->get_destination_info( $package['destination'] ), $package );

			$this->show_debug(
				wp_json_encode(
					array(
						'api_request_params.$destination_info' => $destination_info,
					)
				)
			);

			if ( ! $destination_info || ! array_filter( $destination_info ) ) {
				throw new Exception( __( 'Shipping destination info is empty or invalid', 'woongkir' ) );
			}

			/**
			 * Shipping dimension & weight info.
			 *
			 * @since 1.2.9
			 *
			 * @param array $dimension_weight Original dimension & weight info.
			 * @param array $package Current order package data.
			 *
			 * @return array
			 */
			$dimension_weight = apply_filters( 'woongkir_shipping_dimension_weight', $this->get_dimension_weight( $package['contents'] ), $package );

			$this->show_debug(
				wp_json_encode(
					array(
						'api_request_params.$dimension_weight' => $dimension_weight,
					)
				)
			);

			if ( ! $dimension_weight || ! array_filter( $dimension_weight ) ) {
				throw new Exception( __( 'Cart weight or dimension is empty or invalid', 'woongkir' ) );
			}

			$courier = $domestic ? array_keys( (array) $this->domestic ) : array_keys( (array) $this->international );

			$this->show_debug(
				wp_json_encode(
					array(
						'api_request_params.$courier' => $courier,
					)
				)
			);

			if ( ! $courier || ! array_filter( $courier ) ) {
				throw new Exception( __( 'No couriers selected', 'woongkir' ) );
			}

			return array_merge(
				$origin_info,
				$destination_info,
				$dimension_weight,
				array(
					'courier' => $courier,
					'zone'    => $domestic ? 'domestic' : 'international',
				)
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
		if ( empty( $shipping_address['country'] ) ) {
			return false;
		}

		$domestic = 'ID' === $shipping_address['country'];

		if ( ! $domestic ) {
			$country = woongkir_get_json_data(
				'country',
				array(
					'country_code' => $shipping_address['country'],
				)
			);

			if ( ! $country ) {
				return false;
			}

			return array(
				'destination' => $country['country_id'],
			);
		}

		// Bail early when the state or city info is empty.
		if ( empty( $shipping_address['country'] ) || empty( $shipping_address['city'] ) ) {
			return false;
		}

		// Get province ID data.
		$province = woongkir_get_json_data(
			'province',
			array(
				'code' => $shipping_address['state'],
			)
		);

		if ( ! $province || ! isset( $province['province_id'] ) ) {
			return false;
		}

		// Get city ID data.
		$city_parts = explode( ' ', $shipping_address['city'] );
		$city_type  = $city_parts[0];
		$city_name  = implode( ' ', array_slice( $city_parts, 1 ) );

		$city = woongkir_get_json_data(
			'city',
			array(
				'type'        => $city_type,
				'city_name'   => $city_name,
				'province_id' => $province['province_id'],
			)
		);

		if ( ! $city || ! isset( $city['city_id'] ) ) {
			return false;
		}

		// Get current API account.
		$account = $this->api->get_account( $this->account_type );

		if ( $account && $account->feature_enable( 'subdistrict' ) && ! empty( $shipping_address['address_2'] ) ) {
			// Get subdistrict ID data.
			$subdistrict = woongkir_get_json_data(
				'subdistrict',
				array(
					'subdistrict_name' => $shipping_address['address_2'],
					'city_id'          => $city['city_id'],
					'province_id'      => $province['province_id'],
				)
			);

			if ( $subdistrict && isset( $subdistrict['subdistrict_id'] ) ) {
				return array(
					'destination'     => $subdistrict['subdistrict_id'],
					'destinationType' => 'subdistrict',
				);
			}
		}

		return array(
			'destination'     => $city['city_id'],
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
		$account = $this->api->get_account( $this->account_type );

		if ( $account && $account->feature_enable( 'volumetric' ) ) {
			$width  = wc_get_dimension( max( $width ), 'cm' );
			$length = wc_get_dimension( max( $length ), 'cm' );
			$height = wc_get_dimension( array_sum( $height ), 'cm' );

			$data['width']  = $width;
			$data['length'] = $length;
			$data['height'] = $height;

			if ( 'yes' === $this->volumetric_calculator && $this->volumetric_divider ) {
				$data['weight'] = max( $data['weight'], $this->convert_volumetric( $width, $length, $height ) );
			}
		}

		// Set the package weight to based on base_weight setting value.
		if ( absint( $this->base_weight ) && $data['weight'] < absint( $this->base_weight ) ) {
			$data['weight'] = absint( $this->base_weight );
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
		return ceil( ( ( $width * $length * $height ) / $this->volumetric_divider ) * 1000 );
	}

	/**
	 * Check wether api response to be cached
	 *
	 * @return boolean
	 * @since 1.2.12
	 */
	private function is_enable_cache() {
		return defined( 'WOONGKIR_ENABLE_CACHE' ) ? WOONGKIR_ENABLE_CACHE : true;
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
		$cache_keys = array();

		foreach ( array_keys( $this->instance_form_fields ) as $cache_key ) {
			$cache_keys[ $cache_key ] = $this->get_option( $cache_key );
		}

		return $this->id . '_' . $this->instance_id . '_' . WC()->cart->get_cart_hash() . '_' . md5(
			wp_json_encode(
				array_merge(
					$api_request_params,
					$cache_keys
				)
			)
		);
	}

	/**
	 * Sort domestic couriers list
	 *
	 * @param array $a Value to compare.
	 * @param array $b Value to compare.
	 * @return bool
	 */
	protected function sort_couriers_list_domestic( $a, $b ) {
		$priority = array();

		$letter_index = range( 'a', 'z' );
		$a_code_index = is_numeric( $a['code'][0] ) ? $a['code'][0] : ( array_search( strtolower( $a['code'][0] ), $letter_index, true ) + 10 );
		$b_code_index = is_numeric( $b['code'][0] ) ? $b['code'][0] : ( array_search( strtolower( $b['code'][0] ), $letter_index, true ) + 10 );

		if ( empty( $this->domestic ) ) {
			if ( $a_code_index === $b_code_index ) {
				return 0;
			}

			return ( $a_code_index > $b_code_index ) ? 1 : -1;
		}

		foreach ( array_keys( $this->domestic ) as $index => $courier ) {
			$priority[ $courier ] = $index;
		}

		$al = isset( $priority[ $a['code'] ] ) ? $priority[ $a['code'] ] : ( count( $this->domestic ) + $a_code_index );
		$bl = isset( $priority[ $b['code'] ] ) ? $priority[ $b['code'] ] : ( count( $this->domestic ) + $b_code_index );

		if ( $al === $bl ) {
			return 0;
		}

		return ( $al > $bl ) ? 1 : -1;
	}

	/**
	 * Sort international couriers list
	 *
	 * @param array $a Value to compare.
	 * @param array $b Value to compare.
	 * @return bool
	 */
	protected function sort_couriers_list_international( $a, $b ) {
		$priority = array();

		$letter_index = range( 'a', 'z' );
		$a_code_index = is_numeric( $a['code'][0] ) ? $a['code'][0] : ( array_search( strtolower( $a['code'][0] ), $letter_index, true ) + 10 );
		$b_code_index = is_numeric( $b['code'][0] ) ? $b['code'][0] : ( array_search( strtolower( $b['code'][0] ), $letter_index, true ) + 10 );

		if ( empty( $this->international ) ) {
			if ( $a_code_index === $b_code_index ) {
				return 0;
			}

			return ( $a_code_index > $b_code_index ) ? 1 : -1;
		}

		foreach ( array_keys( $this->international ) as $index => $courier ) {
			$priority[ $courier ] = $index;
		}

		$al = isset( $priority[ $a['code'] ] ) ? $priority[ $a['code'] ] : ( count( $this->international ) + $a_code_index );
		$bl = isset( $priority[ $b['code'] ] ) ? $priority[ $b['code'] ] : ( count( $this->international ) + $b_code_index );

		if ( $al === $bl ) {
			return 0;
		}

		return ( $al > $bl ) ? 1 : -1;
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
		$a_name = isset( $a['courier'] ) ? strtolower( $a['courier'] ) : '';
		$b_name = isset( $b['courier'] ) ? strtolower( $b['courier'] ) : '';

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
		$a_name = isset( $a['courier'] ) ? strtolower( $a['courier'] ) : '';
		$b_name = isset( $b['courier'] ) ? strtolower( $b['courier'] ) : '';

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
		$debug_mode = 'yes' === get_option( 'woocommerce_shipping_debug_mode', 'no' );

		if ( ! $debug_mode || ! current_user_can( 'manage_options' ) || wc_has_notice( $message ) || ( defined( 'WC_DOING_AJAX' ) && WC_DOING_AJAX ) ) {
			return;
		}

		wc_add_notice( ( $this->id . ' : ' . $message ), $notice_type );
	}
}
