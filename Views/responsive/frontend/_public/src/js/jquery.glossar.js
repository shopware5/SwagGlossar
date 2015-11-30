;(function ($, undefined) {

    $.plugin('swGlossar', {

        /**
         * The default options.
         * Mainly contains the selectors for the events.
         * You can set those by using data-attributes in HTML e.g. "data-quantitySelector='abc'"
         */
        defaults: {
            /**
             * The selector for the self-built keyword-box on the glossar-page
             *
             * @property keywordBox
             * @type string
             */
            keywordBox: '.glossar--column-keyword'
        },

        /** Plugin constructor */
        init: function () {
            var me = this;

            me.applyDataAttributes();
            me.initKeyword();

            me.$keywordBox = $(me.opts.keywordBox);

            me.registerEvents();
        },

        /**
         * Method to hide all glossar content box except first one
         */
        initKeyword: function() {
            var me = this;

            $('.glossar--column-content').hide();
            $('.glossar--column-content:first').show().find('a').addClass('glossar-active');
        },

        /**
         * Method to register all the events
         */
        registerEvents: function () {
            var me = this;

            me._on(me.$keywordBox, 'click', $.proxy(me.onClickKeyword, me));
        },

        /**
         * Method to handle the self-built keyword
         *
         * @param event
         */
        onClickKeyword: function (event) {
            var me = this,
                $el = $(event.currentTarget);

            $el.toggleClass('glossar-active');
            $el.parent().find('div').slideToggle('fast');
        },

        /** Destroys the plugin */
        destroy: function () {
            this._destroy();
        }

    });

    $('.glossar--content').swGlossar();
})(jQuery);