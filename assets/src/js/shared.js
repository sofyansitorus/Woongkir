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
		var self = this;
		searchMethod = searchMethod || 'find';
		switch (searchMethod) {
			case 'filter':
				var itemFound = items.filter(function (item) {
					return self.isLocationMatch(item, search);
				});
				break;

			default:
				var itemFound = items.find(function (item) {
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
}

WoongkirLocation.storeCountry(); // Store custom country data to local storage.
WoongkirLocation.storeProvince(); // Store custom province data to local storage.
WoongkirLocation.storeCity(); // Store custom city data to local storage.
WoongkirLocation.storeSubdistrict(); // Store custom subdistrict data to local storage.
