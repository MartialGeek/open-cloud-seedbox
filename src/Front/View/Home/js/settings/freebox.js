$(function() {

    /**
     * Checks the status of the authorization.
     * @param {integer} trackId
     */
    function getAuthorizationStatus(trackId) {
        var container = $('.main-content').first();

        $
            .get('/freebox/authorization-status/' + trackId, function(data) {
                if (data.status == 'success') {
                    container.addFlash('success', 'The application was successfully authorized.');
                } else if (data.status == 'pending') {
                    console.log('Authorization is pending, retry in 2 seconds...');
                    setTimeout(getAuthorizationStatus(trackId), 2000);
                }
            }, 'json')
            .fail(function(error) {
                container.addFlash('alert', 'Oops!\n' + error.responseText)
            });
    }

    /**
     * Sends a permission request to the Freebox.
     */
    function askPermission() {
        $
            .post('/freebox/ask-permission', function(data) {
                getAuthorizationStatus(data.result.track_id);
            }, 'json')
            .fail(function(error) {
                var container = $('.main-content').first();
                container.addFlash('alert', 'Oops!\n\n' + error.responseText);
            });
    }

    $('#send-authorization-request').on('click', function() {
        askPermission();
    });

    $('#exportSettingsData').on('submit', function(event) {
        event.preventDefault();
        var form = $(this);
        var loader = form.find('i.loader');
        var container = $('.main-content').first();

        loader.css('display', 'inline-block');

        $
            .post($(form).attr('action'), $(form).serialize(), function() {
                container.addFlash('success', 'Your settings have been successfully exported.');
            })
            .fail(function(error) {
                container.addFlash('alert', 'Oops!\n\n' + error.responseText);
            })
            .always(function() {
                loader.css('display', 'none');
                $('#exportSettingsModal').foundation('reveal', 'close');
            });
    });
});