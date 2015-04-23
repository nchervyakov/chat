/**
* Created with IntelliJ IDEA by Nick Chervyakov.
* User: Nikolay Chervyakov
* Date: 23.04.2015
* Time: 17:30
*/

/**
 * Controls the profile
 */
can.Control('UserPhotosWidget', {
    pluginName: 'userPhotosWidget',
    defaults: {}
}, {
    init: function () {
    },

    '.js-remove-photo-link click': function (el, ev) {
        ev.preventDefault();
        var id = el.data('photo-id'),
            thumb = el.closest('.user-photo');

        thumb.addClass('hidden');

        $.ajax(Routing.generate('profile_delete_photo', {photo: id}), {
            type: 'post',
            timeout: 30000
        }).success(function () {
            thumb.remove();

        }).error(function () {
            thumb.removeClass('hidden');
        });
    }
});

jQuery(function ($) {
    $('.js-user-photos').userPhotosWidget();
});