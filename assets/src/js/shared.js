var woongkirShared = {
	updateCheckoutTimeoutId: null,
	renderLocationFields: function (fieldPrefix, fieldSuffixes) {
		var dfd = new $.Deferred();

		var renderCounter = 0;

		$.each(woongkirShared.getFields(), function (fieldSuffix, fieldData) {
			if (!fieldSuffixes || fieldSuffixes.indexOf(fieldSuffix) === -1) {
				return;
			}

			var fieldId = fieldPrefix + '_' + fieldSuffix;

			if (!$('#' + fieldId) || !$('#' + fieldId).length) {
				return;
			}

			woongkirShared.getLocationData(fieldSuffix).then(function (results) {
				var options = woongkirShared.filterLocationData(results, fieldPrefix, fieldSuffix, fieldData);

				var optionSelected = options.find(function (option) {
					return option.selected;
				});

				var optionSelectedValue = optionSelected ? optionSelected.id : null;

				$('#' + fieldId).selectWoo({
					data: options,
					width: '100%',
				}).val(optionSelectedValue);

				renderCounter++;

				if (renderCounter === fieldSuffixes.length) {
					dfd.resolve();
				}
			});
		});

		return dfd.promise();
	},
	onChangeFieldState: function (event) {
		var fieldPrefix = $(event.target).attr('id').replace('_state', '');

		woongkirShared.renderLocationFields(fieldPrefix, ['city', 'address_2']).then(function () {
			$('#' + fieldPrefix + '_city').trigger('change');
		});
	},
	onChangeFieldCity: function (event) {
		var fieldPrefix = $(event.target).attr('id').replace('_city', '');

		woongkirShared.renderLocationFields(fieldPrefix, ['address_2']).then(function () {
			$('#' + fieldPrefix + '_address_2').trigger('change');
		});
	},
	onChangeFieldAddress2: function (event) {
		var fieldPrefix = $(event.target).attr('id').replace('_address_2', '');
		var isShipDifferentAddress = $('#ship-to-different-address-checkbox').is(':checked');
		var isCheckout = wc_checkout_params && wc_checkout_params.is_checkout;

		if (!isCheckout) {
			return;
		}

		if (isShipDifferentAddress && 'billing' === fieldPrefix) {
			return;
		}

		if (woongkirShared.updateCheckoutTimeoutId) {
			clearTimeout(woongkirShared.updateCheckoutTimeoutId);
		}

		woongkirShared.updateCheckoutTimeoutId = setTimeout(function () {
			$(document.body).trigger('update_checkout');
		}, 200);
	},
	getFields: function () {
		return {
			state: {
				onChange: woongkirShared.onChangeFieldState,
				convert: false,
				restore: false,
			},
			city: {
				onChange: woongkirShared.onChangeFieldCity,
				convert: true,
				restore: true,
				fieldFilters: ['state'],
			},
			address_2: {
				onChange: woongkirShared.onChangeFieldAddress2,
				convert: true,
				restore: true,
				fieldFilters: ['state', 'city'],
			},
		};
	},
	getLocationDataCountry: function () {
		return woongkirShared.getLocationData('country');
	},
	getLocationDataState: function () {
		return woongkirShared.getLocationData('state');
	},
	getLocationDataCity: function () {
		return woongkirShared.getLocationData('city');
	},
	getLocationDataAddress2: function () {
		return woongkirShared.getLocationData('address_2');
	},
	getLocationData: function (locationType) {
		var dfd = new $.Deferred();
		var dataKey = woongkir_params.json[locationType].key;
		var dataUrl = woongkir_params.json[locationType].url;

		var items = Lockr.get(dataKey);

		if (null === items || typeof items === 'undefined') {
			var randomKey = Math.random().toString(36).substring(7);
			$.getJSON(dataUrl, { [randomKey]: new Date().getTime() }, function (data) {
				data.sort(function (a, b) {
					return (a.value > b.value) ? 1 : ((b.value > a.value) ? -1 : 0);
				});

				Lockr.set(dataKey, data);

				dfd.resolve(data);
			});
		} else {
			dfd.resolve(items);
		}

		return dfd.promise();
	},
	filterLocationData: function (results, fieldPrefix, fieldSuffix, fieldData) {
		var getLocationDataFilter = [];

		if (fieldData.fieldFilters) {
			getLocationDataFilter = fieldData.fieldFilters.filter(function (item) {
				return $('#' + fieldPrefix + '_' + item).length > 0;
			}).map(function (item) {
				return {
					key: item,
					value: $('#' + fieldPrefix + '_' + item).val(),
				};
			});
		}

		return results.filter(function (result) {
			if (!getLocationDataFilter || !getLocationDataFilter.length) {
				return true;
			}

			return getLocationDataFilter.every(function (locationFilter) {
				return result[locationFilter.key] === locationFilter.value;
			});
		}).map(function (item) {
			return {
				id: item.value,
				text: item.label || item.value,
				selected: $('#' + fieldPrefix + '_' + fieldSuffix).val() === item.value,
			};
		});
	},
};
