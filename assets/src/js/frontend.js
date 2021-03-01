function maybeCloneInputNotExist(fieldPrefix, fieldSuffix, fieldData) {
	var fieldId = fieldPrefix + '_' + fieldSuffix;
	var $field = $('#' + fieldId);

	if ($field && $field.length) {
		return;
	}

	var cloneIfNotExist = $.extend({}, {
		fieldPrefix: [],
		fieldFrom: false,
	}, fieldData.cloneIfNotExist);

	var cloneIfNotExistPrefixMatch = true === cloneIfNotExist.fieldPrefix || -1 !== cloneIfNotExist.fieldPrefix.indexOf(fieldPrefix);

	if (!cloneIfNotExistPrefixMatch) {
		return;
	}

	var cloneFromId = fieldPrefix + '_' + cloneIfNotExist.fieldFrom;
	var $cloneFromWrap = $('#' + cloneFromId + '_field');

	if (!$cloneFromWrap || !$cloneFromWrap.length) {
		return;
	}

	var $cloneFromField = $('#' + cloneFromId);

	if (!$cloneFromField || !$cloneFromField.length) {
		return;
	}

	var $clonedField = $cloneFromField.clone().attr({
		id: fieldId,
		name: fieldId,
		value: $('#woongkir_' + fieldId).val(),
		placeholder: fieldData.placeholder || '',
	});

	var $clonedWrap = $cloneFromWrap.clone().prop({
		id: fieldId + '_field',
	}).empty().append($clonedField);

	$cloneFromWrap.before($clonedWrap);
}

function maybeConvertInputToSelect(fieldPrefix, fieldSuffix, fieldData, onChangeListener) {
	var $field = $('#' + fieldPrefix + '_' + fieldSuffix);

	if (!$field || !$field.length) {
		return;
	}

	if (onChangeListener) {
		$field.off('change', fieldData.onChange);
	}

	var convertInputToSelect = $.extend({}, {
		fieldPrefix: [],
		fieldFilters: [],
	}, fieldData.convertInputToSelect);

	var convertInputToSelectPrefixMatch = true === convertInputToSelect.fieldPrefix || -1 !== convertInputToSelect.fieldPrefix.indexOf(fieldPrefix);

	if (convertInputToSelectPrefixMatch) {
		var getLocationDataFilter = [];

		$.each(convertInputToSelect.fieldFilters, function (index, fieldFilter) {
			getLocationDataFilter.push({
				index: index,
				key: fieldFilter,
				value: $('#' + fieldPrefix + '_' + fieldFilter).val(),
			});
		});

		woongkirLocation.getLocationData(fieldSuffix).then(function (results) {
			var options = results.filter(function (result) {
				return getLocationDataFilter.every(function (locationFilter) {
					return result[locationFilter.key] === locationFilter.value;
				});
			}).map(function (item) {
				return {
					id: item.value,
					text: item.label || item.value,
					selected: $field.val() === item.value,
				};
			});

			console.log('options', {
				options,
				fieldPrefix,
				fieldSuffix,
				$field: $field.val(),
			});

			var optionMatch = options.find(function (item) {
				return item.selected;
			});

			var fieldValue = optionMatch ? optionMatch.id : '';
			var fieldPlaceholder = fieldData.placeholder || '';

			$field.attr({
				placeholder: fieldPlaceholder,
			}).data('placeholder', fieldPlaceholder).val(fieldValue);

			$field.selectWoo({
				width: '100%',
				data: options,
			});
		});
	}

	if (onChangeListener) {
		$field.on('change', fieldData.onChange);
	}
}

function onChangeFieldState(event) {
	var fieldPrefix = $(event.target).attr('ID').replace('_state', '');

	if ('ID' !== $('#' + fieldPrefix + '_country').val()) {
		return;
	}

	$.each(getFields(['state'], 'omit'), function (fieldSuffix, fieldData) {
		maybeConvertInputToSelect(fieldPrefix, fieldSuffix, fieldData);
	});
}

