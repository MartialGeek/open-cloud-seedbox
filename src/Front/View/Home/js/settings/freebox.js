$(function() {
    $('#freebox-get-app-token').on('click', function() {
        $.post(freeboxRoutes.getAppToken, {
            app_id: $('#freebox_settings_appId').val(),
            app_name: $('#freebox_settings_appName').val(),
            app_version: $('#freebox_settings_appVersion').val(),
            device_name: $('#freebox_settings_deviceName').val()
        }, function(data) {
            console.log(data);
        }, 'json');
    });

    $('#freebox-check-auth-status').on('click', function() {
        $.get(freeboxRoutes.getStatus, function(data) {
            console.log(data);
        }, 'json');
    });
});