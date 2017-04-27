(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("departmentsController", ["$sdDepartments", "$sdAlert", function ($sdDepartments, $sdAlert) {
            var ctrl = this;
            ctrl.order = "name";
            ctrl.selected = [];
            ctrl.departments = [];

            $sdDepartments.fetch().then(function (response) {
                ctrl.departments = [];
                angular.forEach(response.data.departments, function (department) {
                    ctrl.departments.push(department);
                });
            }, function () {
                $sdAlert.error("departments_load_failed");
            });

            ctrl.getDepartment = function (departmentId) {
                var found = null;
                ctrl.departments.forEach(function (department) {
                    if (department.department_id === departmentId) {
                        found = department;
                    }
                });
                return found;
            };
        }]);

})();
