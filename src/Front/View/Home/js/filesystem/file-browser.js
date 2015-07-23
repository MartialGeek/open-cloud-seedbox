'use strict';

var fileBrowser = {};

/**
 * The File entity.
 *
 * @param {Object} data
 * @constructor
 */
fileBrowser.File = function(data) {
    this.filename = m.prop(data.filename);
    this.isDir = m.prop(data.isDir);
    this.relativePath = m.prop(data.relativePath);
    this.fullPath = m.prop(data.fullPath);
};

/**
 * Returns a list of the files in the given path.
 *
 * @param {string} path
 * @param {Object} context
 * @returns {fileBrowser.File[]}
 */
fileBrowser.File.list = function(path, context) {
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

            context.currentPath = response.path;
            context.parentPath = response.parentPath;

            return files;
        }
    });
};

/**
 * Sorts the given files.
 *
 * @param {fileBrowser.File[]} files
 * @param {Object} options
 */
fileBrowser.File.sort = function(files, options) {
    options = options || { order: "asc", type: "alpha" };

    files.sort(function(a, b) {
        var first = options.order == 'asc' ? a : b;
        var second = options.order == 'asc' ? b : a;

        if (first.filename() > second.filename()) {
            return 1;
        }

        if (first.filename() < second.filename()) {
            return -1;
        }

        return 0;
    });
};

fileBrowser.vm = (function() {
    var vm = {};

    vm.pathContext = {};
    vm.sortContext = { order: "desc", type: "alpha" };
    vm.list = Array;

    vm.find = function(path) {
        vm.list = fileBrowser.File.list(path, vm.pathContext);
    };

    vm.sort = function() {
        vm.sortContext.order = vm.sortContext.order == "desc" ? "asc" : "desc";
        fileBrowser.File.sort(vm.list(), vm.sortContext);
    };

    return vm;
}());

fileBrowser.controller = function() {
    fileBrowser.vm.find(m.route.param('path'));
};

fileBrowser.view = function() {
    var files = fileBrowser.vm.list();
    var rows = files.map(function(file) {
        if (file.filename().charAt(0) == '.') {
            return;
        }

        return m("tr", [
            m("td", file.isDir() ? [
                m("a[href='" + file.relativePath() + "']" , { config: m.route }, file.filename())
            ] : file.filename()),
            m("td", "")
        ]);
    });

    var table = [
        m("thead", [
            m("tr", [
                m("th", { class: "file-browser-file" }, [
                    m("a", { onclick: fileBrowser.vm.sort }, "File")
                ]),
                m("th", { class: "file-browser-actions" }, "Actions")
            ])
        ])
    ];

    var context = fileBrowser.vm.pathContext;

    if (context.currentPath != context.parentPath) {
        table.push(m("tbody", [
            m("tr", [
                m("td", [
                    m("a[href='" + context.parentPath + "']", { config: m.route }, "<-- Parent")
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

m.route(document.querySelector("#file-browser-tab"), "/", {
    "/:path...": fileBrowser
});
