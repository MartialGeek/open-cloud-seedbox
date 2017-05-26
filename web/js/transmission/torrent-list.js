$(function() {

    /**
     * Returns the human readable size of a file in octets.
     *
     * @param {int} size
     * @returns {string}
     */
    function getHumanReadableSize(size) {
        return size > 999999999 ?
            Math.round((size / 1000000000) * 100) / 100 + ' GB' :
            Math.round((size / 1000000) * 100) / 100 + ' MB';
    }

    $('span.meter').each(function() {
        var percentDone = $(this).attr('data-torrent-percent-done');
        $(this).css('width', percentDone + '%');
    });

    $('.action-upload-torrent').each(function() {
        $(this).on('click', function(event) {
            event.preventDefault();
            var container = $('.main-content').first();

            $
                .post($(this).attr('href'), function() {
                    container.addFlash('success', 'Element successfully added to the downloads queue.');
                })
                .fail(function(error) {
                    var response = JSON.parse(error.responseText);

                    container.addFlash('alert', 'Oops!\n' + response.message);
                });
        })
    });

    $('table#torrent-queue').find('tbody tr').each(function() {
        var $row = $(this);

        setInterval(function() {
            var percentDone = $row.find('[data-torrent-percent-done]').attr('data-torrent-percent-done');

            if (percentDone == 100) {
                return;
            }

            var dataUrl = $row.attr('data-torrent-data-url');

            $.get(dataUrl, function(data) {
                var percentDone = data.percentDone * 100;
                var downloaded = data.totalSize * data.percentDone;

                $row.find('[data-torrent-field=name] div.progress span.meter').css('width', percentDone + '%');
                $row.find('[data-torrent-field=downloaded]').html(getHumanReadableSize(downloaded));
                $row.find('[data-torrent-percent-done]').attr('data-torrent-percent-done', percentDone);
            }, 'json');
        }, 2000);
    });
});
