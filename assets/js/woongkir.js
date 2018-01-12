(function ($, w) {

    "use strict";

    function woongkirStoreProvince(callback) {
        var data = w.store.get(woongkir_params.json.province_key);
        if (typeof data == 'undefined' || data == 'undefined' || !data) {
            $.getJSON(woongkir_params.json.province_url, function (data) {
                data.sort(function (a, b) {
                    return (a.province_name > b.province_name) ? 1 : ((b.province_name > a.province_name) ? -1 : 0);
                });
                w.store.set(woongkir_params.json.province_key, data);
                if (typeof callback === 'function') {
                    callback(data);
                }
            });
        }
    }

    function woongkirGetProvince() {
        var data = w.store.get(woongkir_params.json.province_key);
        if (typeof data == 'undefined' || data == 'undefined' || !data) {
            return [];
        }
        return data;
    }

    function woongkirStoreCity(callback) {
        var data = w.store.get(woongkir_params.json.city_key);
        if (typeof data == 'undefined' || data == 'undefined' || !data) {
            $.getJSON(woongkir_params.json.city_url, function (data) {
                data.sort(function (a, b) {
                    return (a.city_name > b.city_name) ? 1 : ((b.city_name > a.city_name) ? -1 : 0);
                });
                w.store.set(woongkir_params.json.city_key, data);
                if (typeof callback === 'function') {
                    callback(data);
                }
            });
        }
    }

    function woongkirGetCity() {
        var data = w.store.get(woongkir_params.json.city_key);
        if (typeof data == 'undefined' || data == 'undefined' || !data) {
            return [];
        }
        return data;
    }

    function woongkirStoreSubdistrict(callback) {
        var data = w.store.get(woongkir_params.json.subdistrict_key);
        if (typeof data == 'undefined' || data == 'undefined' || !data) {
            $.getJSON(woongkir_params.json.subdistrict_url, function (data) {
                data.sort(function (a, b) {
                    return (a.subdistrict_name > b.subdistrict_name) ? 1 : ((b.subdistrict_name > a.subdistrict_name) ? -1 : 0);
                });
                w.store.set(woongkir_params.json.subdistrict_key, data);
                if (typeof callback === 'function') {
                    callback(data);
                }
            });
        }
    }

    function woongkirGetSubdistrict() {
        var data = w.store.get(woongkir_params.json.subdistrict_key);
        if (typeof data == 'undefined' || data == 'undefined' || !data) {
            return [];
        }
        return data;
    }

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

    woongkirStoreProvince(); // Store custom province data to local storage.
    woongkirStoreCity(); // Store custom city data to local storage.
    woongkirStoreSubdistrict(); // Store custom subdistrict data to local storage.

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

        // Bind settings form on click .wc-shipping-zone-method-settings.
        $(document).on('click', '.wc-shipping-zone-method-settings', woongkirFormSettings);
    });

})(jQuery, window);