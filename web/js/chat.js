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
            var list;
            if (res.message) {
                list = widget.element.find('.chat');
                list.find('.no-messages').remove();
                list.append(res.message);
                widget.inputElement.val('')
                    .focus();
            }
        });
    }
});

jQuery(function ($) {
    $('.chat-widget').chatWidget();
});