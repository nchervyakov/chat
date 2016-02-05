/**
* Created with IntelliJ IDEA by Nick Chervyakov.
* User: Nikolay Chervyakov
* Date: 10.03.2015
* Time: 11:02
*/
var App = window.App || {};

/**
 * Controls the chat widget
 */
can.Control('ChatWidget', {
    pluginName: 'chatWidget',
    defaults: {
        emoticonPopup: null,
        socket: null
    }
}, {
    init: function () {
        this.socket = this.options.socket;
        this.form = this.element.find('.js-message-form');
        this.inputElement = this.form.find('.js-message-input');
        this.submitButton = this.form.find('.js-submit-button');
        this.statsBlock = $('.js-chat-stats');
        this.companionId = this.element.data('companion-id');
        this.list = this.element.find('.chat');
        this.emoticons =  this.element.find('.js-emoticons');
        this.emoticonLink = this.element.find('[data-toggle=popover]');
        this.imageMessageInput = this.element.find('#imageMessageInput');
        this.sendImageButton = this.element.find('.js-send-image-btn');

        this.bindSocket();

        var widget = this;

        this.emoticonLink.popover({
            container: 'body',
            placement: 'right',
            content: this.emoticons[0],
            html: true
        });

        if ($.fn.uploadify) {
            //this.imageMessageInput.uploadify({
            //    swf: '/swf/uploadify.swf',
            //    uploader: Routing.generate('chat_add_image_message', {companion_id: this.companionId}),
            //    buttonText: 'Send image...',
            //    queueID: 'chatUploadifyQueue',
            //    formData: {
            //        PHPSESSID: jQuery.cookie('PHPSESSID')
            //    },
            //    onUploadSuccess: this.proxy(this.onUploadImageSuccess),
            //    onUploadError: this.proxy(this.onUploadImageError),
            //    onFallback: this.proxy(this.onUploadifyFallback),
            //    onSWFReady: function () {
            //    },
            //    onInit: function () {
            //        //var uploadifyChecker = function () {
            //        //    var uploadify = widget.imageMessageInput.data('uploadify');
            //        //    if (!uploadify.movieElement) {
            //        //        widget.onUploadifyFallback();
            //        //    }
            //        //};
            //        //window.setTimeout(uploadifyChecker, 1000);
            //    }
            //});
            this.onUploadifyFallback();
        }

        var allMessageIds = this.list.children('.js-message').map(function () { return parseInt($(this).data('id'), 10); }).toArray();
        if (!allMessageIds.length) {
            allMessageIds = [0];
        }
        this.latestMessageId = Math.max.apply(null, allMessageIds);
        this.timer = null;
        //this.fetchNewMessages();

        this.fetchingPrevMessages = false;

        setTimeout(function () {
            widget.scrollChatToBottom();
        }, 50);
        this.markMessagesSeen(5000);
    },

    '.js-message-form submit': function (el, ev) {
        ev.preventDefault();
        if (!$.trim(this.inputElement.val())) {
            return;
        }

        ion.sound.play("snap");

        this.createMessageRequest(this.proxy(this.sendAddMessageRequest, this.inputElement.val())).execute();
        //this.sendAddMessageRequest(this.inputElement.val());
    },

    '.js-message-input keypress': function (el, ev) {
        if (ev.key == 'Enter' || ev.keyCode == 13 || ev.keyCode == 10) {
            ev.preventDefault();

            if (ev.shiftKey || ev.ctrlKey) {
                this.inputElement.insertAtCaret("\n");

            } else {
                el.closest('form').submit();
            }
            return false;
        }

        return true;
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
    },

    '.js-previous-messages-link click': function (el, ev) {
        ev.preventDefault();
        if (this.fetchingPrevMessages) {
            return;
        }

        var beforeMessageId = parseInt(el.data('before-message-id'), 10);
        el.after('<img src="/images/ajax-loader-grey-bg.gif" alt="" class="ajax-loader" />');
        this.fetchPreviousMessages(beforeMessageId);
    },

    '.js-uploadify-form submit': function (el, ev) {
        ev.preventDefault();

        if (!el.find('#imageMessageInput').val()) {
            return;
        }

        this.createMessageRequest(this.proxy(this.sendImageMessageRequest)).execute();
    },

    '.js-message-actions .js-delete-link click': function (el, ev) {
        ev.preventDefault();

        var $message = el.closest('.js-message'),
            messageId = parseInt($message.data('id'), 10);

        el.addClass('hidden');

        $.ajax(Routing.generate('delete_own_message', {message_id: messageId}), {
            type: 'POST',
            data: {},
            timeout: 30000

        }).success(function (res) {
            if (res.success) {
                el.remove();
                $message.after(res.html);
                $message.remove();
            }
            el.removeClass('hidden');

        }).error(function () {
            el.removeClass('hidden');
        });
    },

    '.js-message-actions .js-complain-link click': function (el, ev) {
        ev.preventDefault();

        var $message = el.closest('.js-message'),
            messageId = parseInt($message.data('id'), 10);

        el.addClass('hidden');

        $.ajax(Routing.generate('complain_message', {message_id: messageId}), {
            type: 'POST',
            data: {},
            timeout: 30000

        }).success(function (res) {
            if (res.success) {
                el.remove();
                $message.after(res.html);
                $message.remove();
            }
            el.removeClass('hidden');

        }).error(function () {
            el.removeClass('hidden');
        });
    },

    onAddedMessage: function (data) {
        var sameUserInChat = parseInt(this.companionId, 10) == parseInt(data.companionId, 10);

        if (!sameUserInChat || this.hasMessageWithId(data.message.id)) {
            return;
        }

        if (this.latestMessageId < data.message.id) {
            this.latestMessageId = data.message.id;
        }

        this.addNewMessages(data.html);
        this.markMessagesSeen(5000);
    },

    hasMessageWithId: function (id) {
        return this.list.find('.js-message[data-id="' + id + '"]').length > 0;
    },

    bindSocket: function () {
        //console.log(['Socket: ', this.options.socket]);
        if (!this.socket) {
            //console.log('Socket inactive.');
            return;
        }
        //console.log('Bound chat page');
        var widget = this;
        this.socket.on('new-message', function (data) {
            console.log(['new-message', data]);
            widget.onAddedMessage(data);
        });

        this.socket.on('coins-changed', function (data) {
            App.updateHeaderCoins(data.coins);
        });

        this.socket.on('conversation-stats-changed', this.proxy(function (data) {
            if (data.stat_html) {
                this.statsBlock.html(data.stat_html);
            }
        }));

        //this.socket.on('messages-marked-read', function (data) {
        //    //console.log(['messages-marked-read', data]);
        //    widget.onReadMessages(data);
        //});
    },

    createMessageRequest: function (callback, deferred) {

        var defer = deferred || $.Deferred(),
            widget = this,
            request,
            exec = false;

        var execute = function () {
            if (exec) {
                return defer;
            }

            var d = callback();
            exec = true;
            d.done(function (res, success) {
                res = res || {};

                if (res.success) {
                    if ($.isFunction(success)) {
                        success(res);
                    }
                    defer.resolve();
                } else {
                    if (res.need_to_agree_to_pay) {
                        widget.showAgreeToPayDialog(res.message, request);
                    } else if (res.not_enough_money) {
                        //App.showAddCoinsDialog(request);
                        App.showBuyCoinsDialog(request);
                    }
                }

            }).fail(function () {
                defer.reject();
            });

            return defer;
        };

        var copy = function () {
            return widget.createMessageRequest(callback, defer);
        };

        request = {
            execute: execute,
            copy: copy
        };

        return request;
    },

    sendImageMessageRequest: function () {
        var widget = this,
            d = $.Deferred(),
            el = this.element.find('.js-uploadify-form'),
            realSender;

        realSender = function () {
            el.ajaxSubmit({
                success: widget.proxy(widget.onMessageResponse, d, function (res) {
                    widget.onSuccessfulMessage(res);
                    if (res.success) {
                        el[0].reset();
                        el.find('.file-input-name').remove();
                    }
                }),
                error: function () {
                    d.reject();
                }
            });
        };

        $.ajax(Routing.generate('chat_check_can_add_message', {companion_id: this.companionId}), {
            type: 'GET',
            data: {},
            timeout: 30000
        }).success(function (checkRes) {
            if (checkRes.success) {
                realSender();
            } else {
                widget.proxy(widget.onMessageResponse, d, realSender)(checkRes);
            }

        }).error(function () { d.reject(); });

        return d;
    },

    onUploadifyFallback: function () {
        this.sendImageButton.removeClass('hidden');
        this.imageMessageInput.uploadify('destroy');
        this.imageMessageInput = this.element.find('#imageMessageInput');
    },

    sendAddMessageRequest: function (message) {
        if (!$.trim(message)) {
            return;
        }

        var widget = this,
            d = $.Deferred();

        $.ajax(Routing.generate('chat_add_message', {companion_id: this.companionId}), {
            type: 'POST',
            data: {
                message: $.trim(message)
            },
            timeout: 30000,
            beforeSend: function () {
                widget.submitButton.attr('disabled', 'disabled');
            },
            complete: function () {
                widget.submitButton.removeAttr('disabled');
            }
        }).success(this.proxy(this.onMessageResponse, d, this.proxy(this.onSuccessfulMessage)))
          .error(function () { d.reject(); });

        return d;
    },

    onMessageResponse: function (d, success, res) {
        d.resolve(res, success);
    },

    onSuccessfulMessage: function (res) {
        if (res.success) {
            if (res.message) {
                if (!this.hasMessageWithId(res.id)) {
                    this.addNewMessages(res.message);
                }
                this.latestMessageId = res.id;
            }
            if (res.stat_html) {
                this.statsBlock.html(res.stat_html);
            }

            this.inputElement.val('');
            this.inputElement.focus();
            this.markMessagesSeen(5000);
            App.updateHeaderCoins(res.coins);

        }
    },

    showAgreeToPayDialog: function (message, request) {
        var widget = this,
            dialog = $('<div id="agreeToPayDialog"></div>').html(message).appendTo('body');
        dialog.dialog({
            modal: true,
            resizable: false,
            buttons: {
                "Pay": function () {
                    widget.sendAgreeToPayRequest(request);
                    $(this).dialog('close');
                },
                "Cancel": function () {
                    $(this).dialog('close');
                }
            }
        });
    },

    sendAgreeToPayRequest: function (request) {
        var widget = this;
        $.ajax(Routing.generate('chat_agree_to_pay', {companion_id: this.companionId}), {
            type: 'POST',
            data: {},
            timeout: 30000
        }).success(function (res) {
            if (res.success/* && widget.inputElement.val()*/) {
                if (!request) {
                    widget.sendAddMessageRequest(widget.inputElement.val());
                } else {
                    request.copy().execute();
                }
            }
        });
    },

    addNewMessages: function (messagesHtml) {
        var list;
        list = this.element.find('.chat');
        list.find('.no-messages').remove();
        list.append(messagesHtml);
        //this.inputElement.focus();
        this.scrollChatToBottom();
    },

    scrollChatToBottom: function () {
        //var list, messageContainer, containerHeight, listHeight;
        //list = this.element.find('.chat');
        //messageContainer = this.element.find('.chat-messages');
        //listHeight = list.outerHeight();
        //containerHeight = messageContainer.height();
        //if (listHeight > containerHeight) {
        //    messageContainer.scrollTop(listHeight - containerHeight);
        //}
        $(document).scrollTop(document.documentElement.scrollHeight);
    },

    fetchNewMessages: function () {
        return;
        var widget = this;
        $.ajax(Routing.generate('chat_get_new_messages', {companion_id: this.companionId}), {
            type: 'GET',
            data: {
                latest_message_id: this.latestMessageId
            },
            timeout: 30000,
            complete: function () {
                // Schedule new message fetching
                var fetcher = widget.proxy(widget.fetchNewMessages);
                widget.timer = window.setTimeout(fetcher, 5000);
            }
        }).success(function (res) {
            if (res.messages && res.latestMessageId > widget.latestMessageId) {
                widget.addNewMessages(res.messages);
                widget.latestMessageId = res.latestMessageId;
                ion.sound.play("button_tiny");
            }
            if (res.stat_html) {
                widget.statsBlock.html(res.stat_html);
            }

            widget.markMessagesSeen(5000);
            App.updateHeaderCoins(res.coins);
        });
    },

    fetchPreviousMessages: function (beforeMessageId) {
        var widget = this;
        this.fetchingPrevMessages = true;

        $.ajax(Routing.generate('chat_get_previous_messages', {companion_id: this.companionId}), {
            type: 'GET',
            data: {
                before_message_id: beforeMessageId
            },
            timeout: 30000,
            complete: function () {
                widget.fetchingPrevMessages = false;
            }
        }).success(function (res) {
            if (res.messages) {
                widget.element.find('.js-previous-messages-block').remove();
                widget.list.prepend(res.messages);
                widget.markMessagesSeen(5000);
            }
        });
    },

    onUploadImageSuccess: function(file, data/*, response*/) {
        var res = JSON.parse(data);
        if (res) {
            this.onMessageResponse(res);
        }
    },

    onUploadImageError: function(file, errorCode, errorMsg, errorString) {
        alert('The file ' + file.name + ' could not be uploaded: ' + errorString);
    },

    markMessagesSeen: function (milliseconds) {
        var widget = this,
            remover, messages, messageIds;
        milliseconds = milliseconds || 0;
        messages = widget.element.find('.chat .message.unseen').not('.marking-unseen');
        messages.addClass('marking-unseen');

        remover = function () {
            messages.removeClass('unseen');
            messages.removeClass('marking-unseen');

            messageIds = messages.map(function () { return $(this).data('id'); }).toArray();

            if (messageIds.length) {
                $.ajax(Routing.generate('chat_mark_messages_read', {companion_id: widget.companionId}), {
                    type: 'POST',
                    data: {messageIds: messageIds},
                    timeout: 30000
                }).success(function (res) {
                });
            }
        };

        window.setTimeout(remover, milliseconds);
    },

    destroy: function () {
        if (this.timer) {
            clearTimeout(this.timer);
        }
    }
});

