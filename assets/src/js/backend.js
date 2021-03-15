var openSettingsModalTimeout;

function woongkirBackendGetUrlParams(url) {
	var params = {};
	var parser = document.createElement('a');

	parser.href = url;

	var query = parser.search.substring(1);
	var vars = query.split('&');

	for (var i = 0; i < vars.length; i++) {
		var pair = vars[i].split('=');

		if (pair.length === 2) {
			params[pair[0]] = decodeURIComponent(pair[1]);
		}
	}

	return params;
};

function woongkirBackendOpenSettingsModal() {
	var urlParams = woongkirBackendGetUrlParams(window.location.search);

	if (!urlParams.woongkir_settings) {
		return;
	}

	if (openSettingsModalTimeout) {
		clearTimeout(openSettingsModalTimeout);
	}

	openSettingsModalTimeout = setTimeout(function () {
		var methodsMatch = $(document).find('.wc-shipping-zone-method-type').filter(function () {
			return $(this).text() === woongkir_params.method_title;
		});

		if (methodsMatch.length) {
			$(methodsMatch).closest('tr').find('.row-actions .wc-shipping-zone-method-settings').trigger('click');
		} else {
			$('.wc-shipping-zone-add-method').trigger('click');
			$('select[name="add_method_id"]').val(woongkir_params.method_id).trigger('change');
		}
	}, 500);
}

function woongkirBackendRenderOriginLocations() {
	var fieldPrefix = 'woocommerce_woongkir_origin_location';

	var localeData = $.extend(true, {}, woongkir_params.locale.default, woongkir_params.locale.ID);

	$.each(woongkirShared.getFields(), function (fieldSuffix, fieldData) {
		var $field = $('#' + fieldPrefix + '_' + fieldSuffix);

		if (!$field || !$field.length) {
			return;
		}

		$field.off('change', fieldData.onChange);

		woongkirShared.getLocationData(fieldSuffix).then(function (results) {
			var options = woongkirShared.filterLocationData(results, fieldPrefix, fieldSuffix, fieldData);
			var placeholder = localeData[fieldSuffix] && localeData[fieldSuffix].placeholder || '';

			$field.attr({
				placeholder: placeholder,
			}).data({
				placeholder: placeholder,
			}).selectWoo({
				data: options,
				width: '100%',
			});

			$field.on('change', fieldData.onChange);
		});
	});
}

function woongkirBackendToggleApiKeyVisibility() {
	$('#woocommerce_woongkir_api_key_toggle').remove();

	$("#woocommerce_woongkir_api_key").each(function () {
		$(this).after('<button id="woocommerce_woongkir_api_key_toggle" type="button" class="button button-secondary"><span class="dashicons dashicons-visibility" style="margin-top: 4px;"></span></button>');
	});

	$('#woocommerce_woongkir_api_key_toggle').off('click');

	$('#woocommerce_woongkir_api_key_toggle').on('click', function (event) {
		event.preventDefault();

		var fieldType = $("#woocommerce_woongkir_api_key").attr('type');

		if ('password' === fieldType) {
			$("#woocommerce_woongkir_api_key").attr('type', 'text');
		} else {
			$("#woocommerce_woongkir_api_key").attr('type', 'password');
		}

		$(this).find('span').toggleClass('dashicons-visibility dashicons-hidden');
	});
}

function woongkirBackendInitCouriersSortable() {
	$(".woongkir-couriers").sortable({
		axis: 'y',
		cursor: 'move',
	});
}

function woongkirBackendHandleChangeAccountType(event) {
	var $accountType = $(event.target);

	if (!$accountType.is(':checked')) {
		return;
	}

	var accounts = $accountType.data('accounts');
	var accountType = $accountType.val();
	var accountSelected = accountType && accounts[accountType];

	if (!accountSelected) {
		return;
	}

	// Toggle volumetric converter fields
	var $volumetricCalculator = $('#woocommerce_woongkir_volumetric_calculator');

	if (accountSelected.volumetric) {
		$volumetricCalculator.closest('tr').show();
	} else {
		$volumetricCalculator.prop('checked', false).closest('tr').hide();
	}

	$volumetricCalculator.trigger('change');

	var couriers = $accountType.data('couriers');

	$.each(couriers, function (zoneId, zoneData) {
		var zoneCouriers = [];

		$.each(zoneData, function (courierId, courierData) {
			if (courierData.account && courierData.account.indexOf(accountType) !== -1) {
				zoneCouriers.push(courierId);
			}
		});

		$('#woongkir-couriers-' + zoneId).find('.woongkir-couriers-item').each(function () {
			if (zoneCouriers.length && zoneCouriers.indexOf($(this).data('id')) !== -1) {
				$(this).show();
			} else {
				$(this).hide().find('.woongkir-service--bulk').prop('checked', false).trigger('change');
			}
		});
	});

	if (!accountSelected.multiple_couriers) {
		$.each(couriers, function (zoneId) {
			var $firstCheckBox = $('#woongkir-couriers-' + zoneId).find('.woongkir-service--single:checked').first();

			if ($firstCheckBox && $firstCheckBox.length) {
				var $firstCheckBoxBulk = $firstCheckBox.closest('.woongkir-couriers-item').find('.woongkir-service--bulk');

				if ($firstCheckBoxBulk && $firstCheckBoxBulk.length) {
					$('#woongkir-couriers-' + zoneId).find('.woongkir-service--bulk').not($firstCheckBoxBulk).prop('checked', false).trigger('change');
				}
			}
		});
	}
}

