(function () {
    "use strict";

    angular.module("spotdesk")
        .factory("$sdDepartments", ["$http", function ($http) {
            return {
                fetch: function () {
                    return $http.get("/departments");
                }
            };
        }])

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
