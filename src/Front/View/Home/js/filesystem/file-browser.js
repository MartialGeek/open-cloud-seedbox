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

    var items = [];

    var browse = function(path) {
        var uri = getBrowsePathUri(path);
        var currentPath = getCurrentPath();

        $
            .getJSON(uri, function(res) {
                items = res.items;
                buildFileTab(res);
                window.history.pushState({'html': res, 'pageTitle': path}, path, uri);
            })
            .fail(function(data) {
                $('.main-content').first().addFlash('alert', data.responseJSON.message);

                if (uri != getBrowsePathUri(currentPath)) {
                    browse(currentPath);
                }
            });
    };

    var sortItemsAlphabetically = function(items, direction) {
        direction = direction || 'asc';

        items.sort(function(a, b) {
            var first = direction == 'asc' ? a : b;
            var second = direction == 'asc' ? b : a;

            if (first.filename > second.filename) {
                return 1;
            }

            if (first.filename < second.filename) {
                return -1;
            }

            return 0;
        });
    };

    var buildLink = function(linkPath, relativePath, text) {
        return '<a href="' + linkPath + '" data-file-type="directory" data-file-path="' +
            relativePath + '">' + text + '</a>';
    };

    var buildRows = function(items, options) {
        options = options || {
                sortOrder: 'asc',
                sortType: 'alpha'
            };

        var output = '';
        var directories = [];
        var files = [];

        var extractDirectoriesAndFiles = (function(items, directories, files) {
            for (var i = 0; i < items.length; i++) {
                if (items[i].isDir) {
                    directories.push(items[i]);
                } else {
                    files.push(items[i]);
                }
            }
        });

        extractDirectoriesAndFiles(items, directories, files);

        if (options.sortType == 'alpha') {
            sortItemsAlphabetically(directories, options.sortOrder);
            sortItemsAlphabetically(files, options.sortOrder);
        }

        var buildRow = (function(items) {
            var output = '';

            for (var i = 0; i < items.length; i++) {
                if (items[i].filename.charAt(0) == '.') {
                    return;
                }

                output+= '<tr><td>';

                if (items[i].isDir) {
                    output += buildLink(
                        getBrowsePathUri(items[i].relativePath),
                        items[i].relativePath,
                        items[i].filename
                    );
                } else {
                    output += items[i].filename;
                }

                output += '</td><td>';

                if (!items[i].isDir) {
                    output += '<a href="' + getUploadUri(items[i].fullPath) + '">Download</a>'
                } else {
                    output += '&nbsp;'
                }

                output += '</td>';
            }

            return output;
        });

        output += buildRow(directories);
        output += buildRow(files);

        return output;
    };

    var tableBody = $('#file-browser-tab').find('tbody');

    var buildFileTab = function(res) {
        var html = '';

        if (res.path != '/') {
            html += '<tr id="parent-link"><td>' +
                buildLink(getBrowsePathUri(res.parentPath), res.parentPath, '<-- Parent') +
                '</td><td>&nbsp;</td></tr>';
        }

        html += buildRows(res.items);

        tableBody.html(html);
    };

    $(document).on('click', 'a[data-file-type=directory]', function(event) {
        var path = $(this).attr('data-file-path');

        event.preventDefault();
        tableBody.html('');
        browse(path);
    });

    var sortContext = {
        sortType: 'alpha',
        sortOrder: 'asc'
    };

    $('#sort-by-filename').on('click', function() {
        var parentLink = $('#parent-link');
        var currentSortOrder = sortContext.sortOrder;

        tableBody.html('');
        tableBody.append(parentLink);
        sortContext.sortOrder = currentSortOrder == 'asc' ? 'desc' : 'asc';
        tableBody.append(buildRows(items, sortContext));
    });

    browse(getCurrentPath());
});
