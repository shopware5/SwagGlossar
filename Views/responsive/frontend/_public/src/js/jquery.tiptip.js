;(function ($, undefined) {

    $.plugin('swTiptip', {

        init: function() {
            var me = this,
                width = me.$el.attr('data-width'),
                position = me.$el.attr('data-position');

            if (width) {
                me.$el.tipTip({
                    'width': width,
                    'defaultPosition': position
                });
            }
        },

        /** Destroys the plugin */
        destroy: function () {
            this._destroy();
        }

    });

    $('.tiptip').swTiptip();
})(jQuery);