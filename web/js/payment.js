/**
* Created with IntelliJ IDEA by Nick Chervyakov.
* User: Nikolay Chervyakov
* Date: 02.02.2016
* Time: 17:38
*/

can.Control('PaymentFormWidget', {
    pluginName: 'paymentForm'
},{
    init: function () {
        this.custom = this.element.find('[name="payment_selection[custom]"]');
        this.checkCustomAvailable();
    },

    '.js-payment-variant change': function (el, ev) {
         this.checkCustomAvailable();
    },

    '.custom-value keyup': function (el, ev) {
        this.estimateCoins();
    },

    checkCustomAvailable: function () {
        if (this.element.find(".js-payment-variant:checked").val() != 'custom') {
            this.custom.attr('disabled', 'disabled');
        } else {
            this.custom.removeAttr('disabled').focus();
            this.estimateCoins();
        }
    },

    estimateCoins: function () {
        var widget = this;

        $.ajax(Routing.generate('payments_estimate_coins'), {
            type: 'GET',
            dataType: 'json',
            data: {
                amount: this.custom.val()
            },
            success: function (res) {
                var block = widget.element.find('.coin-estimation-block');
                block.removeClass('hidden');
                block.find('.coin-estimation').text(res.coins);
            },
            error: function () {
                widget.element.find('.coin-estimation-block').addClass('hidden');
            }
        });
    }
});

$(function () {
    $(".js-payment-form").paymentForm();
});
