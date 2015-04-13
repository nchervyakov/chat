/**
* Created with IntelliJ IDEA by Nick Chervyakov.
* User: Nikolay Chervyakov
* Date: 26.02.2015
* Time: 16:10
*/

var App = window.App || {};

$.widget("custom.addCoinsDialog", $.ui.dialog, {
});

App.showAddCoinsDialog = function () {
    $('#addCoinsDialog').addCoinsDialog({
        title: 'Add coins',
        modal: true,
        resizable: false,
        buttons: {
            "Cancel": function () {
                $(this).addCoinsDialog('close')
            }
        }
    });
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
        console.log(this.element);
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
                //widget.element.closest('.ui-dialog').dialog('close');
            }
        }).success(function (res) {
            if (!res.success) {
                return;
            }
            App.updateHeaderCoins(res.amount);
            $(document).trigger('coins.added', res.amount);
        });
    }
});

jQuery(function ($) {
    $('input[type="file"]').bootstrapFileInput();
    $('#addCoinsDialog').addCoinsDialogControl();

    $('.js-header-coins').on('click', function () {
        App.showAddCoinsDialog();
    });
});