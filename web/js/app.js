$(document).foundation();

(function($) {
    $.fn.addFlash = function(type, message) {

        var dom =
            '<div class="row">' +
                '<div class="small-12 columns">' +
                    '<div data-alert class="alert-box ' + type + ' radius">' + message +
                        '<a href="#" class="close">&times;</a>' +
                    '</div>' +
                '</div>' +
            '</div>';

        this.prepend(dom);
        $(document).foundation();

        return this;
    };
})(jQuery);
