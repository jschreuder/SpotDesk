(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("departmentsController", ["$sdDepartments", "$sdAlert", "$mdDialog",
            function ($sdDepartments, $sdAlert, $mdDialog) {
                var ctrl = this;
                ctrl.order = "full_name";
                ctrl.selected = [];
                ctrl.departments = $sdDepartments.all();

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
                        ctrl.departments = $sdDepartments.all(true); // refresh departments
                    }, function () {
                        // @todo handle validation errors differently
                        $sdAlert.error("department_create_failed");
                    });
                };
            }
        ])

        .controller("viewDepartmentController", ["$sdDepartments", "$stateParams", "$sdAlert", "$mdDialog", "$state",
            function ($sdDepartments, $stateParams, $sdAlert, $mdDialog, $state) {
                var ctrl = this;
                ctrl.department = null;
                ctrl.all_departments = $sdDepartments.all();
                ctrl.department_users = [];
                ctrl.department_mailboxes = [];

                ctrl.fetchDepartment = function () {
                    $sdDepartments.fetchOne($stateParams.department_id).then(function (response) {
                        ctrl.department = response.data.department;
                        ctrl.all_departments.updateOne(ctrl.department); // updates departments cache

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

                ctrl.deleteDepartment = function(ev) {
                    $mdDialog.show({
                        contentElement: "#deleteDepartment",
                        parent: angular.element(document.body),
                        targetEvent: ev
                    });
                };
                ctrl.cancelDeleteDepartment = function () {
                    $mdDialog.cancel();
                };
                ctrl.submitDeleteDepartment = function () {
                    $sdDepartments.delete(
                        ctrl.department.department_id, ctrl.delete_new_ticket_department
                    ).then(function () {
                        $mdDialog.hide();
                        $sdDepartments.all(true); // refresh departments cache
                        $state.go("departments");
                    }, function () {
                        // @todo handle validation errors differently
                        $sdAlert.error("department_delete_failed");
                        ctrl.fetchDepartment();
                    });
                };
            }
        ]);

})();
