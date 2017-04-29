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
                },

                update: function (department_id, name, email) {
                    return $http.put("/departments/" + department_id, {
                        name: name,
                        email: email
                    });
                }
            };
        }]);
})();
