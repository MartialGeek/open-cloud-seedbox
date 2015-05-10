$(function() {

    /**
     * Checks the status of the authorization.
     * @param {integer} trackId
     */
    function getAuthorizationStatus(trackId) {
        $
            .get('/freebox/authorization-status/' + trackId, function(data) {
                if (data.status == 'success') {
                    alert('The application was successfully authorized.');
                } else if (data.status == 'pending') {
                    console.log('Authorization is pending, retry in 2 seconds...');
                    setTimeout(getAuthorizationStatus(trackId), 2000);
                }
            }, 'json')
            .fail(function(error) {
                alert(error.responseText);
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
                alert(error.responseText);
            });
    }

    $('#send-authorization-request').on('click', function() {
        askPermission();
    });
});