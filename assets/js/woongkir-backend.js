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
// Render settings form.
function woongkirFormSettings() {

    var provinceData = woongkirGetProvince(),
        cityData = woongkirGetCity(),
        subdistrictData = woongkirGetSubdistrict(),
        $form = $('.woongkir-account-type').closest('form');

    // Bind on account type data change.
    $form.find('.woongkir-account-type').bind('change', function (e) {

        var account_type = $(e.currentTarget).val(),
            accounts = $(e.currentTarget).data('accounts'),
            couriers = $(e.currentTarget).data('couriers');

        for (var zone_id in couriers) {
            $('#woongkir-couriers-list-' + zone_id).hide();
            var multiple = 0;
            for (var courier_id in couriers[zone_id]) {
                if (couriers[zone_id][courier_id].account.indexOf(account_type) === -1) {
                    $('#woongkir-courier-box-' + zone_id + '-' + courier_id).hide().find('.woongkir-service').prop('checked', false);
                } else {
                    $('#woongkir-couriers-list-' + zone_id).show();
                    $('#woongkir-courier-box-' + zone_id + '-' + courier_id).show();
                }
                if (!accounts[account_type].multiple) {
                    if (multiple) {
                        $('#woongkir-courier-box-' + zone_id + '-' + courier_id).find('.woongkir-service').prop('checked', false);
                    }
                    if ($('#woongkir-courier-box-' + zone_id + '-' + courier_id).find('.woongkir-service.single:checked').length) {
                        multiple++;
                    }
                }
            }
        }
    });

    $form.find('.woongkir-service.bulk').bind('change', function (e) {
        var $table = $(this).closest('table');
        var zone_id = $(this).closest('.woongkir-couriers-list').data('id');
        var courier_id = $(this).closest('.woongkir-courier-box').data('id');
        var account_type = $form.find('.woongkir-account-type').val();
        var accounts = $form.find('.woongkir-account-type').data('accounts');
        var couriers = $form.find('.woongkir-account-type').data('couriers');
        if (this.checked) {
            $table.find('.woongkir-service.single').prop('checked', true);
            if (!accounts[account_type].multiple) {
                $form.find('.woongkir-courier-box').not('.' + courier_id).find('.woongkir-service').prop('checked', false);
            }
        } else {
            $table.find('.woongkir-service.single').prop('checked', false);
        }
    });

    $form.find('.woongkir-service.single').bind('change', function (e) {
        var $table = $(this).closest('table');
        var zone_id = $(this).closest('.woongkir-couriers-list').data('id');
        var courier_id = $(this).closest('.woongkir-courier-box').data('id');
        var account_type = $form.find('.woongkir-account-type').val();
        var accounts = $form.find('.woongkir-account-type').data('accounts');
        var couriers = $form.find('.woongkir-account-type').data('couriers');
        if (this.checked) {
            $table.find('.woongkir-service.bulk').prop({
                checked: true
            });
            if (!accounts[account_type].multiple) {
                $form.find('.woongkir-courier-box').not('.' + courier_id).find('.woongkir-service').prop('checked', false);
            }
        } else {
            if (!$table.find('.woongkir-service.single:checked').length) {
                $table.find('.woongkir-service.bulk').prop({
                    checked: false
                });
            }
        }
    });

    // Trigger account type data change.
    $form.find('.woongkir-account-type').trigger('change');

    // Render province dropdown list.
    $form.find('.woongkir-origin-province-select').empty().append('<option value="">' + woongkir_params.text.select_province + '</option>');

    if (provinceData.length) {
        var selected = $form.find('.woongkir-origin-province').val();
        $.each(provinceData, function (index, item) {
            var optionItem = '<option value="' + item.province_id + '"';
            if (selected == item.province_id) {
                optionItem += ' selected';
            }
            optionItem += '>' + item.province;
            optionItem += '</option>';
            $form.find('.woongkir-origin-province-select').append(optionItem);
        });
    }

    // Bind on province data change.
    $form.find('.woongkir-origin-province-select').bind('change', function (e) {
        // Set value for province input field.
        $form.find('.woongkir-origin-province').val($(e.currentTarget).val());
        // Build city list dropdown.
        $form.find('.woongkir-origin-city-select').empty().append('<option value="">' + woongkir_params.text.select_city + '</option>');
        if (cityData.length) {
            var selected = $form.find('.woongkir-origin-city').val();
            $.each(cityData, function (index, item) {
                if (item.province_id == $(e.currentTarget).val()) {
                    var optionItem = '<option value="' + item.city_id + '"';
                    if (selected == item.city_id) {
                        optionItem += ' selected';
                    }
                    optionItem += '>';
                    optionItem += item.type + ' ';
                    optionItem += item.city_name;
                    optionItem += '</option>';
                    $form.find('.woongkir-origin-city-select').append(optionItem);
                }
            });
            // Trigger city data change.
            $form.find('.woongkir-origin-city-select').trigger('change');
        }
    });

    // Bind on city data change.
    $form.find('.woongkir-origin-city-select').bind('change', function (e) {
        // Set value for city input field.
        $form.find('.woongkir-origin-city').val($(e.currentTarget).val());
        // Build subdistrict list dropdown.
        $form.find('.woongkir-origin-subdistrict-select').empty().append('<option value="">' + woongkir_params.text.select_subdistrict + '</option>');
        var subdistrictData = woongkirGetSubdistrict();
        if (subdistrictData.length) {
            var selected = $form.find('.woongkir-origin-subdistrict').val();
            $.each(subdistrictData, function (index, item) {
                if (item.city_id == $(e.currentTarget).val()) {
                    var optionItem = '<option value="' + item.subdistrict_id + '"';
                    if (selected == item.subdistrict_id) {
                        optionItem += ' selected';
                    }
                    optionItem += '>' + item.subdistrict_name;
                    optionItem += '</option>';
                    $form.find('.woongkir-origin-subdistrict-select').append(optionItem);
                }
            });
            // Trigger subdistrict data change.
            $form.find('.woongkir-origin-subdistrict-select').trigger('change');
        }
    });

    // Bind on subdistrict data change.
    $form.find('.woongkir-origin-subdistrict-select').bind('change', function (e) {
        // Set value for subdistrict input field.
        $form.find('.woongkir-origin-subdistrict').val($(e.currentTarget).val());
    });

    // Trigger province data change.
    $form.find('.woongkir-origin-province-select').trigger('change');
}

$(document).ready(function () {

    // Bind settings form on click .wc-shipping-zone-method-settings.
    $(document).on('click', '.wc-shipping-zone-method-settings', woongkirFormSettings);

    if (woongkir_params.show_settings) {

        setTimeout(function () {

            // Try show settings modal on settings page.
            var isMethodAdded = false;
            var methods = $(document).find('.wc-shipping-zone-method-type');
            for (var i = 0; i < methods.length; i++) {
                var method = methods[i];
                if ($(method).text() == woongkir_params.method_title) {
                    $(method).closest('tr').find('.row-actions .wc-shipping-zone-method-settings').trigger('click');
                    isMethodAdded = true;
                    return;
                }
            }

            // Show Add shipping method modal if the shipping is not added.
            if (!isMethodAdded) {
                $(".wc-shipping-zone-add-method").trigger('click');
                $("select[name='add_method_id']").val(woongkir_params.method_id).trigger('change');
            }

        }, 300);

    }

});
}(jQuery));
