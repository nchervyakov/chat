/**
 * Created by Nikolay Chervyakov on 10.03.2015.
 */

/**
 * Controls the chat widget
 */
can.Control('ChatWidget', {
    pluginName: 'chatWidget',
    defaults: {
    }
}, {
    init: function () {
        this.form = this.element.find('.js-message-form');
        this.inputElement = this.form.find('.js-message-input');
        this.submitButton = this.form.find('.js-submit-button');
        this.companionId = this.element.data('companion-id');
        this.list = this.element.find('.chat');

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
        var widget = this;
        $.ajax(Routing.generate('chat_add_message', {companion_id: this.companionId}), {
            type: 'POST',
            data: {
                message: $.trim(this.inputElement.val())
            },
            timeout: 10000,
            beforeSend: function () {
                widget.submitButton.attr('disabled', 'disabled');
            },
            complete: function () {
                widget.submitButton.removeAttr('disabled');
            }
        }).success(function (res) {
            if (res.message) {
                widget.addNewMessages(res.message);
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
        });
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