/**
 * Controls the chat widget
 */
can.Control('ChatPage', {
    pluginName: 'chatPage',
    defaults: {
        emoticonPopup: null,
        socket: null
    }
}, {
    init: function () {
        this.socket = this.options.socket;
        this.element.find('.chat-widget').chatWidget({socket: this.options.socket});
        this.companionId = this.element.find('.chat-widget').data('companion-id');
        this.bindSocket();
    },

    '{window} added.message': function (el, ev, d) {
        var data = d && d.data || {};
        this.onAddedMessage(data);
    },

    '{window} read.message': function (el, ev, d) {
        var data = d && d.data || {};
        this.onReadMessages(data);
    },

    bindSocket: function () {
        //console.log(['Socket: ', this.options.socket]);
        if (!this.socket) {
            //console.log('Socket inactive.');
            return;
        }
        //console.log('Bound chat page');
        var widget = this;
        this.socket.on('new-message', function (data) {
            //console.log(['new-message', data]);
            widget.onAddedMessage(data);
        });

        this.socket.on('messages-marked-read', function (data) {
            //console.log(['messages-marked-read', data]);
            widget.onReadMessages(data);
        });

        this.socket.on('user-online-status-changed', this.proxy(function (data) {
            var friend = this.element.find('.chat-friends .friend[data-companion-id=' + data.user_id + ']');

            if (friend.length) {
                if (data.is_online) {
                    if (!friend.find('.photo .user-status').length) {
                        friend.find('.photo').append('<span class="user-status user-status-online"></span>')
                    }
                } else {
                    friend.find('.photo .user-status').remove();
                }
            }
        }));
    },

    onAddedMessage: function (data) {
        var sameUserInChat = parseInt(this.companionId, 10) == parseInt(data.companionId, 10);

        if (!sameUserInChat) {
            if (data && data.hasOwnProperty('conversationUnreadMessages')) {
                this.updateUnreadMessagesCount(data.companionId, data.conversationUnreadMessages);
            }
        }
    },

    onReadMessages: function (data) {
        if (data && data.hasOwnProperty('conversationUnreadMessages')) {
            this.updateUnreadMessagesCount(data.companionId, data.conversationUnreadMessages);
        }
    },

    updateUnreadMessagesCount: function (companionId, count) {
        if (!companionId) {
            return;
        }

        var $friendList = $('.friend-list'),
            $companion = $friendList.find('.friend[data-companion-id="' + companionId + '"]'),
            $indicator = $companion.find('.unread-messages-label'),
            $photo = $companion.find('.photo');

        if (!$indicator.length) {
            $photo.append('<span class="label label-primary unread-messages-label">0</span>');
            $indicator = $companion.find('.unread-messages-label');
        }

        count = parseInt(count, 10);
        $indicator.text(count);

        if (count > 0) {
            $indicator.removeClass('hidden');

        } else {
            $indicator.addClass('hidden');
        }
    }
});


jQuery(function ($) {
    $('.js-chat-page').chatPage({socket: App.socket});
});