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
    this.total = m.prop(parseInt(data.total, 10));
    this.offset = m.prop(parseInt(data.offset, 10));
    this.limit = m.prop(parseInt(data.limit, 10));
    this.results = m.prop(data.results);
};

/**
 * The Result entity.
 *
 * @param {Object} data - An object containing the Result data.
 * @param {int} data.id - The ID of the result.
 * @param {string} data.name - The name of the torrent.
 * @param {tracker.Category} data.category - The category of the torrent.
 * @param {int} data.numberOfSeeders - The number of seeders.
 * @param {int} data.numberOfLeechers - The number of leechers.
 * @param {int} data.numberOfComments - The number of comments.
 * @param {bool} data.isVerified - The verification status of the torrent.
 * @param {Date} data.additionDate - The addition date of the torrent.
 * @param {int} data.size - The size of the torrent.
 * @param {int} data.timesCompleted - The number of completed torrents.
 * @param {string} data.owner - The owner of the torrent.
 * @param {string} data.privacy - The privacy of the torrent.
 * @constructor
 */
tracker.Result = function(data) {
    this.id = m.prop(parseInt(data.id, 10));
    this.name = m.prop(data.name);
    this.category = m.prop(data.category);
    this.numberOfSeeders = m.prop(parseInt(data.numberOfSeeders, 10));
    this.numberOfLeechers = m.prop(parseInt(data.numberOfLeechers, 10));
    this.numberOfComments = m.prop(parseInt(data.numberOfComments, 10));
    this.isVerified = m.prop(data.isVerified);
    this.additionDate = m.prop(data.additionDate);
    this.size = m.prop(parseInt(data.size, 10));
    this.timesCompleted = m.prop(parseInt(data.timesCompleted, 10));
    this.owner = m.prop(data.owner);
    this.privacy = m.prop(data.privacy);
};

/**
 * The Category entity.
 *
 * @param {Object} data - An object containing the Category data.
 * @param {int} data.id - The ID of the category.
 * @param {string} data.name - The name of the category.
 * @param {tracker.Category[]} [data.subCategories] - The sub-categories if any.
 * @param {tracker.Category} [data.parentCategory] - The parent category if any.
 *
 * @constructor
 */
tracker.Category = function(data) {
    this.id = m.prop(parseInt(data.id, 10));
    this.name = m.prop(data.name);

    if (data.subCategories) {
        this.subCategories = m.prop(data.subCategories);
    }

    if (data.parentCategory) {
        this.parentCategory = m.prop(data.parentCategory);
    }
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
     * @param {Object} data - The data sent
     * @param {string} data.terms - The query
     * @param {int} data.categoryId - The ID of the category
     * @param {Function} callback
     */
    vm.search = function(url, data, callback) {
        var queryString = "?tracker_search[terms]=" + data.terms + "&tracker_search[category_id]=" + data.categoryId;

        vm.resultSet = m.request({
            method: "GET",
            url: url + queryString,
            unwrapSuccess: function(response) {
                if (callback) {
                    callback();
                }

                var results = [];

                response.torrents.forEach(function(result) {
                    result.category = new tracker.Category(result.category);
                    result.additionDate = new Date(result.additionDate);
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
    var loader = searchForm.querySelectorAll('div.loader')[0];

    searchForm.addEventListener('submit', function(event) {
        event.preventDefault();

        var form = event.currentTarget;
        loader.style.display = "block";

        tracker.vm.search(form.action, {
            terms: form.elements[0].value,
            categoryId: form.elements[1].value
        }, function() {
            loader.style.display = "none";
        });
    }, false);
};

tracker.view = function() {
    if (tracker.vm.resultSet == null) {
        return;
    }

    /**
     * Add a zero before a number if it is less than 10.
     *
     * @param {Number} int
     * @returns {string}
     */
    var zeroFill = function(int) {
        if (int.toString().length < 2) {
            return "0" + int;
        } else {
            return int.toString();
        }
    };

    /**
     * Returns a human readable representation of a file size.
     *
     * @param {number} size
     * @returns {string}
     */
    var convertSizeToHumanReadable = function(size) {
        var converted, unit;

        if (size > 999999999) {
            converted = size / 1000000000;
            unit = "GB";
        } else {
            converted = size / 1000000;
            unit = "MB";
        }

        return Number(converted).toFixed(2) + " " + unit;
    };

    var resultSet = tracker.vm.resultSet();

    var numberOfResults = m("div.row", [
        m("div.small-centered", [
            m("p", resultSet.results().length + " results.")
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
                var date = torrent.additionDate();
                var dateToString = date.getFullYear() + "-";
                dateToString += zeroFill(date.getMonth() + 1) + "-";
                dateToString += zeroFill(date.getDate());

                return m("tr", [
                    m("td", [
                        m("a[href='/tracker/download/" + torrent.id() + "']", torrent.name())
                    ]),
                    m("td", torrent.isVerified() ? "OK" : "Not yet"),
                    m("td", torrent.category().name()),
                    m("td", convertSizeToHumanReadable(torrent.size())),
                    m("td", torrent.numberOfSeeders()),
                    m("td", torrent.numberOfLeechers()),
                    m("td", torrent.numberOfComments()),
                    m("td", torrent.timesCompleted()),
                    m("td", dateToString)
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
