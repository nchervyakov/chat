/**
* Created with IntelliJ IDEA by Nick Chervyakov.
* User: Nikolay Chervyakov
* Date: 10.04.2015
* Time: 12:34
*/

/**
 * Controls the search
 */
can.Control('SearchWidget', {
    pluginName: 'searchWidget',
    defaults: {
    }
}, {
    init: function () {
        this.page = 1;
        this.finished = false;
        this.isLoadingOffline = false;
        this.isLoading = false;
        this.loader = can.mustache("<div class='row search-loader js-search-loader'><div class='col-md-12'><div class='well well-sm text-center'>Loading....</div></div></div>");
        this.pagesContainer = this.element.find('.search-pages');
        this.urlBase = this.element.data('url-base');
        this.loadPageIfNeeded();
    },

    '{window} scroll': function (el, ev) {
        this.loadPageIfNeeded();
    },

    addLoader: function () {
        this.pagesContainer.append(this.loader());
    },

    removeLoader: function () {
        this.pagesContainer.find('.js-search-loader').remove();
    },

    loadPageIfNeeded: function () {
        if (this.finished) {
            return;
        }

        var pos = this.element.position(),
            bottom = parseInt(pos.top + this.element.outerHeight(), 10),
            $window = $(window),
            scrollTop = $window.scrollTop(),
            windowHeight = $window.height(),
            normalizedBottom = bottom - scrollTop;

        var needLoad = !!(normalizedBottom >= 0 && normalizedBottom <= windowHeight);

        if (needLoad) {
            this.fetchNextPage();
        }
    },

    fetchNextPage: function () {
        var widget = this;

        if (this.isLoading || this.finished) {
            return;
        }
        this.isLoading = true;

        $.ajax(this.urlBase.replace('__PAGE__', this.page + 1).replace('__OFFLINE__', this.isLoadingOffline ? 1 : 0), {
            type: 'GET',
            data: {},
            timeout: 10000,
            beforeSend: function () {
                widget.addLoader();
            },
            complete: function () {
                widget.removeLoader();
                widget.isLoading = false;
            }
        }).success(function (res) {
            widget.removeLoader();
            widget.isLoading = false;

            if (res.html) {
                widget.pagesContainer.append(res.html);
            }

            widget.page = res.page;
            console.log(res, widget.isLoadingOffline);
            if (widget.isLoadingOffline) {
                if (res.page >= res.pageCount) {
                    widget.finished = true;
                }
            } else {
                if (res.page >= res.pageCount) {
                    widget.page = 0;
                    widget.isLoadingOffline = true;
                    widget.loadPageIfNeeded();
                }
            }
        });
    }
});

jQuery(function ($) {
    $('.search-widget').searchWidget();
});