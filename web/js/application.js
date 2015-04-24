/**
* Created with IntelliJ IDEA by Nick Chervyakov.
* User: Nikolay Chervyakov
* Date: 26.02.2015
* Time: 16:10
*/

var App = window.App || {};

$.widget("custom.addCoinsDialog", $.ui.dialog, {
});

App.showAddCoinsDialog = function (request) {
    $('#addCoinsDialog').addCoinsDialog({
        title: 'Add coins',
        modal: true,
        resizable: false,
        buttons: {
            "Cancel": function () {
                $(this).addCoinsDialog('close')
            }
        }
    }).data('request', request);
};

App.updateHeaderCoins = function (amount) {
    $('.js-header-coins').text(amount);
};

can.Control('AddCoinsDialogControl', {
    pluginName: 'addCoinsDialogControl',
    defaults: {
        emoticonPopup: null
    }
}, {
    init: function () {

    },

    '.js-add-coins-link click': function (el, ev) {
        ev.preventDefault();
        var amount = parseInt(el.data('amount'), 10);
        this.sendAddCoinsRequest(amount);
    },

    sendAddCoinsRequest: function (amount) {
        var widget = this;
        widget.element.addCoinsDialog('close');

        $.ajax(Routing.generate('coins_add'), {
            type: 'POST',
            data: {
                amount: amount
            },
            timeout: 10000,
            complete: function () {
                widget.element.data('request', null);
            }
        }).success(function (res) {
            if (!res.success) {
                return;
            }
            var request = widget.element.data('request');

            App.updateHeaderCoins(res.amount);
            if (request) {
                request.copy().execute();
            }
            $(document).trigger('coins.added', res.amount);
        });
    },

    destroy: function () {
        this.element.data('request', null);
    }
});

jQuery(function ($) {
    $('input[type="file"]').bootstrapFileInput();
    $('#addCoinsDialog').addCoinsDialogControl();

    $('.js-header-coins').on('click', function () {
        App.showAddCoinsDialog();
    });
});