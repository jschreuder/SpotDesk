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
                        ctrl.department.name, ctrl.department.email, ctrl.department.parent_id
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
                ctrl.department_children = [];
                ctrl.department_users = [];
                ctrl.department_mailboxes = [];
                ctrl.delete_new_ticket_department = null;

                ctrl.fetchDepartment = function () {
                    $sdDepartments.fetchOne($stateParams.department_id).then(function (response) {
                        ctrl.department = response.data.department;
                        ctrl.all_departments.updateOne(ctrl.department); // updates departments cache

                        ctrl.department_children = [];
                        angular.forEach(response.data.children, function (child) {
                            ctrl.department_children.push(child);
                        });

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

                ctrl.possibleParents = function (department) {
                    if (!department || !ctrl.department) {
                        // for null or when department was not yet loaded
                        return true;
                    } else if (department.department_id === ctrl.department.department_id) {
                        // Filter department itself as a possible parent
                        return false;
                    } else if (department.path.indexOf(ctrl.department.full_name + " /") === 0) {
                        // Filter department's children as possible parents
                        return false;
                    }

                    return true;
                };

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
                        ctrl.department.department_id,
                        ctrl.department.name,
                        ctrl.department.email,
                        ctrl.department.parent_id
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
                    var promise;
                    if (ctrl.delete_new_ticket_department === "DELETE") {
                        promise = $sdDepartments.delete(ctrl.department.department_id, "delete", null);
                    } else {
                        promise = $sdDepartments.delete(
                            ctrl.department.department_id, "move", ctrl.delete_new_ticket_department
                        );
                    }

                    promise.then(function () {
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
