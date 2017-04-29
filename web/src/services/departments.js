(function () {
    "use strict";

    angular.module("spotdesk")
        .factory("$sdDepartments", ["$http", "$sdAlert", function ($http, $sdAlert) {
            var srvc = {
                /**
                 * Does runtime caching of all departments once loaded
                 */
                _departmentsCache: [],

                /**
                 * takes a Department object and generates its path and full_name, requires
                 * all departments to have been retrieved before it can run.
                 */
                _buildDepartmentPath: function (department) {
                    var path = " / ";
                    var parent_id = department.parent_id;
                    while (parent_id) {
                        var parent = srvc._departmentsCache.getOne(parent_id);
                        path = " / " + parent.name + path;
                        parent_id = parent.parent_id;
                    }
                    department.path = path.substring(3);
                    department.full_name = department.path + department.name;
                },

                /**
                 * Returns array with all departments (possibly still loading). Will fetch the
                 * list fresh from the API when passed true as argument.
                 *
                 * This array also has 2 methods added:
                 * - getOne(string department_id) for fetching a specific department
                 * - updateOne(object department) for updating a department instance
                 */
                all: function (refresh) {
                    if (refresh === true || srvc._departmentsCache.length === 0) {
                        srvc.fetch().then(function (response) {
                            srvc._departmentsCache.length = 0; // clears the array
                            angular.forEach(response.data.departments, function (department) {
                                srvc._departmentsCache.push(department);
                            });
                            srvc._departmentsCache.forEach(function (department) {
                                srvc._buildDepartmentPath(department);
                            });
                        }, function () {
                            $sdAlert.error("departments_load_failed");
                        });
                    }

                    srvc._departmentsCache.getOne = function (department_id) {
                        var found = null;
                        srvc._departmentsCache.forEach(function (department) {
                            if (department.department_id === department_id) {
                                found = department;
                            }
                        });
                        return found;
                    };

                    srvc._departmentsCache.updateOne = function (newDepartment) {
                        srvc._departmentsCache.forEach(function (department, idx) {
                            if (department.department_id === newDepartment.department_id) {
                                srvc._buildDepartmentPath(newDepartment);
                                srvc._departmentsCache[idx] = newDepartment;
                            }
                        });
                    };

                    return srvc._departmentsCache;
                },

                /**
                 * Fetch all departments from API, returns a promise
                 */
                fetch: function () {
                    return $http.get("/departments");
                },

                /**
                 * Fetch a single department from API, returns a promise
                 */
                fetchOne: function (department_id) {
                    return $http.get("/departments/" + department_id);
                },

                create: function (name, email, parent_id) {
                    return $http.post("/departments", {
                        name: name,
                        email: email,
                        parent_id: parent_id || null
                    });
                },

                update: function (department_id, name, email, parent_id) {
                    return $http.put("/departments/" + department_id, {
                        name: name,
                        email: email,
                        parent_id: parent_id || null
                    });
                }
            };
            return srvc;
        }]);
})();
