(function () {
    "use strict";

    angular.module("spotdesk")

        .factory("$sdMailboxes", ["$http", function ($http) {
            return {
                fetch: function () {
                    return $http.get("/mailboxes");
                }
            };
        }])

        .factory("$sdStatuses", ["$http", function ($http) {
            return {
                fetch: function () {
                    return $http.get("/statuses");
                }
            };
        }]);
})();
