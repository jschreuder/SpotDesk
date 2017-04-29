(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("departmentsController", ["$sdDepartments", "$sdAlert", "$mdDialog",
            function ($sdDepartments, $sdAlert, $mdDialog) {
                var ctrl = this;
                ctrl.order = "name";
                ctrl.selected = [];
                ctrl.departments = [];

                ctrl.fetchDepartments = function () {
                    $sdDepartments.fetch().then(function (response) {
                        ctrl.departments = [];
                        angular.forEach(response.data.departments, function (department) {
                            ctrl.departments.push(department);
                        });
                    }, function () {
                        $sdAlert.error("departments_load_failed");
                    });
                };
                ctrl.fetchDepartments();

                ctrl.getDepartment = function (departmentId) {
                    var found = null;
                    ctrl.departments.forEach(function (department) {
                        if (department.department_id === departmentId) {
                            found = department;
                        }
                    });
                    return found;
                };

                ctrl.createDepartment = function(ev) {
                    ctrl.department = {
                        name: null,
                        parent_id: null,
                        email: null
                    };
                    $mdDialog.show({
                        contentElement: "#createDepartment",
                        parent: angular.element(document.body),
                        targetEvent: ev
                    });
                };
                ctrl.cancelDepartment = function () {
                    $mdDialog.cancel();
                };
                ctrl.submitDepartment = function () {
                    $sdDepartments.create(
                        ctrl.department.name, ctrl.department.parent_id, ctrl.department.email
                    ).then(function () {
                        $mdDialog.hide();
                        ctrl.fetchDepartments();
                    }, function () {
                        // @todo handle validation errors differently
                        $sdAlert.error("department_create_failed");
                    });
                };
            }
        ])

        .controller("viewDepartmentController", ["$sdDepartments", "$stateParams", "$sdAlert", "$mdDialog",
            function ($sdDepartments, $stateParams, $sdAlert, $mdDialog) {
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

                ctrl.editDepartment = function(ev) {
                    $mdDialog.show({
                        contentElement: "#editDepartment",
                        parent: angular.element(document.body),
                        targetEvent: ev
                    });
                };
                ctrl.cancelEditDepartment = function () {
                    $mdDialog.cancel();
                };
                ctrl.submitEditDepartment = function () {
                    $sdDepartments.update(
                        ctrl.department.department_id, ctrl.department.name, ctrl.department.email
                    ).then(function () {
                        $mdDialog.hide();
                        ctrl.fetchDepartment();
                    }, function () {
                        // @todo handle validation errors differently
                        $sdAlert.error("department_edit_failed");
                        ctrl.fetchDepartment();
                    });
                };
            }
        ]);

})();
