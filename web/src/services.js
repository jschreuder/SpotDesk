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
        }])

        .factory("$users", ["$http", function ($http) {
            return {
                fetch: function () {
                    return $http.get("/users");
                },

                fetchOne: function (email) {
                    return $http.get("/users/" + btoa(email));
                },

                create: function (email, display_name, password) {
                    return $http.post("/users", {
                        email: email,
                        display_name: display_name,
                        password: password
                    });
                },

                saveDepartments: function (email, departmentIds) {
                    var departments = [];
                    departmentIds.forEach(function (departmentId) {
                        departments.push({ department_id: departmentId });
                    });

                    return $http.put("/users/" + btoa(email) + "/departments", {
                        email: email,
                        departments: departments
                    });
                },

                delete: function (email) {
                    return $http.delete("/users/" + btoa(email));
                }
            };
        }])

        .factory("$departments", ["$http", function ($http) {
            return {
                fetch: function () {
                    return $http.get("/departments");
                }
            };
        }])

        .factory("$mailboxes", ["$http", function ($http) {
            return {
                fetch: function () {
                    return $http.get("/mailboxes");
                }
            };
        }])

        .factory("$statuses", ["$http", function ($http) {
            return {
                fetch: function () {
                    return $http.get("/statuses");
                }
            };
        }]);
})();