function onChangeFieldCity(event) {
	var fieldPrefix = $(event.target).attr('ID').replace('_city', '');

	if ('ID' !== $('#' + fieldPrefix + '_country').val()) {
		return;
	}

	$.each(getFields(['state', 'city'], 'omit'), function (fieldSuffix, fieldData) {
		maybeConvertInputToSelect(fieldPrefix, fieldSuffix, fieldData);
	});
}

function onChangeFieldAddress2(event) {
	var fieldPrefix = $(event.target).attr('ID').replace('_city', '');

	if ('ID' !== $('#' + fieldPrefix + '_country').val()) {
		return;
	}

	console.log('onChangeFieldAddress2', event);
}

function getFields(filterBy, filterType) {
	var fields = {
		state: {
			onChange: onChangeFieldState,
		},
		city: {
			onChange: onChangeFieldCity,
			placeholder: woongkir_params.text.placeholder.city,
			convertInputToSelect: {
				fieldPrefix: true,
				fieldFilters: ['state'],
			},
		},
		address_2: {
			onChange: onChangeFieldAddress2,
			placeholder: woongkir_params.text.placeholder.address_2,
			convertInputToSelect: {
				fieldPrefix: true,
				fieldFilters: ['state', 'city'],
			},
			cloneIfNotExist: {
				fieldPrefix: 'calc_shipping',
				fieldFrom: 'postcode',
			},
		},
	};

	if (filterBy) {
		var filtered = {};

		$.each(fields, function (key, fieldData) {
			if ('omit' === filterType && -1 !== filterBy.indexOf(key)) {
				return;
			}

			if ('includes' === filterType && -1 === filterBy.indexOf(key)) {
				return;
			}

			filtered[key] = fieldData;
		});

		return filtered;
	}

	return fields;
}

function modifyForm($selectorCountry) {
	var fieldPrefix = $selectorCountry.attr('id').replace('_country', '');

	$.each(getFields(), function (fieldSuffix, fieldData) {
		maybeCloneInputNotExist(fieldPrefix, fieldSuffix, fieldData);
		maybeConvertInputToSelect(fieldPrefix, fieldSuffix, fieldData, true);
	})
}

function restoreForm($selectorCountry) {
	// var fieldPrefix = $selectorCountry.attr('ID').replace('_country', '');

	// $.each(getFields(), function (fieldSuffix, fieldData) {
	// 	var fieldId = fieldPrefix + '_' + fieldSuffix;
	// 	var $field = $('#' + fieldId);

	// 	if (!$field || !$field.length) {
	// 		return;
	// 	}

	// 	$field.off('change', fieldData.onChange);

	// 	var convertInputToSelect = $.extend({}, {
	// 		fieldPrefix: [],
	// 	}, fieldData.convertInputToSelect);

	// 	var convertInputToSelectPrefixMatch = true === convertInputToSelect.fieldPrefix || -1 !== convertInputToSelect.fieldPrefix.indexOf(fieldPrefix);

	// 	if (convertInputToSelectPrefixMatch && $field.hasClass('select2-hidden-accessible')) {
	// 		$field.selectWoo('destroy');
	// 	}

	// 	var cloneIfNotExist = $.extend({}, {
	// 		fieldPrefix: [],
	// 		fieldFrom: false,
	// 	}, fieldData.cloneIfNotExist);

	// 	var cloneIfNotExistPrefixMatch = true === cloneIfNotExist.fieldPrefix || -1 !== cloneIfNotExist.fieldPrefix.indexOf(fieldPrefix);

	// 	if (cloneIfNotExistPrefixMatch) {
	// 		$('#' + fieldId + '_field').remove();
	// 	}
	// });
}

$(document.body).on('country_to_state_changed', function (event, country, dropdownCountry) {
	if ('country_to_state_changed' !== event.type) {
		return;
	}

	var selectedCountry = country || $('#calc_shipping_country').val();
	var $selectorCountry = dropdownCountry ? dropdownCountry.prevObject : $('#calc_shipping_country');

	if (!$selectorCountry || !$selectorCountry.length) {
		return;
	}

	if ('ID' === selectedCountry) {
		modifyForm($selectorCountry);
	} else {
		restoreForm($selectorCountry);
	}
});

$(document.body).on('update_checkout', function () {
	console.log('update_checkout');
});

