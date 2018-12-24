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
				var prefix = form.prefix;

				if (!prefix && $wrapper.find('#billing_country').length) {
					prefix = 'billing';
				}

				if (!prefix && $wrapper.find('#shipping_country').length) {
					prefix = 'shipping';
				}

				if (prefix) {
					var country = $('#' + prefix + '_country').val();
					if (country && country.length) {
						if (country === 'ID') {
							woongkirFrontend.modifyForm(prefix);
						} else {
							woongkirFrontend.restoreForm(prefix, country);
						}
					}
				}
			}
		});
	},
	modifyForm: function (prefix) {
		var addressParams = woongkirFrontend.getAddressParams('ID');
		_.each(['address_2', 'city'], function (field) {
			var $field = $('#' + prefix + '_' + field);
			if (!$field || !$field.length) {
				var $cloneFieldWrap = $('#' + prefix + '_postcode_field');

				if ($cloneFieldWrap && $cloneFieldWrap.length) {
					var $fieldWrap = $cloneFieldWrap.clone().attr({
						id: prefix + '_' + field + '_field'
					});

					$fieldWrap.find('input').attr({
						id: prefix + '_' + field,
						name: prefix + '_' + field,
						value: $('#' + prefix + '_' + field + '_dummy').val(),
					});

					$cloneFieldWrap.before($fieldWrap);

					$field = $fieldWrap.find('input');
				}
			}

			if ($field && $field.length) {
				setTimeout(function () {
					var $fieldLabel = $('#' + prefix + '_' + field + '_field').find('label');
					var labelClass = _.has(addressParams[field], 'label_class') ? addressParams[field].label_class : [];
					if ($fieldLabel && $fieldLabel.length) {
						var fieldLabelHtml = $fieldLabel.html().replace(addressParams[field].label, woongkir_params.text.label[field]);
						$fieldLabel.html(fieldLabelHtml);
						if (_.indexOf(labelClass, 'screen-reader-text') !== -1) {
							$fieldLabel.removeClass('screen-reader-text');
						}
					}
					var $fieldWrapSorted = $('#' + prefix + '_' + field + '_field').detach();
					$fieldWrapSorted.insertAfter($('#' + prefix + '_state_field'));
				}, 100);
			}
		});

		$(document.body).off('change', '#' + prefix + '_state');
		$(document.body).on('change', '#' + prefix + '_state', function (e) {
			woongkirFrontend.modifyFormCity(prefix);
		});

		$(document.body).off('change', '#' + prefix + '_city');
		$(document.body).on('change', '#' + prefix + '_city', function (e) {
			woongkirFrontend.modifyFormSubdistrict(prefix);
		});

		$(document.body).off('change', '#' + prefix + '_address_2');
		$(document.body).on('change', '#' + prefix + '_address_2', function (e) {
			if ($(document.body).hasClass('woocommerce-checkout')) {
				$(document.body).trigger('update_checkout');
			}
		});

		$('#' + prefix + '_state').trigger('change');
	},
	modifyFormCity: function (prefix) {
		var $provinceField = $('#' + prefix + '_state');
		var provinceSelected = $provinceField.val();

		var placeholder = woongkir_params.text.placeholder.city;
		var cityOptions = [{
			id: '',
			text: placeholder,
		}];
		var citySelected = $('#' + prefix + '_city').val();

		var provinceData = woongkirLocation.getProvince({ code: provinceSelected });

		if (provinceData) {
			var cityData = woongkirLocation.getCity({ province_id: provinceData.province_id }, 'filter');
			if (cityData) {
				for (var i = 0; i < cityData.length; i++) {
					var cityName = cityData[i].type + ' ' + cityData[i].city_name;

					cityOptions.push({
						id: cityName,
						text: cityName,
						selected: citySelected === cityName
					});
				}
			}
		}

		woongkirFrontend.convertInputToSelect(prefix, 'city', cityOptions, placeholder);
	},
	modifyFormSubdistrict: function (prefix) {
		var $provinceField = $('#' + prefix + '_state');
		var provinceSelected = $provinceField.val();

		var $cityField = $('#' + prefix + '_city');
		var citySelected = $cityField.val();

		var placeholder = woongkir_params.text.placeholder.address_2;
		var subdistrictOptions = [{
			id: '',
			text: placeholder,
		}];
		var subdistrictSelected = $('#' + prefix + '_address_2').val();



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
						subdistrictOptions.push({
							id: subdistrictData[i].subdistrict_name,
							text: subdistrictData[i].subdistrict_name,
							selected: subdistrictSelected === subdistrictData[i].subdistrict_name,
						});
					}
				}
			}
		}

		woongkirFrontend.convertInputToSelect(prefix, 'address_2', subdistrictOptions, placeholder);
	},
	restoreForm: function (prefix, country) {
		_.each(['city', 'address_2'], function (field) {
			woongkirFrontend.convertSelectToInput(prefix, field, country);

			if (prefix === 'calc_shipping' && field === 'address_2') {
				$('#calc_shipping_address_2_field').remove();
			}
		});
	},
	convertInputToSelect: function (prefix, field, options, placeholder) {
		var fieldKey = prefix + '_' + field;
		var $field = $('#' + fieldKey);
		if ($field && $field.length) {
			if (!$field.is('select')) {
				$field.replaceWith($('<select></select>').attr({
					id: fieldKey,
					name: fieldKey,
				}));
			}

			$field.empty();

			$field = $('#' + fieldKey);

			_.each(options, function (option) {
				$field.append($('<option></option>').attr({
					value: option.id
				}).text(option.text).prop('selected', option.selected));
			});

			$field.select2({
				width: '100%',
				placeholder: placeholder
			}).trigger('change');
		}
	},
	convertSelectToInput: function (prefix, field, country) {
		var addressParams = woongkirFrontend.getAddressParams(country);
		var fieldKey = prefix + '_' + field;
		var $field = $('#' + fieldKey);
		if ($field && $field.length) {
			if ($field.is('select')) {
				var fieldParam = _.has(addressParams, field) ? addressParams[field] : {
					autocomplete: undefined,
					required: false,
					label_class: [],
				};

				$field.select2('destroy');

				$field.replaceWith($('<input>').attr(_.extend(
					{
						autocomplete: fieldParam.autocomplete,
						required: fieldParam.required,
					}, {
						id: fieldKey,
						name: fieldKey,
					}
				)));

				$field = $('#' + fieldKey);
				$field.addClass('input-text ');

				var $fieldLabel = $('#' + prefix + '_' + field + '_field').find('label');
				if ($fieldLabel && $fieldLabel.length) {
					if (_.indexOf(fieldParam.label_class, 'screen-reader-text') !== -1) {
						$fieldLabel.addClass('screen-reader-text');
					}
				}
			}
		}
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
