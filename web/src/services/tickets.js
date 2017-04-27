(function () {
    "use strict";

    angular.module("spotdesk")
        .factory("$sdTickets", ["$http", function ($http) {
            return {
                fetch: function (status_type, limit, page, sort_by, sort_direction) {
                    return $http.get("/tickets", { params: {
                        status_type: status_type,
                        limit: limit,
                        page: page,
                        sort_by: sort_by,
                        sort_direction: sort_direction
                    } });
                },

                fetchOne: function (ticket_id) {
                    return $http.get("/tickets/" + ticket_id);
                },

                addReply: function (ticket_id, message, internal, status_update) {
                    return $http.post("/tickets/" + ticket_id, {
                        message: message,
                        internal: internal,
                        status_update: status_update
                    });
                },

                changeStatus: function (ticket_id, new_status) {
                    return $http.put("/tickets/" + ticket_id + "/status", { status: new_status });
                },

                changeDepartment: function (ticket_id, new_department_id) {
                    return $http.put("/tickets/" + ticket_id + "/department", { department_id: new_department_id });
                },

                delete: function (ticket_id) {
                    return $http.delete("/tickets/" + ticket_id);
                }
            };
        }]);
})();
