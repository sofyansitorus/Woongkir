var woongkirLocation = {
	filterLocationData: function (results, fieldPrefix, fieldSuffix, fieldData) {
		var getLocationDataFilter = [];

		if (fieldData.convertInputToSelect && fieldData.convertInputToSelect.fieldFilters) {
			getLocationDataFilter = fieldData.convertInputToSelect.fieldFilters.map(function (item) {
				return {
					key: item,
					value: $('#' + fieldPrefix + '_' + item).val(),
				};
			});
		}

		var options = results.filter(function (result) {
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

		return options;
	},
	onChangeFieldState: function (event) {
		var fieldPrefix = $(event.target).attr('id').replace('_state', '');
		var $fieldCountry = $('#' + fieldPrefix + '_country');

		if ($fieldCountry && $fieldCountry.length && 'ID' !== $fieldCountry.val()) {
			return;
		}

		$.each(woongkirLocation.getFields(['state'], 'omit'), function (fieldSuffix, fieldData) {
			woongkirLocation.getLocationData(fieldSuffix).then(function (results) {
				var options = woongkirLocation.filterLocationData(results, fieldPrefix, fieldSuffix, fieldData);

				$('#' + fieldPrefix + '_' + fieldSuffix).selectWoo({
					data: options,
					placeholder: fieldData.placeholder || '',
				});
			});
		});
	},
	onChangeFieldCity: function (event) {
		var fieldPrefix = $(event.target).attr('id').replace('_city', '');
		var $fieldCountry = $('#' + fieldPrefix + '_country');

		if ($fieldCountry && $fieldCountry.length && 'ID' !== $fieldCountry.val()) {
			return;
		}

		$.each(woongkirLocation.getFields(['city', 'state'], 'omit'), function (fieldSuffix, fieldData) {
			woongkirLocation.getLocationData(fieldSuffix).then(function (results) {
				var options = woongkirLocation.filterLocationData(results, fieldPrefix, fieldSuffix, fieldData);

				$('#' + fieldPrefix + '_' + fieldSuffix).selectWoo({
					data: options,
					placeholder: fieldData.placeholder || '',
				});
			});
		});
	},
	onChangeFieldAddress2: function () {

	},
	getFields: function (filterBy, filterType) {
		var fields = {
			state: {
				onChange: woongkirLocation.onChangeFieldState,
				settingSuffix: 'province',
			},
			city: {
				onChange: woongkirLocation.onChangeFieldCity,
				placeholder: woongkir_params.text.placeholder.city,
				convertInputToSelect: {
					fieldPrefix: true,
					fieldFilters: ['state'],
				},
			},
			address_2: {
				onChange: woongkirLocation.onChangeFieldAddress2,
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
	},
	getLocationDataCountry: function () {
		return woongkirLocation.getLocationData('country');
	},
	getLocationDataState: function () {
		return woongkirLocation.getLocationData('state');
	},
	getLocationDataCity: function () {
		return woongkirLocation.getLocationData('city');
	},
	getLocationDataAddress2: function () {
		return woongkirLocation.getLocationData('address_2');
	},
	getLocationData: function (locationType) {
		var dfd = new $.Deferred();
		var dataKey = woongkir_params.json[locationType].key;
		var dataUrl = woongkir_params.json[locationType].url;

		var items = Lockr.get(dataKey);

		if (null === items || typeof items === 'undefined') {
			$.getJSON(dataUrl, { _: new Date().getTime() }, function (data) {
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
	}
};
