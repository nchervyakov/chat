/**
* Created with IntelliJ IDEA by Nick Chervyakov.
* User: Nikolay Chervyakov
* Date: 10.03.2015
* Time: 11:02
*/

/**
 * Controls the chat widget
 */
can.Control('ChatWidget', {
    pluginName: 'chatWidget',
    defaults: {
        emoticonPopup: null
    }
}, {
    init: function () {
        this.form = this.element.find('.js-message-form');
        this.inputElement = this.form.find('.js-message-input');
        this.submitButton = this.form.find('.js-submit-button');
        this.statsBlock = this.element.find('.js-chat-stats');
        this.companionId = this.element.data('companion-id');
        this.list = this.element.find('.chat');
        this.emoticons =  this.element.find('.js-emoticons');
        this.emoticonLink = this.element.find('[data-toggle=popover]');
        this.imageMessageInput = this.element.find('#imageMessageInput');

        this.emoticonLink.popover({
            container: 'body',
            placement: 'right',
            content: this.emoticons[0],
            html: true
        });

        this.imageMessageInput.uploadify({
            swf: '/swf/uploadify.swf',
            uploader: Routing.generate('chat_add_image_message', {companion_id: this.companionId}),
            buttonText: 'Send image...',
            queueID: 'chatUploadifyQueue',
            onUploadSuccess: this.proxy(this.onUploadImageSuccess),
            onUploadError: this.proxy(this.onUploadImageError)
        });

        var allMessageIds = this.list.children('.js-message').map(function () { return parseInt($(this).data('id'), 10); }).toArray();
        if (!allMessageIds.length) {
            allMessageIds = [0];
        }
        this.latestMessageId = Math.max.apply(null, allMessageIds);
        this.timer = null;
        this.fetchNewMessages()
    },

    '.js-message-form submit': function (el, ev) {
        ev.preventDefault();
        if (!$.trim(this.inputElement.val())) {
            return;
        }
        this.sendAddMessageRequest(this.inputElement.val());
    },

    '.js-message-input keypress': function (el, ev) {
        if (ev.ctrlKey && (ev.key == 'Enter' || ev.keyCode == 13 || ev.keyCode == 10)) {
            el.closest('form').submit();
        }
    },

    '.js-emoticon click': function (el, ev) {
        ev.preventDefault();
        this.inputElement.insertAtCaret(el.attr('title'));
    },

    '{emoticonPopup} .js-emoticon click': function (el, ev) {
        ev.preventDefault();
        this.inputElement.insertAtCaret(el.attr('title'));
        this.emoticonLink.popover('hide');
        this.options.emoticonPopup = null;
        this.on();
    },

    '.js-emoticon-invocation-link click': function (el, ev) {
        ev.preventDefault();
        var tip = el.data('bs.popover').$tip;
        this.options.emoticonPopup = tip;
        tip.addClass('emoticon-popover');
        this.on();
        //this.emoticons.popover('show');
    },

    '{window} click': function (el, ev) {
        var target = $(ev.target);
        if (!target.closest('.popover').length && !target.closest('.js-emoticon-invocation-link').length) {
            this.emoticonLink.popover('hide');
        }
    },

    '{document} coins.added': function (el, ev, amount) {
        App.updateHeaderCoins(amount);
        if (this.inputElement.val()) {
            this.sendAddMessageRequest(this.inputElement.val());
        }
    },

    sendAddMessageRequest: function (message) {
        if (!$.trim(message)) {
            return;
        }

        var widget = this;
        $.ajax(Routing.generate('chat_add_message', {companion_id: this.companionId}), {
            type: 'POST',
            data: {
                message: $.trim(message)
            },
            timeout: 10000,
            beforeSend: function () {
                widget.submitButton.attr('disabled', 'disabled');
            },
            complete: function () {
                widget.submitButton.removeAttr('disabled');
            }
        }).success(this.proxy(this.onSuccessfulMessage));
    },

    onSuccessfulMessage: function (res) {
        if (res.success) {
            if (res.message) {
                this.addNewMessages(res.message);
                this.latestMessageId = res.id;
            }
            if (res.stat_html) {
                this.statsBlock.html(res.stat_html);
            }

            App.updateHeaderCoins(res.coins);

        } else {
            if (res.need_to_agree_to_pay) {
                this.showAgreeToPayDialog(res.message);
            } else if (res.not_enough_money) {
                App.showAddCoinsDialog();
            }
        }
    },

    showAgreeToPayDialog: function (message) {
        var widget = this,
            dialog = $('<div id="agreeToPayDialog"></div>').html(message).appendTo('body');
        dialog.dialog({
            modal: true,
            resizable: false,
            buttons: {
                "Pay": function () {
                    widget.sendAgreeToPayRequest();
                    $(this).dialog('close');
                },
                "Cancel": function () {
                    $(this).dialog('close');
                }
            }
        });
    },

    sendAgreeToPayRequest: function () {
        var widget = this;
        $.ajax(Routing.generate('chat_agree_to_pay', {companion_id: this.companionId}), {
            type: 'POST',
            data: {},
            timeout: 10000
        }).success(function (res) {
            if (res.success && widget.inputElement.val()) {
                widget.sendAddMessageRequest(widget.inputElement.val());
            }
        });
    },

    addNewMessages: function (messagesHtml) {
        var list, messageContainer, containerHeight, listHeight;
        list = this.element.find('.chat');
        list.find('.no-messages').remove();
        list.append(messagesHtml);
        this.inputElement.val('')
            .focus();
        messageContainer = this.element.find('.chat-messages');
        listHeight = list.outerHeight();
        containerHeight = messageContainer.height();
        if (listHeight > containerHeight) {
            messageContainer.scrollTop(listHeight - containerHeight);
        }
    },

    fetchNewMessages: function () {
        var widget = this;
        $.ajax(Routing.generate('chat_get_new_messages', {companion_id: this.companionId}), {
            type: 'GET',
            data: {
                latest_message_id: this.latestMessageId
            },
            timeout: 5000,
            complete: function () {
                // Schedule new message fetching
                widget.timer = setTimeout(widget.proxy(widget.fetchNewMessages), 5000);
            }
        }).success(function (res) {
            if (res.messages && res.latestMessageId > widget.latestMessageId) {
                widget.addNewMessages(res.messages);
                widget.latestMessageId = res.latestMessageId;
            }
            if (res.stat_html) {
                widget.statsBlock.html(res.stat_html);
            }

            App.updateHeaderCoins(res.coins);
        });
    },

    onUploadImageSuccess: function(file, data, response) {
        var res = JSON.parse(data);
        if (res) {
            this.onSuccessfulMessage(res);
        }
    },

    onUploadImageError: function(file, errorCode, errorMsg, errorString) {
        alert('The file ' + file.name + ' could not be uploaded: ' + errorString);
    },

    destroy: function () {
        if (this.timer) {
            clearTimeout(this.timer);
        }
    }
});

jQuery(function ($) {
    $('.chat-widget').chatWidget();
});