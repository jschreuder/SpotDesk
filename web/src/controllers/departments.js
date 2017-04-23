(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("departmentsController", ["$departments", "$adminMessage", function ($departments, $adminMessage) {
            var ctrl = this;
            ctrl.order = "name";
            ctrl.selected = [];
            ctrl.departments = [];

            $departments.fetch().then(function (response) {
                ctrl.departments = [];
                angular.forEach(response.data.departments, function (department) {
                    ctrl.departments.push(department);
                });
            }, function () {
                $adminMessage.error("departments_load_failed");
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
