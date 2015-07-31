'use strict';

var tracker = {};

/**
 * The ResultSet entity.
 *
 * @param {Object} data - An object containing the ResultSet data.
 * @param {string} data.query - The search query.
 * @param {int} data.total - The number of results.
 * @param {int} data.offset - The offset.
 * @param {int} data.limit - The limit.
 * @param {tracker.Result[]} data.results - The results.
 * @constructor
 */
tracker.ResultSet = function(data) {
    this.query = m.prop(data.query);
    this.total = m.prop(data.total);
    this.offset = m.prop(data.offset);
    this.limit = m.prop(data.limit);
    this.results = m.prop(data.results);
};

/**
 * The Result entity.
 *
 * @param {Object} data - An object containing the Result data.
 * @param {int} data.id - The ID of the result.
 * @param {string} data.name - The name of the torrent.
 * @param {tracker.Category} data.category - The category of the torrent.
 * @param {int} data.seeders - The number of seeders.
 * @param {int} data.leechers - The number of leechers.
 * @param {int} data.comments - The number of comments.
 * @param {bool} data.isVerified - The verification status of the torrent.
 * @param {Date} data.additionDate - The addition date of the torrent.
 * @param {int} data.size - The size of the torrent.
 * @param {int} data.timesCompleted - The number of completed torrents.
 * @param {string} data.owner - The owner of the torrent.
 * @param {string} data.privacy - The privacy of the torrent.
 * @constructor
 */
tracker.Result = function(data) {
    this.id = m.prop(data.id);
    this.name = m.prop(data.name);
    this.category = m.prop(data.category);
    this.seeders = m.prop(data.seeders);
    this.leechers = m.prop(data.leechers);
    this.comments = m.prop(data.comments);
    this.isVerified = m.prop(data.isVerified);
    this.additionDate = m.prop(data.additionDate);
    this.size = m.prop(data.size);
    this.timesCompleted = m.prop(data.timesCompleted);
    this.owner = m.prop(data.owner);
    this.privacy = m.prop(data.privacy);
};

tracker.Category = function(data) {
    this.id = m.prop(data.id);
    this.name = m.prop(data.name);
    this.subCategories = m.prop(data.subCategories);
    this.parentCategory = m.prop(data.parentCategory);
};

tracker.vm = (function() {
    var vm = {};

    /**
     * @type {tracker.ResultSet}
     */
    vm.resultSet = null;

    /**
     * Sends the request to the "search" method of the T411 API.
     * @param {string} url - The requested URL
     * @param {{}} data - The data sent
     * @param {string} data.terms - The query
     * @param {int} data.categoryId - The ID of the category
     */
    vm.search = function(url, data) {
        var queryString = "?tracker_search[terms]=" + data.terms + "&tracker_search[category_id]=" + data.categoryId;

        vm.resultSet = m.request({
            method: "GET",
            url: url + queryString,
            unwrapSuccess: function(response) {
                var results = [];

                response.torrents.forEach(function(result) {
                    result.category = new tracker.Category(result.category);
                    result.seeders = result.numberOfSeeders;
                    result.leechers = result.numberOfLeechers;
                    result.comments = result.numberOfComments;
                    results.push(new tracker.Result(result));
                });

                return new tracker.ResultSet({
                    query: response.query,
                    total: response.total,
                    offset: response.offset,
                    limit: response.limit,
                    results: results
                });
            }
        });
    };

    return vm;
}());

tracker.controller = function() {
    var searchForm = document.querySelector("#tracker-search-form");

    searchForm.addEventListener('submit', function(event) {
        event.preventDefault();

        var form = event.currentTarget;

        tracker.vm.search(form.action, {
            terms: form.elements[0].value,
            categoryId: form.elements[1].value
        });
    }, false);
};

tracker.view = function() {
    if (tracker.vm.resultSet == null) {
        return;
    }

    var resultSet = tracker.vm.resultSet();

    var numberOfResults = m("div.row", [
        m("div.small-centered", [
            m("p", resultSet.total() + " results.")
        ])
    ]);
    
    var tHeads = [
        "Name",
        "Verified",
        "Category",
        "Size",
        "Seeders",
        "Leechers",
        "Comments",
        "Times completed",
        "Addition date"
    ];

    var table = m("div.row", [
        m("table#tracker-search-result", {role: "grid"}, [
            m("thead", [
                m("tr", tHeads.map(function(head) {
                    return m("th", head);
                }))
            ]),
            m("tbody", resultSet.results().map(function(torrent) {
                return m("tr", [
                    m("td", [
                        m("a", torrent.name())
                    ]),
                    m("td", torrent.isVerified() ? "OK" : "Not yet"),
                    m("td", torrent.category().name()),
                    m("td", torrent.size()),
                    m("td", torrent.seeders()),
                    m("td", torrent.leechers()),
                    m("td", torrent.comments()),
                    m("td", torrent.timesCompleted()),
                    m("td", torrent.additionDate())
                ])
            }))
        ])
    ]);

    return [numberOfResults, table];
};

m.mount(document.querySelector("#tracker-search-result-container"), {
    controller: tracker.controller,
    view: tracker.view
});
