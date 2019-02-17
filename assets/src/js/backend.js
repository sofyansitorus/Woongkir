var woongkirBackend = {
    init: function () {
        woongkirBackend.bindEvents();
        woongkirBackend.maybeOpenModal();
    },
    bindEvents: function () {
        $(document.body).off('click', '.wc-shipping-zone-method-settings');
        $(document.body).on('click', '.wc-shipping-zone-method-settings', function (e) {
            $(document.body).off('wc_backbone_modal_loaded', woongkirBackend.loadForm);

            if ($(e.currentTarget).closest('tr').find('.wc-shipping-zone-method-type').text() === woongkir_params.method_title) {
                $(document.body).on('wc_backbone_modal_loaded', woongkirBackend.loadForm);
            }
        });

        $(document.body).off('change', '#woocommerce_woongkir_origin_province', woongkirBackend.loadFormCity);
        $(document.body).on('change', '#woocommerce_woongkir_origin_province', woongkirBackend.loadFormCity);

        $(document.body).off('change', '#woocommerce_woongkir_origin_city', woongkirBackend.loadFormSubdistrict);
        $(document.body).on('change', '#woocommerce_woongkir_origin_city', woongkirBackend.loadFormSubdistrict);

        $(document.body).off('change', '#woocommerce_woongkir_account_type', woongkirBackend.highlightFeature);
        $(document.body).on('change', '#woocommerce_woongkir_account_type', woongkirBackend.highlightFeature);

        $(document.body).off('change', '#woocommerce_woongkir_account_type', woongkirBackend.toggleCouriers);
        $(document.body).on('change', '#woocommerce_woongkir_account_type', woongkirBackend.toggleCouriers);

        $(document.body).off('change', '.woongkir-account-type', woongkirBackend.selectAccountType);
        $(document.body).on('change', '.woongkir-account-type', woongkirBackend.selectAccountType);

        $(document.body).off('change', '.woongkir-service.bulk', woongkirBackend.selectServicesBulk);
        $(document.body).on('change', '.woongkir-service.bulk', woongkirBackend.selectServicesBulk);

        $(document.body).off('change', '.woongkir-service.single', woongkirBackend.selectServices);
        $(document.body).on('change', '.woongkir-service.single', woongkirBackend.selectServices);
    },
    maybeOpenModal: function () {
        if (woongkir_params.show_settings) {
            setTimeout(function () {
                // Try show settings modal on settings page.
                var isMethodAdded = false;
                var methods = $(document).find('.wc-shipping-zone-method-type');
                for (var i = 0; i < methods.length; i++) {
                    var method = methods[i];
                    if ($(method).text() === woongkir_params.method_title) {
                        $(method).closest('tr').find('.row-actions .wc-shipping-zone-method-settings').trigger('click');
                        isMethodAdded = true;
                        return;
                    }
                }

                // Show Add shipping method modal if the shipping is not added.
                if (!isMethodAdded) {
                    $('.wc-shipping-zone-add-method').trigger('click');
                    $('select[name="add_method_id"]').val(woongkir_params.method_id).trigger('change');
                }

            }, 300);
        }
    },
    loadForm: function () {
        var provinceData = woongkirLocation.getProvince();
        var provinceParam = {
            data: [],
            placeholder: woongkir_params.text.placeholder.state
        };

        if (provinceData.length) {
            for (var i = 0; i < provinceData.length; i++) {
                provinceParam.data.push({
                    id: provinceData[i].province_id,
                    text: provinceData[i].province,
                });
            }
        }

        $('#woocommerce_woongkir_origin_province').selectWoo(provinceParam).trigger('change');

        $('#woocommerce_woongkir_account_type').trigger('change');
    },
    loadFormCity: function () {
        var cityParam = {
            data: [],
            placeholder: woongkir_params.text.placeholder.city
        };
        var $cityField = $('#woocommerce_woongkir_origin_city');
        var citySelected = $cityField.val();
        var cityMatch = '';

        var provinceSelected = $('#woocommerce_woongkir_origin_province').val();
        var provinceData = woongkirLocation.getProvince({ province_id: provinceSelected });
        if (provinceData) {
            var cityData = woongkirLocation.getCity({ province_id: provinceData.province_id }, 'filter');
            if (cityData) {
                for (var i = 0; i < cityData.length; i++) {
                    cityParam.data.push({
                        id: cityData[i].city_id,
                        text: cityData[i].type + ' ' + cityData[i].city_name,
                    });

                    if (citySelected === cityData[i].city_id) {
                        cityMatch = cityData[i].city_id;
                    }
                }
            }
        }

        $('#woocommerce_woongkir_origin_city').selectWoo(cityParam).val(cityMatch).trigger('change');
    },
    loadFormSubdistrict: function () {
        var subdistrictParam = {
            data: [],
            placeholder: woongkir_params.text.placeholder.address_2
        };
        var $subdistrictField = $('#woocommerce_woongkir_origin_subdistrict');
        var subdistrictSelected = $subdistrictField.val();
        var subdistrictMatch = '';

        var citySelected = $('#woocommerce_woongkir_origin_city').val();
        var cityData = woongkirLocation.getCity({ city_id: citySelected });
        if (cityData) {
            var subdistrictData = woongkirLocation.getSubdistrict({ city_id: cityData.city_id }, 'filter');
            if (subdistrictData) {
                for (var i = 0; i < subdistrictData.length; i++) {
                    subdistrictParam.data.push({
                        id: subdistrictData[i].subdistrict_id,
                        text: subdistrictData[i].subdistrict_name,
                    });

                    if (subdistrictSelected === subdistrictData[i].subdistrict_id) {
                        subdistrictMatch = subdistrictData[i].subdistrict_id;
                    }
                }
            }
        }

        $('#woocommerce_woongkir_origin_subdistrict').selectWoo(subdistrictParam).val(subdistrictMatch).trigger('change');
    },
    selectAccountType: function (e) {
        e.preventDefault();

        var selected = $(this).val();

        $(this).closest('tr').find('input').not($(this)).prop('disabled', false).prop('checked', false);

        $(this).prop('disabled', true);

        $('#woocommerce_woongkir_account_type').val(selected).trigger('change');
    },
    highlightFeature: function (e) {
        var selected = $(e.currentTarget).val();
        $('#woongkir-account-features').find('td, th')
            .removeClass('selected');
        $('#woongkir-account-features')
            .find('.woongkir-account-features-col-' + selected)
            .addClass('selected');
    },
    toggleCouriers: function (e) {
        var $accountType = $('#woocommerce_woongkir_account_type');
        var accounts = $accountType.data('accounts');
        var couriers = $accountType.data('couriers');
        var account = $accountType.val();

        for (var zoneId in couriers) {
            $('#woongkir-couriers-list-' + zoneId).hide();
            var multiple_coriers = 0;
            for (var courierId in couriers[zoneId]) {
                if (couriers[zoneId][courierId].account.indexOf(account) === -1) {
                    $('#woongkir-courier-box-' + zoneId + '-' + courierId)
                        .hide()
                        .find('.woongkir-service')
                        .prop('checked', false);
                } else {
                    $('#woongkir-couriers-list-' + zoneId)
                        .show();
                    $('#woongkir-courier-box-' + zoneId + '-' + courierId)
                        .show();
                }
                if (!accounts[account].multiple_coriers) {
                    if (multiple_coriers) {
                        $('#woongkir-courier-box-' + zoneId + '-' + courierId)
                            .find('.woongkir-service')
                            .prop('checked', false);
                    }
                    if ($('#woongkir-courier-box-' + zoneId + '-' + courierId)
                        .find('.woongkir-service.single:checked').length) {
                        multiple_coriers++;
                    }
                }
            }
        }
    },
    selectServicesBulk: function (e) {
        var $table = $(this).closest('table');
        var courierId = $(this).closest('.woongkir-courier-box').data('id');
        var $accountType = $('#woocommerce_woongkir_account_type');
        var account = $accountType.val();
        var accounts = $accountType.data('accounts');

        if ($(this).is(':checked')) {
            $table.find('.woongkir-service.single').prop('checked', true);
            if (!accounts[account].multiple_coriers) {
                $('.woongkir-courier-box')
                    .not('.' + courierId).find('.woongkir-service')
                    .prop('checked', false);
            }
        } else {
            $table.find('.woongkir-service.single').prop('checked', false);
        }
    },
    selectServices: function (e) {
        var $table = $(this).closest('table');
        var courierId = $(this).closest('.woongkir-courier-box').data('id');
        var $accountType = $('#woocommerce_woongkir_account_type');
        var account = $accountType.val();
        var accounts = $accountType.data('accounts');

        if (this.checked) {
            $table.find('.woongkir-service.bulk').prop({
                checked: true
            });
            if (!accounts[account].multiple_coriers) {
                $('.woongkir-courier-box').not('.' + courierId).find('.woongkir-service').prop('checked', false);
            }
        } else {
            if (!$table.find('.woongkir-service.single:checked').length) {
                $table.find('.woongkir-service.bulk').prop({
                    checked: false
                });
            }
        }
    }
}

$(document).ready(woongkirBackend.init);