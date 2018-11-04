
var woongkirCountryFields = {
	billing_country: '.woocommerce-billing-fields',
	shipping_country: '.woocommerce-shipping-fields',
	calc_shipping_country: '.woocommerce-shipping-calculator',
};

var woongkirStateFields = [
	'#billing_state',
	'#shipping_state',
	'#calc_shipping_state',
];

var woongkirCityFields = [
	'#billing_city',
	'#shipping_city',
	'#calc_shipping_city',
];

var woongkirDynamicFields = [
	'#billing_city',
	'#shipping_city',
	'#calc_shipping_city',
	'#billing_address_2',
	'#shipping_address_2',
	'#calc_shipping_address_2',
];

function woongkirFrontendFormModifyFieldCity(e) {
	var provinceFeldId = $(e.target).attr('id');
	var provinceFieldValue = $(e.target).val();
	var provinceData = false;
	var $cityField = $('#' + provinceFeldId.replace('_state', '_city'));
	var cityFieldValue = $cityField.val();
	var cityFieldValueMatch = '';
	var cityData = false;
	var cityDropdown = [];

	if (provinceFieldValue && provinceFieldValue.length) {
		provinceData = WoongkirLocation.getProvince({ code: provinceFieldValue });
	}

	if (provinceData) {
		cityData = WoongkirLocation.getCity({ province_id: provinceData.province_id }, 'filter');
	}

	if (cityData) {
		for (var i = 0; i < cityData.length; i++) {
			var cityName = cityData[i].type + ' ' + cityData[i].city_name;

			if (cityFieldValue && cityFieldValue === cityName) {
				cityFieldValueMatch = cityName;
			}

			cityDropdown.push({
				id: cityName,
				text: cityName,
			});
		}
	}

	$cityField.selectWoo({
		data: cityDropdown,
		width: '100%',
		placeholder: woongkir_params.text.city,
	}).val(cityFieldValueMatch).trigger('change');
}

function woongkirFrontendFormModifyFieldSubdistrict(e) {
	var cityFieldId = $(e.target).attr('id');
	var cityFieldValue = $(e.target).val();
	var cityData = false;
	var $subdistrictField = $('#' + cityFieldId.replace('_city', '_address_2'));
	var subdistrictFieldValue = $subdistrictField.val();
	var subdistrictFieldValueMatch = '';
	var subdistrictData = false;
	var subdistrictDropdown = [];

	if (cityFieldValue && cityFieldValue.length) {
		var provinceFieldValue = $('#' + cityFieldId.replace('_city', '_state')).val();

		if (provinceFieldValue && provinceFieldValue.length) {
			var provinceData = WoongkirLocation.getProvince({ code: provinceFieldValue });

			if (provinceData) {
				var cityType = cityFieldValue.split(' ')[0];
				var cityName = cityFieldValue.split(' ').splice(1).join(' ');

				cityData = WoongkirLocation.getCity({
					type: cityType,
					city_name: cityName,
					province_id: provinceData.province_id,
				});

				if (cityData) {
					subdistrictData = WoongkirLocation.getSubdistrict({
						province_id: provinceData.province_id,
						city_id: cityData.city_id
					}, 'filter');
				}
			}
		}
	}

	if (subdistrictData) {
		for (var i = 0; i < subdistrictData.length; i++) {
			if (subdistrictFieldValue && subdistrictFieldValue === subdistrictData[i].subdistrict_name) {
				subdistrictFieldValueMatch = subdistrictData[i].subdistrict_name;
			}

			subdistrictDropdown.push({
				id: subdistrictData[i].subdistrict_name,
				text: subdistrictData[i].subdistrict_name,
			});
		}
	}

	$subdistrictField.selectWoo({
		data: subdistrictDropdown,
		width: '100%',
		placeholder: woongkir_params.text.subdistrict,
	}).val(subdistrictFieldValueMatch).trigger('change');
}

/**
 * Modify fields
 * 
 * @param {Object} $wrapper Field wrapper DOM object
 */
function woongkirFrontendFormModifyField($wrapper) {
	for (var i = 0; i < woongkirDynamicFields.length; i++) {
		var fieldId = woongkirDynamicFields[i].replace('#', '');
		var $field = $wrapper.find('#' + fieldId);

		if (fieldId === 'calc_shipping_address_2') {
			var $postCodeFieldWrap = $wrapper.find('#' + fieldId.replace('_address_2', '_postcode_field'));

			var $fieldWrap = $postCodeFieldWrap.clone().attr({
				id: fieldId + '_field'
			});

			$fieldWrap.find('input').attr({
				id: fieldId,
				name: fieldId,
			}).val('');

			$postCodeFieldWrap.before($fieldWrap);

			$field = $wrapper.find('#' + fieldId);

		}

		if ($field.length) {
			var placeholderText = fieldId.indexOf('_address_2') !== -1 ? woongkir_params.text.subdistrict : $field.attr('placeholder');
			$field.selectWoo({
				placeholder: placeholderText,
				width: '100%'
			}).addClass('woongkir-select2');
		}
	}

	// Entry point event
	$wrapper.find(woongkirStateFields.join(',')).trigger('change');
}

/**
 * Restore fields
 * 
 * @param {Object} $wrapper
 */
function woongkirFrontendFormRestore($wrapper) {
	for (var i = 0; i < woongkirDynamicFields.length; i++) {
		var fieldId = woongkirDynamicFields[i].replace('#', '');
		var $field = $wrapper.find('#' + fieldId);
		if ($field.length) {
			if ($field.hasClass('woongkir-select2')) {
				$field.selectWoo('destroy').removeClass('woongkir-select2');
			}

			if (fieldId === 'calc_shipping_address_2') {
				$field.closest('#calc_shipping_address_2_field').remove();
			}
		}
	}
}

// Render checkout form.
function woongkirFrontendForm() {
	$.each(woongkirCountryFields, function (id, wrapper) {
		var $country = $('#' + id);
		if ($country) {
			var $wrapper = $country.closest(wrapper);
			if ($wrapper) {
				$(document).off('change', '#' + id.replace('_country', '_city'), woongkirFrontendFormModifyFieldSubdistrict);
				$(document).off('change', '#' + id.replace('_country', '_state'), woongkirFrontendFormModifyFieldCity);
				woongkirFrontendFormRestore($wrapper);
				if ($country.val() === 'ID') {
					$(document).on('change', '#' + id.replace('_country', '_city'), woongkirFrontendFormModifyFieldSubdistrict);
					$(document).on('change', '#' + id.replace('_country', '_state'), woongkirFrontendFormModifyFieldCity);
					woongkirFrontendFormModifyField($wrapper);
				}
			}
		}
	});
}

$(document).ready(function () {
	// Bind checkout form on country_to_state_changed event.
	$(document.body).on('country_to_state_changed', woongkirFrontendForm);

	$(document.body).on('updated_wc_div', function () {
		$(document.body).trigger('country_to_state_changed');
	});

	// Bind checkout form on updated_shipping_method event.
	$(document.body).on('updated_shipping_method', function () {
		$(document.body).trigger('country_to_state_changed');
	});

	$(document.body).on('init_checkout', function () {
		$(document.body).trigger('country_to_state_changed');
	});
});
