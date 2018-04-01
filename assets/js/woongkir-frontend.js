;(function($) {
"use strict";

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
// Render checkout form.
function woongkirFormCheckout(country, $wrapper) {
    if (typeof country == 'undefined' || country == 'undefined' || !country) {
        return;
    }
    if (typeof $wrapper == 'undefined' || $wrapper == 'undefined' || !$wrapper) {
        return;
    }

    var $provinceBox = $wrapper.find('#billing_state, #shipping_state, #calc_shipping_state'),
        $cityBox = $wrapper.find('#billing_city, #shipping_city, #calc_shipping_city'),
        $cityParent = $cityBox.parent(),
        city_value = $cityBox.val(),
        city_input_name = $cityBox.attr('name'),
        city_input_id = $cityBox.attr('id'),
        city_input_placeholder = $cityBox.attr('placeholder') || $cityBox.attr('data-placeholder') || '',
        $subdistrictBox = $wrapper.find('#billing_address_2, #shipping_address_2'),
        $subdistrictParent = $subdistrictBox.parent(),
        subdistrict_value = $subdistrictBox.val(),
        subdistrict_input_name = $subdistrictBox.attr('name'),
        subdistrict_input_id = $subdistrictBox.attr('id'),
        subdistrict_input_placeholder = $subdistrictBox.attr('placeholder') || $subdistrictBox.attr('data-placeholder') || '';

    if (country === 'ID') {
        $provinceBox.bind('change', function () {
            $cityBox.unbind('change');
            // Change for select
            if ($cityBox.is('input')) {
                $cityBox.replaceWith('<select name="' + city_input_name + '" id="' + city_input_id + '" class="city_select" data-placeholder="' + woongkir_params.text.select_city + '"></select>');
                $cityBox = $wrapper.find('#billing_city, #shipping_city, #calc_shipping_city');
            }
            $cityBox.empty().append('<option value="">' + woongkir_params.text.select_city + '</option>');
            var selectedProvince = $provinceBox.val();
            var province_id = 0;
            var province_data = woongkirGetProvince();
            if (province_data) {
                $.each(province_data, function (index, data) {
                    if (data.code == selectedProvince) {
                        province_id = data.province_id;
                        return;
                    }
                });
            }
            var city_data = woongkirGetCity();
            var city_name = '';
            if (city_data) {
                $.each(city_data, function (index, data) {
                    if (data.province_id == province_id) {
                        city_name = data.type + ' ' + data.city_name;
                        var selected = city_name === city_value ? ' selected' : '';
                        $cityBox.append('<option value="' + city_name + '" ' + selected + '>' + city_name + '</option>');
                    }
                });
            }
            if ($().select2) {
                $cityBox.select2({
                    placeholderOption: 'first',
                    placeholder: woongkir_params.text.select_city,
                    width: '100%'
                });
            }
            $cityBox.bind('change', function () {
                if ($subdistrictBox.length) {
                    if ($subdistrictBox.is('input')) {
                        $subdistrictBox.replaceWith('<select name="' + subdistrict_input_name + '" id="' + subdistrict_input_id + '" class="subdistrict_select" data-placeholder="' + woongkir_params.text.select_subdistrict + '"></select>');
                        $subdistrictBox = $wrapper.find('#billing_address_2, #shipping_address_2');

                    }
                    $subdistrictBox.empty().append('<option value="">' + woongkir_params.text.select_subdistrict + '</option>');
                    var city_id = 0;
                    var city_data = woongkirGetCity();
                    if (city_data) {
                        $.each(city_data, function (index, data) {
                            if (data.type + ' ' + data.city_name == $cityBox.val()) {
                                city_id = data.city_id;
                                return;
                            }
                        });
                    }
                    if (city_id) {
                        var subdistrict_data = woongkirGetSubdistrict();
                        if (subdistrict_data) {
                            $.each(subdistrict_data, function (index, data) {
                                if (data.city_id == city_id) {
                                    var selected = data.subdistrict_name === subdistrict_value ? ' selected' : '';
                                    $subdistrictBox.append('<option value="' + data.subdistrict_name + '" ' + selected + '>' + data.subdistrict_name + '</option>');
                                }
                            });
                        }
                    }
                    if ($().select2) {
                        $subdistrictBox.select2({
                            placeholderOption: 'first',
                            placeholder: woongkir_params.text.select_subdistrict,
                            width: '100%'
                        });
                    }
                }
            });
            $(function () {
                $cityBox.change();
            });
        });
        $(function () {
            $provinceBox.change();
        });
    } else {
        if ($cityBox.is('select')) {
            $cityParent.show().find('.select2-container').remove();
            $cityBox.replaceWith('<input type="text" class="input-text" name="' + city_input_name + '" id="' + city_input_id + '" placeholder="' + city_input_placeholder + '" />');
            $cityBox = $wrapper.find('#billing_city, #shipping_city, #calc_shipping_city');
        }
        if ($subdistrictBox.is('select')) {
            $subdistrictParent.show().find('.select2-container').remove();
            $subdistrictBox.replaceWith('<input type="text" class="input-text" name="' + subdistrict_input_name + '" id="' + subdistrict_input_id + '" placeholder="' + subdistrict_input_placeholder + '" />');
            $subdistrictBox = $wrapper.find('#billing_address_2, #shipping_address_2');
        }
    }
}

$(document).ready(function () {
    // Bind checkout form on country_to_state_changed event.
    $(document.body).on('country_to_state_changed', function (e, country, wrapper) {
        woongkirFormCheckout(country, wrapper);
    });
    // Bind checkout form on updated_wc_div event.
    $(document.body).on('updated_wc_div', function (e) {
        $(':input.country_to_state').change();
    });
    // Bind checkout form on updated_shipping_method event.
    $(document.body).on('updated_shipping_method', function (e) {
        $(':input.country_to_state').change();
    });
});
}(jQuery));
