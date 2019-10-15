var woongkirBackendLog = {
    init: function () {
        woongkirBackendLog.maybeInjectSendLogButton();
    },
    maybeInjectSendLogButton: function () {
        var $deleteLogButton = $('#log-viewer-select a.page-title-action');

        if (!$deleteLogButton.length) {
            return;
        }

        var deleteButtonLogHref = $deleteLogButton.attr('href');

        if (deleteButtonLogHref.indexOf('handle=woongkir_') === -1) {
            return;
        }

        var $sendLogButton = $deleteLogButton
            .clone()
            .attr('href', deleteButtonLogHref.replace('handle=woongkir_', 'send_log=woongkir_'))
            .html(woongkir_log_params.button_text + '<span class="spinner"></span>');

        $sendLogButton.on('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (confirm(woongkir_log_params.confirm_text)) {
                var data = _.extend({
                    action: 'woongkir_send_log',
                }, woongkirBackendLog.parseParams($sendLogButton.attr('href')));

                $.ajax({
                    method: 'POST',
                    url: woongkir_log_params.ajax_url,
                    data: data,
                    beforeSend: function () {
                        $sendLogButton.find('.spinner').css('visibility', 'visible');
                    },
                }).done(function (response) {
                    if (response.data) {
                        return alert(response.data);
                    }
                }).fail(function (error, textStatus, errorThrown) {
                    if (error.statusText) {
                        return alert(textStatus.toUpperCase() + ': ' + error.statusText);
                    }

                    alert(textStatus.toUpperCase() + ': ' + errorThrown);
                }).always(function () {
                    $sendLogButton.find('.spinner').css('visibility', 'hidden');
                });
            }
        });

        $deleteLogButton.after($sendLogButton);
    },
    parseParams: function (url) {
        var re = /([^&=]+)=?([^&]*)/g;
        var decodeRE = /\+/g;  // Regex for replacing addition symbol with a space
        var decode = function (str) {
            return decodeURIComponent(str.replace(decodeRE, " "));
        };

        var params = {}, e;

        while (e = re.exec(url)) {
            var paramKey = decode(e[1]);
            var paramValue = decode(e[2]);

            if (paramKey && paramKey.indexOf('http') !== 0) {
                params[paramKey] = paramValue;
            }
        }

        return params;
    }
}

$(document).ready(woongkirBackendLog.init);