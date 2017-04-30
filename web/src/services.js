(function () {
    "use strict";

    angular.module("spotdesk")

        .factory("$sdMailboxes", ["$http", function ($http) {
            return {
                fetch: function () {
                    return $http.get("/mailboxes");
                },

                fetchOne: function (mailbox_id) {
                    return $http.get("/mailboxes/" + mailbox_id);
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
