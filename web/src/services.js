(function () {
    "use strict";

    angular.module("spotdesk")
        .factory("$sdDepartments", ["$http", function ($http) {
            return {
                fetch: function () {
                    return $http.get("/departments");
                },

                fetchOne: function (department_id) {
                    return $http.get("/departments/" + department_id);
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
