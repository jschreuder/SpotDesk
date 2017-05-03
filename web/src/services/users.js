(function () {
    "use strict";

    angular.module("spotdesk")
        .factory("$sdUsers", ["$http", "$sdHash", function ($http, $sdHash) {
            return {
                fetch: function () {
                    return $http.get("/users");
                },

                fetchOne: function (email) {
                    return $http.get("/users/" + btoa(email));
                },

                create: function (email, display_name, password, role) {
                    return $http.post("/users", {
                        email: email,
                        display_name: display_name,
                        password: $sdHash(password),
                        role: role
                    });
                },

                update: function (email, display_name, role, active) {
                    return $http.put("/users/" + btoa(email), {
                        display_name: display_name,
                        role: role,
                        active: active
                    });
                },

                saveDepartments: function (email, departmentIds) {
                    var departments = [];
                    departmentIds.forEach(function (departmentId) {
                        departments.push({ department_id: departmentId });
                    });

                    return $http.put("/users/" + btoa(email) + "/departments", {
                        departments: departments
                    });
                },

                delete: function (email) {
                    return $http.delete("/users/" + btoa(email));
                }
            };
        }]);
})();
