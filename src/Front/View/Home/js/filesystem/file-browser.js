'use strict';

var fileBrowser = {};

fileBrowser.File = function(data) {
    this.filename = m.prop(data.filename);
    this.isDir = m.prop(data.isDir);
    this.relativePath = m.prop(data.relativePath);
    this.fullPath = m.prop(data.fullPath);
};

fileBrowser.File.list = function() {
    return m.request({method: "GET", url: "/api/file-browser/path", type: fileBrowser.File});
};

fileBrowser.vm = (function() {
    var vm = {};

    vm.init = function() {
        vm.list = fileBrowser.File.list();
    };

    return vm;
}());

fileBrowser.controller = function() {
    fileBrowser.vm.init();
};

fileBrowser.view = function() {
    return fileBrowser.vm.list().map(function(file) {
        if (file.filename().charAt(0) == '.') {
            return;
        }

        return m("tr", [
            m("td", file.isDir() ? [
                m("a", {
                    href: "/api/file-browser/path/" + file.relativePath()
                }, file.filename())
            ] : file.filename()),
            m("td", "")
        ]);
    });
};

m.mount(document.querySelector('#file-browser-tab tbody'), {
    controller: fileBrowser.controller,
    view: fileBrowser.view
});
