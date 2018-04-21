;(function($) {
"use strict";

var WoongkirLocation = {
	storeCountry: function () {
		var self = this;
		if (!self.getCountry().length) {
			$.getJSON(woongkir_params.json.country_url, function (data) {
				data.sort(function (a, b) {
					return (a.country_name > b.country_name) ? 1 : ((b.country_name > a.country_name) ? -1 : 0);
				});
				Lockr.set(woongkir_params.json.country_key, data);
			});
		}
	},
	getCountry: function (search, searchMethod) {
		var self = this;
		var items = Lockr.get(woongkir_params.json.country_key);
		if (!items || typeof items === 'undefined') {
			return [];
		}

		if (search && search === Object(search)) {
			return self.searchLocation(items, search, searchMethod);
		}

		return items;
	},
	storeProvince: function () {
		var self = this;
		if (!self.getProvince().length) {
			$.getJSON(woongkir_params.json.province_url, function (data) {
				data.sort(function (a, b) {
					return (a.province_name > b.province_name) ? 1 : ((b.province_name > a.province_name) ? -1 : 0);
				});
				Lockr.set(woongkir_params.json.province_key, data);
			});
		}
	},
	getProvince: function (search, searchMethod) {
		var self = this;
		var items = Lockr.get(woongkir_params.json.province_key);
		if (!items || typeof items === 'undefined') {
			return [];
		}

		if (search && search === Object(search)) {
			return self.searchLocation(items, search, searchMethod);
		}

		return items;
	},
	storeCity: function () {
		var self = this;
		if (!self.getCity().length) {
			$.getJSON(woongkir_params.json.city_url, function (data) {
				data.sort(function (a, b) {
					return (a.city_name > b.city_name) ? 1 : ((b.city_name > a.city_name) ? -1 : 0);
				});
				Lockr.set(woongkir_params.json.city_key, data);
			});
		}
	},
	getCity: function (search, searchMethod) {
		var self = this;
		var items = Lockr.get(woongkir_params.json.city_key);
		if (!items || typeof items === 'undefined') {
			return [];
		}

		if (search && search === Object(search)) {
			return self.searchLocation(items, search, searchMethod);
		}

		return items;
	},
	storeSubdistrict: function () {
		var self = this;
		if (!self.getSubdistrict().length) {
			$.getJSON(woongkir_params.json.subdistrict_url, function (data) {
				data.sort(function (a, b) {
					return (a.subdistrict_name > b.subdistrict_name) ? 1 : ((b.subdistrict_name > a.subdistrict_name) ? -1 : 0);
				});
				Lockr.set(woongkir_params.json.subdistrict_key, data);
			});
		}
	},
	getSubdistrict: function (search, searchMethod) {
		var self = this;
		var items = Lockr.get(woongkir_params.json.subdistrict_key);
		if (!items || typeof items === 'undefined') {
			return [];
		}

		if (search && search === Object(search)) {
			return self.searchLocation(items, search, searchMethod);
		}

		return items;
	},
	searchLocation: function (items, search, searchMethod) {
		var self = this, itemFound= false;
		searchMethod = searchMethod || 'find';
		switch (searchMethod) {
			case 'filter':
				itemFound = items.filter(function (item) {
					return self.isLocationMatch(item, search);
				});
				break;

			default:
				itemFound = items.find(function (item) {
					return self.isLocationMatch(item, search);
				});
				break;
		}
		return itemFound || false;
	},
	isLocationMatch: function (item, search) {
		var isItemMatch = true;
		for (var key in search) {
			if (!item.hasOwnProperty(key) || String(item[key]).toLowerCase() !== String(search[key]).toLowerCase()) {
				isItemMatch = false;
			}
		}
		return isItemMatch;
	}
};

WoongkirLocation.storeCountry(); // Store custom country data to local storage.
WoongkirLocation.storeProvince(); // Store custom province data to local storage.
WoongkirLocation.storeCity(); // Store custom city data to local storage.
WoongkirLocation.storeSubdistrict(); // Store custom subdistrict data to local storage.

// Render checkout form.
function woongkirFormCheckout(country, $wrapper) {
	if (!country || typeof country === 'undefined') {
		return;
	}
	if (!$wrapper || typeof $wrapper === 'undefined') {
		return;
	}

	$('#calc_shipping_address_2_field').remove();

	$($wrapper).find('#billing_city, #shipping_city, #calc_shipping_city, #billing_address_2, #shipping_address_2').each(function () {
		var self = this;
		$(self).show().closest('.form-row').find('.select2-container').remove();
		if (country === 'ID') {
			if ($(self).is('input')) {
				var $elementSelect = $('<select></select>');
				$.each(self.attributes, function (i, a) {
					switch (a.name) {
						case 'type':
							// Do nothing
							break;
						case 'value':
							$elementSelect.attr('data-value', a.value);
							break;

						default:
							$elementSelect.attr(a.name, a.value.replace('input-text', 'woongkir-input-text').replace('select2-hidden-accessible', ''));
							break;
					}
				});
				if ($(self).attr('id').indexOf('calc_shipping_city') >= 0) {
					var $calcSubdistrictFieldWrap = $('#calc_shipping_postcode_field').clone().attr({
						id: $(self).attr('id').replace('city', 'address_2') + '_field'
					}).empty().insertBefore('#calc_shipping_postcode_field');
					var $calcSubdistrictSelect = $elementSelect.clone().append('<option value="">' + woongkir_params.text.select_subdistrict + '</option>');
					$calcSubdistrictSelect.attr({
						id: $(self).attr('id').replace('city', 'address_2'),
						name: $(self).attr('id').replace('city', 'address_2')
					}).appendTo($calcSubdistrictFieldWrap);
					if ($().select2) {
						$calcSubdistrictSelect.select2({
							placeholderOption: 'first',
							width: '100%'
						});
					}
				}
				var firstOption = $(self).attr('id').indexOf('city') >= 0 ? woongkir_params.text.select_city : woongkir_params.text.select_subdistrict;
				$elementSelect.append('<option value="">' + firstOption + '</option>');
				$(self).replaceWith($elementSelect);
				if ($().select2) {
					$elementSelect.select2({
						placeholderOption: 'first',
						width: '100%'
					});
				}
			}
		} else {
			if ($(self).is('select')) {
				var $elementInput = $('<input type="text" />');
				$.each(self.attributes, function (i, a) {
					switch (a.name) {
						case 'data-value':
							$elementInput.val(a.value);
							break;

						default:
							$elementInput.attr(a.name, a.value.replace('woongkir-input-text', 'input-text').replace('select2-hidden-accessible', ''));
							break;
					}
				});
				$(self).replaceWith($elementInput);
			}
		}
	});

	// Bind on state fields change.
	var provinceData;
	$wrapper.find('#billing_state, #shipping_state, #calc_shipping_state').on('change', function () {
		provinceData = false;
		if (country !== 'ID') {
			return;
		}
		var $province = $(this);
		var $city = $('#' + $province.attr('id').replace('state', 'city'));

		$city.find('option').not(':first').remove();

		if (!$province.val() || !$province.val().length) {
			return;
		}

		provinceData = WoongkirLocation.getProvince({ code: $province.val() });
		if (!provinceData) {
			return;
		}

		var cityData = WoongkirLocation.getCity({ province_id: provinceData.province_id }, 'filter');
		if (!cityData) {
			return;
		}

		$.each(cityData, function (i, data) {
			var isSelected = $city.data('value') === data.type + ' ' + data.city_name ? ' selected' : '';
			$city.append('<option value="' + data.type + ' ' + data.city_name + '"' + isSelected + '>' + data.type + ' ' + data.city_name + '</option>');
		});
		$city.trigger('change');
	});

	// Bind on city fields change.
	var cityData;
	$wrapper.find('#billing_city, #shipping_city, #calc_shipping_city').on('change', function () {
		cityData = false;

		if (country !== 'ID') {
			return;
		}
		var $city = $(this);
		$city.attr('data-value', $city.val());

		var $subdistrict = $('#' + $city.attr('id').replace('city', 'address_2'));
		$subdistrict.find('option').not(':first').remove();
		$subdistrict.trigger('change');

		if (!$city.val() || !$city.val().length || !provinceData) {
			return;
		}

		var cityType = $city.val().split(' ').slice(0, 1).join('');
		var cityName = $city.val().split(' ').slice(1).join(' ');

		cityData = WoongkirLocation.getCity({
			province_id: provinceData.province_id,
			type: cityType,
			city_name: cityName
		});

		if (!cityData) {
			return;
		}

		var subdistrictData = WoongkirLocation.getSubdistrict({
			province_id: provinceData.province_id,
			city_id: cityData.city_id
		}, 'filter');

		if (!subdistrictData) {
			return;
		}

		$.each(subdistrictData, function (i, data) {
			var isSelected = $subdistrict.data('value') === data.subdistrict_name ? ' selected' : '';
			$subdistrict.append('<option value="' + data.subdistrict_name + '"' + isSelected + '>' + data.subdistrict_name + '</option>');
		});
		$subdistrict.trigger('change');
	});

	$wrapper.find('#billing_address_2, #shipping_address_2').on('change', function () {
		$(this).attr('data-value', $(this).val());
	});

	// Trigger change for state fields.
	$(function () {
		$wrapper.find('#billing_state, #shipping_state, #calc_shipping_state').trigger('change');
	});
}

$(document).ready(function () {
	// Bind checkout form on country_to_state_changed event.
	$(document.body).on('country_to_state_changed', function (e, country, wrapper) {
		woongkirFormCheckout(country, wrapper);
	});
	// Bind checkout form on updated_wc_div event.
	$(document.body).on('updated_wc_div', function () {
		$(':input.country_to_state').trigger('change');
	});
	// Bind checkout form on updated_shipping_method event.
	$(document.body).on('updated_shipping_method', function () {
		$(':input.country_to_state').trigger('change');
	});
});
}(jQuery));
