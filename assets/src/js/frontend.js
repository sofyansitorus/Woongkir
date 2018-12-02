var woongkirFrontend = {
	addressParams: {},
	init: function () {
		// wc_address_i18n_params is required to continue, ensure the object exists
		if (typeof wc_address_i18n_params === 'undefined') {
			return false;
		}

		woongkirFrontend.bindEvents();
		woongkirFrontend.toggleForm();
	},
	bindEvents: function () {
		$(document.body).off('country_to_state_changed', woongkirFrontend.toggleForm);
		$(document.body).on('country_to_state_changed', woongkirFrontend.toggleForm);
	},
	toggleForm: function () {
		_.each(woongkirFrontend.getForms(), function (form) {
			var $wrapper = $(form.wrapper);

			if ($wrapper && $wrapper.length) {
				var formPrefix = form.prefix;

				if (!formPrefix && $('#billing_country').length) {
					formPrefix = 'billing';
				}

				if (!formPrefix && $('#shipping_country').length) {
					formPrefix = 'shipping';
				}

				if (formPrefix) {
					var country = $('#' + formPrefix + '_country').val();
					if (country && country.length) {
						if (country === 'ID') {
							woongkirFrontend.modifyForm(formPrefix, $wrapper);
						} else {
							woongkirFrontend.restoreForm(formPrefix, $wrapper, country);
						}
					}
				}
			}
		});
	},
	modifyForm: function (formPrefix, $wrapper) {
		var addressParams = woongkirFrontend.getAddressParams('ID');
		_.each(['address_2', 'city'], function (field) {
			var $field = $wrapper.find('#' + formPrefix + '_' + field);
			if (!$field || !$field.length) {
				var $cloneFieldWrap = $wrapper.find('#' + formPrefix + '_postcode_field');

				if ($cloneFieldWrap && $cloneFieldWrap.length) {
					var $fieldWrap = $cloneFieldWrap.clone().attr({
						id: formPrefix + '_' + field + '_field'
					});

					$fieldWrap.find('input').attr({
						id: formPrefix + '_' + field,
						name: formPrefix + '_' + field,
						value: $('#' + formPrefix + '_' + field + '_dummy').val(),
					});

					$cloneFieldWrap.before($fieldWrap);

					$field = $fieldWrap.find('input');
				}
			}

			if ($field && $field.length) {
				$field.selectWoo({
					placeholder: woongkir_params.text[field],
					width: '100%'
				}).addClass('woongkir-select2');

				setTimeout(() => {
					var $fieldLabel = $('#' + formPrefix + '_' + field + '_field').find('label');
					var labelClass = _.has(addressParams[field], 'label_class') ? addressParams[field].label_class : [];
					if ($fieldLabel && $fieldLabel.length) {
						var fieldLabelHtml = $fieldLabel.html().replace(addressParams[field].label, woongkir_params.label[field]);
						$fieldLabel.html(fieldLabelHtml);
						if (_.indexOf(labelClass, 'screen-reader-text') !== -1) {
							$fieldLabel.removeClass('screen-reader-text');
						}
					}
					var $fieldWrapSorted = $('#' + formPrefix + '_' + field + '_field').detach();
					$fieldWrapSorted.insertAfter($('#' + formPrefix + '_state_field'));
				}, 100);
			}
		});

		$(document.body).off('change', '#' + formPrefix + '_state');
		$(document.body).on('change', '#' + formPrefix + '_state', function (e) {
			woongkirFrontend.modifyFormCity(formPrefix);
		});

		$(document.body).off('change', '#' + formPrefix + '_city');
		$(document.body).on('change', '#' + formPrefix + '_city', function (e) {
			woongkirFrontend.modifyFormSubdistrict(formPrefix);
		});

		$('#' + formPrefix + '_state').trigger('change');
	},
	modifyFormCity: function (formPrefix) {
		var $provinceField = $('#' + formPrefix + '_state');
		var provinceSelected = $provinceField.val();

		var $cityField = $('#' + formPrefix + '_city');
		var citySelected = $cityField.val();
		var cityMatch = '';
		var cityParam = {
			data: [{
				id: '',
				text: woongkir_params.text.select_city,
			}],
		};

		var provinceData = woongkirLocation.getProvince({ code: provinceSelected });

		if (provinceData) {
			var cityData = woongkirLocation.getCity({ province_id: provinceData.province_id }, 'filter');
			if (cityData) {
				for (var i = 0; i < cityData.length; i++) {
					var cityName = cityData[i].type + ' ' + cityData[i].city_name;

					cityParam.data.push({
						id: cityName,
						text: cityName,
					});

					if (citySelected === cityName) {
						cityMatch = cityName;
					}
				}
			}
		}

		$cityField.selectWoo(cityParam).val(cityMatch).trigger('change');
	},
	modifyFormSubdistrict: function (formPrefix) {
		var $provinceField = $('#' + formPrefix + '_state');
		var provinceSelected = $provinceField.val();

		var $cityField = $('#' + formPrefix + '_city');
		var citySelected = $cityField.val();

		var $subdistrictField = $('#' + formPrefix + '_address_2');
		var subdistrictSelected = $subdistrictField.val();
		var subdistrictMatch = '';

		var subdistrictParam = {
			data: [{
				id: '',
				text: woongkir_params.text.select_subdistrict,
			}],
		};

		var provinceData = woongkirLocation.getProvince({ code: provinceSelected });

		if (provinceData) {
			var cityType = citySelected.split(' ')[0];
			var cityName = citySelected.split(' ').splice(1).join(' ');
			var cityData = woongkirLocation.getCity({
				type: cityType,
				city_name: cityName,
				province_id: provinceData.province_id,
			});

			if (cityData) {
				var subdistrictData = woongkirLocation.getSubdistrict({
					province_id: provinceData.province_id,
					city_id: cityData.city_id
				}, 'filter');

				if (subdistrictData) {
					for (var i = 0; i < subdistrictData.length; i++) {
						subdistrictParam.data.push({
							id: subdistrictData[i].subdistrict_name,
							text: subdistrictData[i].subdistrict_name,
						});

						if (subdistrictSelected && subdistrictSelected === subdistrictData[i].subdistrict_name) {
							subdistrictMatch = subdistrictData[i].subdistrict_name;
						}
					}
				}
			}
		}

		$subdistrictField.selectWoo(subdistrictParam).val(subdistrictMatch).trigger('change');
	},
	restoreForm: function (formPrefix, $wrapper, country) {
		var addressParams = woongkirFrontend.getAddressParams(country);
		_.each(['city', 'address_2'], function (field) {
			var $field = $wrapper.find('#' + formPrefix + '_' + field);
			if ($field && $field.length) {
				if ($field.hasClass('woongkir-select2')) {
					$field.selectWoo('destroy').removeClass('woongkir-select2');
				}

				var $fieldLabel = $('#' + formPrefix + '_' + field + '_field').find('label');
				var labelClass = _.has(addressParams[field], 'label_class') ? addressParams[field].label_class : [];
				if ($fieldLabel && $fieldLabel.length) {
					if (_.indexOf(labelClass, 'screen-reader-text') !== -1) {
						$fieldLabel.addClass('screen-reader-text');
					}
				}
			}

			if (formPrefix === 'calc_shipping' && field === 'address_2') {
				$('#calc_shipping_address_2_field').remove();
			}
		});

		$(document.body).off('change', '#' + formPrefix + '_state');
		$(document.body).off('change', '#' + formPrefix + '_city');
	},
	getForms: function () {
		return [{
			wrapper: '.woocommerce-billing-fields__field-wrapper',
			prefix: 'billing'
		}, {
			wrapper: '.woocommerce-shipping-fields__field-wrapper',
			prefix: 'shipping'
		}, {
			wrapper: '.shipping-calculator-form',
			prefix: 'calc_shipping'
		}, {
			wrapper: '.woocommerce-address-fields__field-wrapper',
			prefix: false
		}];
	},
	getAddressParams: function (country) {
		var addressParams = $.parseJSON(wc_address_i18n_params.locale.replace(/&quot;/g, '"'));

		var addressParamsDefault = addressParams['default'];
		var addressParamsCountry = _.has('addressParams', country) ? addressParams[country] : {};

		return _.extend({}, addressParamsDefault, addressParamsCountry);
	}
}

$(document).ready(woongkirFrontend.init);
