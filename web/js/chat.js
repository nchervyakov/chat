/**
 * Created by Nikolay Chervyakov on 10.03.2015.
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
        this.emoticonLink.popover({
            container: 'body',
            placement: 'right',
            content: this.emoticons[0],
            html: true
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
                widget.latestMessageId = res.id;
            }
            if (res.stat_html) {
                widget.statsBlock.html(res.stat_html);
            }
        });
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