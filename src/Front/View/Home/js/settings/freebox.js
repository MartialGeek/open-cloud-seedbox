$(function() {

    var trackId, appToken, challenge = null;

    function openSession() {
        $
            .post('/freebox/open-session', {
                'app_token': appToken,
                'challenge': challenge
            },function() {
                alert('Session successfully opened.')
            })
            .fail(function(error) {
                console.log(error.responseText);
            });
    }

    function getAuthorizationStatus() {
        $
            .get('/freebox/authorization-status/' + trackId, function(data) {
                if (data.status == 'success') {
                    challenge = data.challenge

                    openSession();
                } else if (data.status == 'pending') {
                    console.log('Authorization is pending, retry in 2 seconds...');
                    setTimeout(getAuthorizationStatus(), 2000);
                }
            }, 'json')
            .fail(function(error) {
                console.log(error.responseText);
            });
    }

    function askPermission() {
        $
            .post('/freebox/ask-permission', function(data) {
                trackId = data.result.track_id;
                appToken = data.result.app_token;

                getAuthorizationStatus();
            }, 'json')
            .fail(function(error) {
                console.log(error.responseText)
            });
    }

    $('#save-freebox-settings').on('click', function(event) {
        var form = $('#form-freebox-settings');
        event.preventDefault();

        if (confirm('Do you want to open a session?')) {
            askPermission();
        } else {
            form.submit();
        }
    });
});