function woongkirFrontendModifyForm(fieldPrefix) {
	var localeData = $.extend(true, {}, woongkir_params.locale.default, woongkir_params.locale.ID);

	$.each(woongkirShared.getFields(), function (fieldSuffix, fieldData) {
		var fieldId = fieldPrefix + '_' + fieldSuffix;
		var fieldLocale = localeData[fieldSuffix] || {};

		if ($('#' + fieldId).length < 1 && 'calc_shipping' === fieldPrefix && 'address_2' === fieldSuffix) {
			var $postCodeField = $('#' + fieldPrefix + '_postcode_field');

			if ($postCodeField && $postCodeField.length) {
				$postCodeField
					.clone()
					.attr({
						id: fieldId + '_field',
					})
					.insertBefore($postCodeField);

				var placeholder = localeData[fieldSuffix] && localeData[fieldSuffix].placeholder || '';

				$('#' + fieldId + '_field').find('input').attr({
					id: fieldId,
					name: fieldId,
					placeholder: placeholder,
					'data-placeholder': placeholder,
					value: $('#woongkir_' + fieldPrefix + '_' + fieldSuffix).val(),
				});
			}
		}

		if ($('#' + fieldId).length < 1) {
			return;
		}

		if ('address_2' === fieldSuffix && fieldLocale.label) {
			$('label[for="' + fieldId + '"]').removeClass('screen-reader-text');
		}

		if (fieldData.onChange) {
			$('#' + fieldId).off('change', fieldData.onChange);
		}

		var isConvert = true === fieldData.convert || (Array.isArray(fieldData.convert) && fieldData.convert.indexOf(fieldPrefix) !== -1);

		if (!$('#' + fieldId).data('select2') && isConvert) {
			woongkirShared.getLocationData(fieldSuffix).then(function (results) {
				var options = woongkirShared.filterLocationData(results, fieldPrefix, fieldSuffix, fieldData);

				$('#' + fieldId).selectWoo({
					data: options,
					width: '100%',
				});

				if (fieldData.onChange) {
					$('#' + fieldId).on('change', fieldData.onChange);
				}
			});
		} else {
			if (fieldData.onChange) {
				$('#' + fieldId).on('change', fieldData.onChange);
			}
		}
	});
}

function woongkirFrontendRestoreForm(fieldPrefix, countryCode) {
	var localeData = $.extend(true, {}, woongkir_params.locale.default, woongkir_params.locale[countryCode]);

	$.each(woongkirShared.getFields(), function (fieldSuffix, fieldData) {
		var fieldId = fieldPrefix + '_' + fieldSuffix;
		var fieldLocale = localeData[fieldSuffix] || {};

		if ($('#' + fieldId).length < 1) {
			return;
		}

		$('#' + fieldId).off('change', fieldData.onChange);

		if (!fieldData.convert) {
			return;
		}

		if (Array.isArray(fieldData.convert) && fieldData.convert.indexOf(fieldPrefix) === -1) {
			return;
		}

		if ('address_2' === fieldSuffix && !fieldLocale.label) {
			$('label[for="' + fieldId + '"]').addClass('screen-reader-text');
		}

		if ($('#' + fieldId).data('select2')) {
			$('#' + fieldId).select2('destroy');
		}

		if ('calc_shipping' === fieldPrefix && 'address_2' === fieldSuffix) {
			$('#' + fieldId + '_field').remove();
		}
	});
}

var woongkirFrontendCountryToStateChangedTimeoutId = {};

$(document.body).on('country_to_state_changed', function (event, country, dropdownCountry) {
	if ('country_to_state_changed' !== event.type) {
		return;
	}

	var countryCode = country || $('#calc_shipping_country').val();
	var $selectorCountry = dropdownCountry && dropdownCountry.prevObject ? dropdownCountry.prevObject : $('#calc_shipping_country');

	if (!$selectorCountry || !$selectorCountry.length) {
		return;
	}

	var fieldPrefix = $selectorCountry.attr('ID').replace('_country', '');

	if (woongkirFrontendCountryToStateChangedTimeoutId[fieldPrefix]) {
		clearTimeout(woongkirFrontendCountryToStateChangedTimeoutId[fieldPrefix]);
	}

	woongkirFrontendCountryToStateChangedTimeoutId[fieldPrefix] = setTimeout(function () {
		if ('ID' === countryCode) {
			woongkirFrontendModifyForm(fieldPrefix);
		} else {
			woongkirFrontendRestoreForm(fieldPrefix, countryCode);
		}
	}, 100);
});

