var woongkirFrontend = {
	init: function () {
		$(document.body).on('country_to_state_changed', function () {
			var fields = woongkirFrontend.getFields();

			$('select.country_to_state, input.country_to_state').each(function () {
				var $countryField = $(this);
				var fieldPrefix = $countryField.attr('id').replace('_country', '');
				var selectedCountry = $countryField.val();

				_.each(fields, function (field) {
					if (field.callback) {
						$('#' + fieldPrefix + '_' + field.suffix).off('change', field.callback);
					}
				});

				if (selectedCountry === 'ID') {
					woongkirFrontend.modifyForm(fieldPrefix);

					_.each(fields, function (field) {
						if (field.callback) {
							$('#' + fieldPrefix + '_' + field.suffix).on('change', field.callback);
						}
					});

					_.each(fields, function (field) {
						if (field.triggerEvent) {
							$('#' + fieldPrefix + '_' + field.suffix).trigger(field.triggerEvent);
						}
					});
				} else {
					woongkirFrontend.restoreForm(fieldPrefix);
				}
			});
		});
	},
	onChangeStateField: function (e) {
		var $provinceField = $(e.currentTarget);
		var fieldPrefix = $provinceField.attr('id').replace('_state', '');
		var provinceSelected = $provinceField.val();
		var cityOptions = [];

		var provinceData = woongkirLocation.getProvince({ code: provinceSelected });

		if (provinceData) {
			var cityData = woongkirLocation.getCity({ province_id: provinceData.province_id }, 'filter');
			if (cityData) {
				for (var i = 0; i < cityData.length; i++) {
					var cityName = cityData[i].type + ' ' + cityData[i].city_name;

					cityOptions.push({
						id: cityName,
						text: cityName,
					});
				}
			}
		}

		var citySelected = $('#' + fieldPrefix + '_city').val();

		$('#' + fieldPrefix + '_city').empty().selectWoo({
			width: '100%',
			data: cityOptions,
			placeholder: woongkirFrontend.getFields({ suffix: 'city' }).placeholder,
		}).val(citySelected).trigger('change');
	},
	onChangeCityField: function (e) {
		var $cityField = $(e.currentTarget);
		var fieldPrefix = $cityField.attr('id').replace('_city', '');
		var $provinceField = $('#' + fieldPrefix + '_state');
		var provinceSelected = $provinceField.val();
		var citySelected = $cityField.val();
		var subdistrictOptions = [];

		var provinceData = woongkirLocation.getProvince({ code: provinceSelected });

		if (provinceData && citySelected) {
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
						});
					}
				}
			}
		}

		var subdistrictSelected = $('#' + fieldPrefix + '_address_2').val();

		$('#' + fieldPrefix + '_address_2').empty().selectWoo({
			width: '100%',
			data: subdistrictOptions,
			placeholder: woongkirFrontend.getFields({ suffix: 'address_2' }).placeholder,
		}).val(subdistrictSelected).trigger('change');
	},
	onChangeSubdistrictField: function (e) {
		var isUpdateCheckout = function () {
			var shipToDifferentAddress = $('#ship-to-different-address-checkbox').is(':checked');
			var fieldPrefix = $(e.currentTarget).attr('id').replace('_address_2', '');

			if (fieldPrefix === 'billing' && !shipToDifferentAddress) {
				return true;
			}

			if (fieldPrefix === 'shipping' && shipToDifferentAddress) {
				return true;
			}

			return false;

		};

		if (isUpdateCheckout()) {
			$(document.body).trigger('update_checkout');
		}
	},
	modifyForm: function (fieldPrefix) {
		_.each(woongkirFrontend.getFields(), function (field) {
			if (field.modifyForm) {
				var $field = $('#' + fieldPrefix + '_' + field.suffix);
				if (!$field || !$field.length) {
					var $cloneFieldWrap = $('#' + fieldPrefix + '_postcode_field');

					if ($cloneFieldWrap && $cloneFieldWrap.length) {
						var $fieldWrap = $cloneFieldWrap.clone().attr({
							id: fieldPrefix + '_' + field.suffix + '_field'
						});

						$fieldWrap.find('input').attr({
							'id': fieldPrefix + '_' + field.suffix,
							'name': fieldPrefix + '_' + field.suffix,
							'value': $('#woongkir_' + fieldPrefix + '_' + field.suffix).val(),
							'placeholder': field.placeholder || '',
							'data-placeholder': field.placeholder || '',
						});

						$cloneFieldWrap.before($fieldWrap);

						$field = $fieldWrap.find('input');
					}
				}

				if ($field && $field.length) {
					if (!$field.is('select')) {
						var fieldValue = $field.val();
						var fieldAttrs = _.omit(woongkirFrontend.getFieldAttributes($field), ['type']);

						$field.replaceWith($('<select></select>').attr(fieldAttrs).append($('<option value="' + fieldValue + '">' + fieldValue + '</option>')));
					}

					$field = $('#' + fieldPrefix + '_' + field.suffix);
					$field.removeClass('input-text');

					$field.selectWoo({
						width: '100%',
					});
				}
			}
		});
	},
	restoreForm: function (fieldPrefix) {
		_.each(woongkirFrontend.getFields(), function (field) {
			if (field.modifyForm) {
				var $field = $('#' + fieldPrefix + '_' + field.suffix);

				if ($field.is('select')) {
					$field.selectWoo('destroy');

					var fieldAttrs = _.extend(woongkirFrontend.getFieldAttributes($field), { type: 'text' });

					$field.replaceWith($('<input>').attr(fieldAttrs));

					$field = $('#' + fieldPrefix + '_' + field.suffix);
					$field.addClass('input-text');

					if (fieldPrefix === 'calc_shipping' && field.suffix === 'address_2') {
						$field.closest('.form-row').remove();
					}
				}
			}
		});
	},
	getFields: function (search) {
		var fields = [{
			suffix: 'state',
			callback: woongkirFrontend.onChangeStateField,
			triggerEvent: 'change',
		},
		{
			suffix: 'city',
			callback: woongkirFrontend.onChangeCityField,
			modifyForm: true,
			placeholder: woongkir_params.text.placeholder.city,
		},
		{
			suffix: 'address_2',
			callback: woongkirFrontend.onChangeSubdistrictField,
			modifyForm: true,
			placeholder: woongkir_params.text.placeholder.address_2,
		}];

		if (search) {
			return _.find(fields, search);
		}

		return fields;
	},
	getFieldAttributes: function ($node) {
		var attrs = {};

		_.each($node[0].attributes, function (attribute) {
			attrs[attribute.name] = attribute.value;
		});

		return attrs;
	},
}

$(document.body).on('wc_address_i18n_ready', function () {
	woongkirFrontend.init();

	setTimeout(function () {
		$(document.body).trigger('country_to_state_changed');
	}, 100);
});

