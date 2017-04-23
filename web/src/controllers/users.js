(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("usersController", ["$users", "$adminMessage", "$mdDialog",
            function ($users, $adminMessage, $mdDialog) {
                var ctrl = this;
                ctrl.order = "email";
                ctrl.selected = [];
                ctrl.users = [];

                ctrl.fetchUsers = function () {
                    $users.fetch().then(function (response) {
                        ctrl.users = [];
                        angular.forEach(response.data.users, function (user) {
                            ctrl.users.push(user);
                        });
                    }, function () {
                        $adminMessage.error("users_load_failed");
                    });
                };
                ctrl.fetchUsers();

                ctrl.createUser = function(ev) {
                    ctrl.user = {
                        email: null,
                        display_name: null,
                        password: null
                    };
                    $mdDialog.show({
                        contentElement: "#createUser",
                        parent: angular.element(document.body),
                        targetEvent: ev
                    });
                };
                ctrl.cancelUser = function () {
                    $mdDialog.cancel();
                };
                ctrl.submitUser = function () {
                    $users.create(
                        ctrl.user.email, ctrl.user.display_name, ctrl.user.password
                    ).then(function () {
                        $mdDialog.hide();
                        ctrl.fetchUsers();
                    }, function () {
                        // @todo handle validation errors differently
                        $adminMessage.error("user_create_failed");
                    });
                };
            }
        ])

        .controller("viewUserController", ["$users", "$departments", "$stateParams", "$adminMessage",
            function ($users, $departments, $stateParams, $adminMessage) {
                var ctrl = this;
                ctrl.user = null;
                ctrl.user_departments = [];
                ctrl.departments = [];

                ctrl.fetchUser = function () {
                    $users.fetchOne($stateParams.email).then(function (response) {
                        ctrl.user = response.data.user;
                        ctrl.user_departments = [];
                        angular.forEach(response.data.departments, function (department) {
                            ctrl.user_departments.push(department.department_id);
                        });
                    }, function () {
                        $adminMessage.error("user_load_failed");
                    });
                };
                ctrl.fetchUser();

                $departments.fetch().then(function (response) {
                    ctrl.departments = [];
                    angular.forEach(response.data.departments, function (department) {
                        ctrl.departments.push(department);
                    });
                }, function () {
                    $adminMessage.error("departments_load_failed");
                });

                ctrl.saveDepartments = function () {
                    $users.saveDepartments(ctrl.user.email, ctrl.user_departments).then(function (response) {
                        ctrl.user_departments = [];
                        angular.forEach(response.data.user_departments, function (department) {
                            ctrl.user_departments.push(department.department_id);
                        });
                    }, function () {
                        $adminMessage.error("user_departments_saving_failed");
                    });
                };

                ctrl.departmentIndexOf = function (department) {
                    var found = -1;
                    ctrl.user_departments.forEach(function (departmentId, idx) {
                        if (departmentId === department.department_id) {
                            found = idx;
                        }
                    });
                    return found;
                };
                ctrl.hasDepartment = function (department) {
                    return ctrl.departmentIndexOf(department) > -1;
                };
                ctrl.toggleDepartment = function (department) {
                    var idx = ctrl.departmentIndexOf(department);
                    if (idx > -1) {
                        ctrl.user_departments.splice(idx, 1);
                    } else {
                        ctrl.user_departments.push(department.department_id);
                    }
                };

                ctrl.departmentsAreIndeterminate = function() {
                    return (ctrl.user_departments.length !== 0
                    && ctrl.user_departments.length !== ctrl.departments.length);
                };
                ctrl.allDepartmentsChecked = function() {
                    return ctrl.user_departments.length === ctrl.departments.length;
                };
                ctrl.toggleAllDepartments = function() {
                    var oldLength = ctrl.user_departments.length;
                    ctrl.user_departments = [];
                    if (oldLength === 0 || oldLength < ctrl.departments.length) {
                        ctrl.departments.forEach(function (department) {
                            ctrl.user_departments.push(department.department_id);
                        });
                    }
                };
            }
        ]);

})();
