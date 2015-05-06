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

can.Control('ApplicationControl', {
    pluginName: 'applicationControl',
    defaults: {}
}, {
    init: function () {
        this.timer = null;
        this.fetchNewMessages();
        this.unreadMessagesIndicator = this.element.find('.js-total-unread-messages');
    },

    '{window} added.message': function (el, ev, d) {
        var data = d && d.data || {};

        var chatWidget = $('.chat-widget '),
            companionId = parseInt(chatWidget.data('companion-id'), 10),
            sameUserInChat = chatWidget.length && companionId == parseInt(data.companionId, 10);

        if (!sameUserInChat) {
            if (data && data.hasOwnProperty('totalUnreadMessages')) {
                this.updateUnreadMessagesCount(data.totalUnreadMessages);
            }
            ion.sound.play("button_tiny");
        }
    },

    '{window} read.message': function (el, ev, d) {
        var data = d && d.data || {};
        if (data && data.hasOwnProperty('totalUnreadMessages')) {
            this.updateUnreadMessagesCount(data.totalUnreadMessages);
        }
    },

    updateUnreadMessagesCount: function (count) {
        count = parseInt(count, 10);
        this.unreadMessagesIndicator.text(count);

        if (count > 0) {
            this.unreadMessagesIndicator.removeClass('hidden');

        } else {
            this.unreadMessagesIndicator.addClass('hidden');
        }
    },

    fetchNewMessages: function () {
        return;
        var widget = this;
        $.ajax(Routing.generate('queue_fetch_new_messages'), {
            type: 'GET',
            timeout: 30000,
            complete: function () {
                // Schedule new message fetching
                var fetcher = widget.proxy(widget.fetchNewMessages);
                widget.timer = window.setTimeout(fetcher, 10000);
            }
        }).success(function (res) {
            if (res.messages && res.messages.length) {
                res.messages.forEach(function (message) {
                    if (typeof message == 'object') {
                        $(window).trigger(message.name, message);
                    }
                });
            }
        });
    }
});

jQuery(function ($) {
    $('input[type="file"]').bootstrapFileInput();
    $('body').applicationControl();
    $('#addCoinsDialog').addCoinsDialogControl();

    $('.js-header-coins').on('click', function () {
        App.showAddCoinsDialog();
    });

    var socket = io(App.parameters.socket_io_host);
    socket.on('connect', function () {
        socket.on('new_messages', function (data) {
            console.log(data);
            $.notify(JSON.stringify(data), {
                position: 'bottom left',
                autoHideDelay: 15000
            });
        });
    });
});