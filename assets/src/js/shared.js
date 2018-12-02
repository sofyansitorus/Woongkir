var woongkirLocation = {
	storeCountry: function () {
		if (!woongkirLocation.getCountry().length) {
			$.getJSON(woongkir_params.json.country_url, function (data) {
				data.sort(function (a, b) {
					return (a.country_name > b.country_name) ? 1 : ((b.country_name > a.country_name) ? -1 : 0);
				});
				Lockr.set(woongkir_params.json.country_key, data);
			});
		}
	},
	getCountry: function (search, searchMethod) {
		var items = Lockr.get(woongkir_params.json.country_key);
		if (!items || typeof items === 'undefined') {
			return [];
		}

		if (search && search === Object(search)) {
			return woongkirLocation.searchLocation(items, search, searchMethod);
		}

		return items;
	},
	storeProvince: function () {
		if (!woongkirLocation.getProvince().length) {
			$.getJSON(woongkir_params.json.province_url, function (data) {
				data.sort(function (a, b) {
					return (a.province_name > b.province_name) ? 1 : ((b.province_name > a.province_name) ? -1 : 0);
				});
				Lockr.set(woongkir_params.json.province_key, data);
			});
		}
	},
	getProvince: function (search, searchMethod) {
		var items = Lockr.get(woongkir_params.json.province_key);
		if (!items || typeof items === 'undefined') {
			return [];
		}

		if (search && search === Object(search)) {
			return woongkirLocation.searchLocation(items, search, searchMethod);
		}

		return items;
	},
	storeCity: function () {
		if (!woongkirLocation.getCity().length) {
			$.getJSON(woongkir_params.json.city_url, function (data) {
				data.sort(function (a, b) {
					return (a.city_name > b.city_name) ? 1 : ((b.city_name > a.city_name) ? -1 : 0);
				});
				Lockr.set(woongkir_params.json.city_key, data);
			});
		}
	},
	getCity: function (search, searchMethod) {
		var items = Lockr.get(woongkir_params.json.city_key);
		if (!items || typeof items === 'undefined') {
			return [];
		}

		if (search && search === Object(search)) {
			return woongkirLocation.searchLocation(items, search, searchMethod);
		}

		return items;
	},
	storeSubdistrict: function () {
		if (!woongkirLocation.getSubdistrict().length) {
			$.getJSON(woongkir_params.json.subdistrict_url, function (data) {
				data.sort(function (a, b) {
					return (a.subdistrict_name > b.subdistrict_name) ? 1 : ((b.subdistrict_name > a.subdistrict_name) ? -1 : 0);
				});
				Lockr.set(woongkir_params.json.subdistrict_key, data);
			});
		}
	},
	getSubdistrict: function (search, searchMethod) {
		var items = Lockr.get(woongkir_params.json.subdistrict_key);
		if (!items || typeof items === 'undefined') {
			return [];
		}

		if (search && search === Object(search)) {
			return woongkirLocation.searchLocation(items, search, searchMethod);
		}

		return items;
	},
	searchLocation: function (items, search, searchMethod) {
		if (searchMethod === 'filter') {
			return items.filter(function (item) {
				return woongkirLocation.isLocationMatch(item, search);
			});
		}

		return items.find(function (item) {
			return woongkirLocation.isLocationMatch(item, search);
		});
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

woongkirLocation.storeCountry(); // Store custom country data to local storage.
woongkirLocation.storeProvince(); // Store custom province data to local storage.
woongkirLocation.storeCity(); // Store custom city data to local storage.
woongkirLocation.storeSubdistrict(); // Store custom subdistrict data to local storage.
