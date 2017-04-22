(function () {
    "use strict";

    angular.module("spotdesk")

        .factory("$title", function () {
            return {
                _current: null,

                get: function () {
                    return this._current;
                },

                change: function (newTitle) {
                    this._current = newTitle;
                }
            };
        })

        .factory("$tickets", ["$http", function ($http) {
            return {
                fetchOpen: function () {
                    var result = [];
                    $http.get("/tickets").then(function (response) {
                        angular.forEach(response.data.tickets, function (ticket) {
                            result.push(ticket);
                        });
                    }, function () {
                        alert("tickets_load_failed")
                    });
                    return result;
                }
            };
        }]);
})();
