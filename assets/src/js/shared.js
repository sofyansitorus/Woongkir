// Store province data.
function woongkirStoreProvince() {
    if (!woongkirGetProvince().length) {
        $.getJSON(woongkir_params.json.province_url, function (data) {
            data.sort(function (a, b) {
                return (a.province_name > b.province_name) ? 1 : ((b.province_name > a.province_name) ? -1 : 0);
            });
            Lockr.set(woongkir_params.json.province_key, data);
        });
    }
}

// Get province data.
function woongkirGetProvince() {
    var data = Lockr.get(woongkir_params.json.province_key);
    if (typeof data == 'undefined' || data == 'undefined' || !data) {
        return [];
    }
    return data;
}

// Store city data.
function woongkirStoreCity() {
    if (!woongkirGetCity().length) {
        $.getJSON(woongkir_params.json.city_url, function (data) {
            data.sort(function (a, b) {
                return (a.city_name > b.city_name) ? 1 : ((b.city_name > a.city_name) ? -1 : 0);
            });
            Lockr.set(woongkir_params.json.city_key, data);
        });
    }
}

// Get city data.
function woongkirGetCity() {
    var data = Lockr.get(woongkir_params.json.city_key);
    if (typeof data == 'undefined' || data == 'undefined' || !data) {
        return [];
    }
    return data;
}

// Store subdictrict data.
function woongkirStoreSubdistrict() {
    if (!woongkirGetSubdistrict().length) {
        $.getJSON(woongkir_params.json.subdistrict_url, function (data) {
            data.sort(function (a, b) {
                return (a.subdistrict_name > b.subdistrict_name) ? 1 : ((b.subdistrict_name > a.subdistrict_name) ? -1 : 0);
            });
            Lockr.set(woongkir_params.json.subdistrict_key, data);
        });
    }
}

// Get subdictrict data.
function woongkirGetSubdistrict() {
    var data = Lockr.get(woongkir_params.json.subdistrict_key);
    if (typeof data == 'undefined' || data == 'undefined' || !data) {
        return [];
    }
    return data;
}

woongkirStoreProvince(); // Store custom province data to local storage.
woongkirStoreCity(); // Store custom city data to local storage.
woongkirStoreSubdistrict(); // Store custom subdistrict data to local storage.