function woongkirBackendHandleChangeVolumetricCalculator(event) {
	if ($(event.target).is(':checked')) {
		$('#woocommerce_woongkir_volumetric_divider').closest('tr').show();
	} else {
		$('#woocommerce_woongkir_volumetric_divider').closest('tr').hide();
	}
}

function woongkirBackendToggleCourierServices(event) {
	event.preventDefault();

	$(event.currentTarget)
		.find('.dashicons')
		.toggleClass('dashicons-admin-generic dashicons-arrow-up-alt2')
		.closest('.woongkir-couriers-item')
		.toggleClass('expanded');

}

function woongkirBackendSelectServicesBulk(event) {
	var $checkboxBulk = $(event.target);
	var $couriers = $checkboxBulk.closest('.woongkir-couriers');
	var $courierItem = $checkboxBulk.closest('.woongkir-couriers-item');

	var $accountType = $('.woongkir-account-type:checked');
	var accounts = $accountType.data('accounts');
	var accountType = $accountType.val();
	var accountSelected = accountType && accounts[accountType];

	var isChecked = $checkboxBulk.is(':checked');

	$courierItem.find('.woongkir-service--single').prop('checked', isChecked).trigger('change');

	if (isChecked && !accountSelected.multiple_couriers) {
		$couriers.find('.woongkir-couriers-item').not($courierItem).each(function () {
			$(this).find('.woongkir-service--bulk:checked').prop('checked', false).trigger('change');
			$(this).find('.woongkir-service--single:checked').prop('checked', false).trigger('change');
		});
	}
}

function woongkirBackendSelectServicesSingle(event) {
	var $checkbox = $(event.target);
	var $couriers = $checkbox.closest('.woongkir-couriers');
	var $courierItem = $checkbox.closest('.woongkir-couriers-item');
	var $accountType = $('.woongkir-account-type:checked');
	var accounts = $accountType.data('accounts');
	var accountType = $accountType.val();
	var accountSelected = accountType && accounts[accountType];

	var courierItemsSelected = $courierItem.find('.woongkir-service--single:checked').length;
	var courierItemsAvailable = $courierItem.find('.woongkir-service--single').length;

	$courierItem.find('.woongkir-couriers--selected').text(courierItemsSelected);
	$courierItem.find('.woongkir-couriers--available').text(courierItemsAvailable);

	var checkCheckboxBulk = courierItemsSelected > 0;
	var selectorCheckboxBulk = checkCheckboxBulk ? '.woongkir-service--bulk:not(:checked)' : '.woongkir-service--bulk:checked';

	$courierItem.find(selectorCheckboxBulk).prop('checked', checkCheckboxBulk);

	if ($checkbox.is(':checked') && !accountSelected.multiple_couriers) {
		$couriers.find('.woongkir-couriers-item').not($courierItem).each(function () {
			$(this).find('.woongkir-service--single:checked').prop('checked', false).trigger('change');
		});
	}
}

function woongkirBackendHandleClickTabNav(event) {
	event.preventDefault();
	var $link = $(event.target);
	var target = $link.attr('href');
	var $target = $(target);

	if (!$target || !$target.length) {
		return;
	}

	$('.woongkir-tab-nav-item').each(function () {
		if ($(this).is($link)) {
			$(this).addClass('active');
		} else {
			$(this).removeClass('active');
		}
	});

	$('.woongkir-tab-content').each(function () {
		if ($(this).is(target)) {
			$(this).addClass('active');
		} else {
			$(this).removeClass('active');
		}
	});
}

function woongkirBackendHandleWcBackboneModalLoaded(event, target) {
	if (!event || 'wc-modal-shipping-method-settings' !== target || 1 > $('.' + target).find('[name^="woocommerce_woongkir_"]').length) {
		return;
	}

	$('.' + target).addClass('wc-modal-shipping-method-settings--woongkir');

	woongkirBackendRenderOriginLocations();
	woongkirBackendInitCouriersSortable();
	woongkirBackendToggleApiKeyVisibility();

	$(document.body).off('change', '.woongkir-account-type', woongkirBackendHandleChangeAccountType);
	$(document.body).on('change', '.woongkir-account-type', woongkirBackendHandleChangeAccountType);
	$(document.body).find('.woongkir-account-type').trigger('change');

	$(document.body).off('change', '#woocommerce_woongkir_volumetric_calculator', woongkirBackendHandleChangeVolumetricCalculator);
	$(document.body).on('change', '#woocommerce_woongkir_volumetric_calculator', woongkirBackendHandleChangeVolumetricCalculator);
	$(document.body).find('#woocommerce_woongkir_volumetric_calculator').trigger('change');

	$(document.body).off('click', '.woongkir-couriers-toggle', woongkirBackendToggleCourierServices);
	$(document.body).on('click', '.woongkir-couriers-toggle', woongkirBackendToggleCourierServices);

	$(document.body).off('change', '.woongkir-service--bulk', woongkirBackendSelectServicesBulk);
	$(document.body).on('change', '.woongkir-service--bulk', woongkirBackendSelectServicesBulk);

	$(document.body).off('change', '.woongkir-service--single', woongkirBackendSelectServicesSingle);
	$(document.body).on('change', '.woongkir-service--single', woongkirBackendSelectServicesSingle);

	$(document.body).off('click', '.woongkir-tab-nav-item', woongkirBackendHandleClickTabNav);
	$(document.body).on('click', '.woongkir-tab-nav-item', woongkirBackendHandleClickTabNav);
	$(document.body).find('.woongkir-tab-nav-item').first().trigger('click');
}

$(document.body).on('wc_backbone_modal_loaded', woongkirBackendHandleWcBackboneModalLoaded);
$(document).ready(woongkirBackendOpenSettingsModal);
