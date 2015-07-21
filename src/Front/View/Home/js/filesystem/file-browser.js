$(function() {
    var uriMap = {
        browsePath: '/file-browser/path',
        upload: '/upload'
    };

    var getBrowsePathUri = function(path) {
        var uri = uriMap.browsePath;

        if (path != '/') {
            uri += '/' + path;
        }

        return uri;
    };

    var getUploadUri = function(filePath) {
        return uriMap.upload + '/?filename=' + filePath;
    };

    var getCurrentPath = function() {
        return extractPathFromUri(window.location.pathname)
    };

    var extractPathFromUri = function(uri) {
        var path = uri.substr(uriMap.browsePath.length, uri.length);

        return path == '' ? '/' : path.substr(1);
    };

    var browse = function(path, callback) {
        var uri = getBrowsePathUri(path);
        var currentPath = getCurrentPath();

        $
            .getJSON(uri, function(res) {
                callback(res);
                window.history.pushState({'html': res, 'pageTitle': path}, path, uri);
            })
            .fail(function(data) {
                $('.main-content').first().addFlash('alert', data.responseJSON.message);

                if (uri != getBrowsePathUri(currentPath)) {
                    browse(currentPath, buildFileTab);
                }
            });
    };

    var tableBody = $('#file-browser-tab').find('tbody');

    var buildFileTab = function(res) {
        var html = '';

        var buildLink = function(linkPath, relativePath, text) {
            return '<a href="' + linkPath + '" data-file-type="directory" data-file-path="' +
                relativePath + '">' + text + '</a>';
        };

        if (res.path != '/') {
            html += '<tr><td>' + buildLink(getBrowsePathUri(res.parentPath), res.parentPath, '<-- Parent') +
                '</td><td>&nbsp;</td></tr>';
        }

        res.items.forEach(function(item) {
            html+= '<tr><td>';

            if (item.isDir) {
                html += buildLink(getBrowsePathUri(item.relativePath), item.relativePath, item.filename);
            } else {
                html += item.filename;
            }

            html += '</td><td>';

            if (!item.isDir) {
                html += '<a href="' + getUploadUri(item.fullPath) + '">Download</a>'
            } else {
                html += '&nbsp;'
            }

            html += '</td>';
        });

        tableBody.html(html);
    };

    $(document).on('click', 'a[data-file-type=directory]', function(event) {
        var path = $(this).attr('data-file-path');

        event.preventDefault();
        tableBody.html('');
        browse(path, buildFileTab);
    });

    browse(getCurrentPath(), buildFileTab);
});
