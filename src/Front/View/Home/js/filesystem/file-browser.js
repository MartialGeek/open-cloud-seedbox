'use strict';

var fileBrowser = {};

/**
 * The File entity.
 *
 * @param {Object} data - An object containing the file data.
 * @param {string} data.filename - The name of the file.
 * @param {bool} data.isDir - If the file is a directory.
 * @param {string} data.relativePath - The relative path of the file.
 * @param {string} data.fullPath - The full path of the file in filesystem.
 * @constructor
 */
fileBrowser.File = function(data) {
    this.filename = m.prop(data.filename);
    this.isDir = m.prop(data.isDir);
    this.relativePath = m.prop(data.relativePath);
    this.fullPath = m.prop(data.fullPath);
};

/**
 * Contains a list of files.
 *
 * @param {Object} data - An object containing the files, the current path and the parent path.
 * @param {fileBrowser.File[]} data.files - An array of files.
 * @param {string} data.currentPath - The path of the fileset.
 * @param {string} data.parentPath - The parent path of the fileset.
 * @constructor
 */
fileBrowser.FileList = function(data) {
    this.files = m.prop(data.files);
    this.currentPath = m.prop(data.currentPath);
    this.parentPath = m.prop(data.parentPath);
};

fileBrowser.Repository = {};

/**
 * Retrieves the files from the given path.
 *
 * @param {string} path - The path of the files.
 * @returns {_mithril.MithrilPromise<T>|fileBrowser.FileList}
 */
fileBrowser.Repository.findInPath = function(path) {
    if (path != "") {
        path = "/" + path;
    }

    return m.request({
        method: "GET",
        url: "/api/file-browser/path" + path,
        unwrapSuccess: function(response) {
            var files = [];

            for (var i = 0; i < response.items.length; i++) {
                files.push(new fileBrowser.File(response.items[i]));
            }

            return new fileBrowser.FileList({
                files: files,
                currentPath: response.path,
                parentPath: response.parentPath
            });
        }
    });
};

/**
 * Sorts the given files.
 *
 * @param {fileBrowser.File[]} files
 * @param {Object} [options]
 * @param {string} [options.order=asc]
 * @param {string} [options.type=alpha]
 */
fileBrowser.FileList.sort = function(files, options) {
    options = options || { order: "asc", type: "alpha" };

    files.sort(function(a, b) {
        var first = options.order == 'asc' ? a : b;
        var second = options.order == 'asc' ? b : a;

        if (first.filename().toLowerCase() > second.filename().toLowerCase()) {
            return 1;
        }

        if (first.filename().toLowerCase() < second.filename().toLowerCase()) {
            return -1;
        }

        return 0;
    });
};

fileBrowser.vm = (function() {
    var vm = {};

    /**
     * Stores a list of the files found in the given path.
     *
     * @param {string} path - The path of the files.
     */
    vm.find = function(path) {
        vm.list = fileBrowser.Repository.findInPath(path);
    };

    /**
     * Sorts the files.
     */
    vm.sort = function() {
        fileBrowser.FileList.sort(vm.list().files(), { order: m.route.param('sort'), type: "alpha" });
    };

    return vm;
}());

fileBrowser.controller = function() {
    fileBrowser.vm.find(m.route.param('path'));
};

fileBrowser.view = function() {
    fileBrowser.vm.sort();

    var fileList = fileBrowser.vm.list();
    var rows = fileList.files().map(function(file) {
        if (file.filename().charAt(0) == '.') {
            return;
        }

        return m("tr", [
            m("td", file.isDir() ? [
                m("a[href='" + file.relativePath() + "?sort=asc']" , { config: m.route }, file.filename())
            ] : file.filename()),
            m("td", "")
        ]);
    });

    var sortOrder = m.route.param('sort') == "asc" ? "desc" : "asc";
    var currentPath = m.route.param('path') == "" ? "/" : "/" + m.route.param('path');

    var table = [
        m("thead", [
            m("tr", [
                m("th", { class: "file-browser-file" }, [
                    m("a[href='" + currentPath + "?sort=" + sortOrder + "']", {
                        onclick: fileBrowser.vm.sort,
                        config: m.route
                    }, "File")
                ]),
                m("th", { class: "file-browser-actions" }, "Actions")
            ])
        ])
    ];

    if (fileList.currentPath() != fileList.parentPath()) {
        table.push(m("tbody", [
            m("tr", [
                m("td", [
                    m("a[href='" + fileList.parentPath() + "?sort=asc']", { config: m.route }, "<-- Parent")
                ])
            ]),
            rows
        ]));
    } else {
        table.push(m("tbody", rows));
    }

    return table;
};

m.route.mode = "hash";

m.route(document.querySelector("#file-browser-tab"), "/?sort=asc", {
    "/:path...": fileBrowser
});
