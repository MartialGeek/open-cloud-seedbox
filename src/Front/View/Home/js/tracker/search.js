$(function() {
    var links = $('[data-action=torrent-add]');

    links.each(function() {
        $(this).on('click', function(event) {
            event.preventDefault();
            var downloadModal = $('#download-modal');

            downloadModal.foundation('reveal', 'open');

            $.get($(this).attr('href'), function() {
                $('#download-modal-content').html('Your torrent was successfully added to the download queue.');
            }).fail(function(jqXHR, textStatus) {
                $('#download-modal-content').html('Oops! An error occurred: ' + textStatus);
            })
        });
    });
});
