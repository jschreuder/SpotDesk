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
        }])

        .controller("viewDepartmentController", ["$sdDepartments", "$stateParams", "$sdAlert",
            function ($sdDepartments, $stateParams, $sdAlert) {
                var ctrl = this;
                ctrl.department = null;
                ctrl.department_users = [];
                ctrl.department_mailboxes = [];

                ctrl.fetchDepartment = function () {
                    $sdDepartments.fetchOne($stateParams.department_id).then(function (response) {
                        ctrl.department = response.data.department;

                        ctrl.department_users = [];
                        angular.forEach(response.data.users, function (user) {
                            ctrl.department_users.push(user);
                        });

                        ctrl.department_mailboxes = [];
                        angular.forEach(response.data.mailboxes, function (mailbox) {
                            ctrl.department_mailboxes.push(mailbox);
                        });
                    }, function () {
                        $sdAlert.error("department_load_failed");
                    });
                };
                ctrl.fetchDepartment();
            }
        ]);

})();
