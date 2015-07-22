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
 * @returns {fileBrowser.File[]}
 */
fileBrowser.File.list = function(path) {
    if (path == "/") {
        path = "";
    }

    return m.request({method: "GET", url: "/api/file-browser/path" + path, type: fileBrowser.File});
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

    vm.init = function() {
        vm.list = fileBrowser.File.list("/");

        vm.sortContext = { order: "desc", type: "alpha" };

        vm.sort = function() {
            vm.sortContext.order = vm.sortContext.order == "desc" ? "asc" : "desc";
            fileBrowser.File.sort(vm.list(), vm.sortContext);
        };

        vm.load = function(path) {
            vm.list = fileBrowser.File.list(path);
        };
    };

    return vm;
}());

fileBrowser.controller = function() {
    fileBrowser.vm.init();
};

fileBrowser.view = function() {
    var files = fileBrowser.vm.list();
    var rows = files.map(function(file) {
        if (file.filename().charAt(0) == '.') {
            return;
        }

        return m("tr", [
            m("td", file.isDir() ? [
                m("a", {
                    href: "#",
                    "data-path": file.relativePath(),
                    onclick: m.withAttr("data-path", fileBrowser.vm.load)
                }, file.filename())
            ] : file.filename()),
            m("td", "")
        ]);
    });

    return [
        m("thead", [
            m("tr", [
                m("th", { class: "file-browser-file" }, [
                    m("a", {
                        href: "#",
                        id: "sort-by-filename",
                        onclick: fileBrowser.vm.sort
                    }, "File")
                ]),
                m("th", { class: "file-browser-actions" }, "Actions")
            ])
        ]),
        m("tbody", rows)
    ];
};

m.mount(document.querySelector('#file-browser-tab'), {
    controller: fileBrowser.controller,
    view: fileBrowser.view
});
