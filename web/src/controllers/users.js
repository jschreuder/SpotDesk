(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("usersController", ["$sdUsers", "$sdAlert", "$mdDialog",
            function ($sdUsers, $sdAlert, $mdDialog) {
                var ctrl = this;
                ctrl.order = "email";
                ctrl.users = [];

                ctrl.fetchUsers = function () {
                    $sdUsers.fetch().then(function (response) {
                        ctrl.users = [];
                        angular.forEach(response.data.users, function (user) {
                            ctrl.users.push(user);
                        });
                    }, function () {
                        $sdAlert.error("users_load_failed");
                    });
                };
                ctrl.fetchUsers();

                ctrl.createUser = function(ev) {
                    ctrl.user = {
                        email: null,
                        display_name: null,
                        password: null,
                        role: "admin"
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
                    $sdUsers.create(
                        ctrl.user.email, ctrl.user.display_name, ctrl.user.password, ctrl.user.role
                    ).then(function () {
                        $mdDialog.hide();
                        ctrl.fetchUsers();
                    }, function () {
                        // @todo handle validation errors differently
                        $sdAlert.error("user_create_failed");
                    });
                };
            }
        ])

        .controller("viewUserController",
            ["$sdUsers", "$sdDepartments", "$state", "$stateParams", "$sdAlert", "$mdDialog",
            function ($sdUsers, $sdDepartments, $state, $stateParams, $sdAlert, $mdDialog) {
                var ctrl = this;
                ctrl.user = null;
                ctrl.user_departments = [];
                ctrl.departments = [];

                ctrl.fetchUser = function () {
                    $sdUsers.fetchOne($stateParams.email).then(function (response) {
                        ctrl.user = response.data.user;
                        ctrl.user_departments = [];
                        angular.forEach(response.data.departments, function (department) {
                            ctrl.user_departments.push(department.department_id);
                        });
                    }, function () {
                        $sdAlert.error("user_load_failed");
                    });
                };
                ctrl.fetchUser();

                ctrl.departments = $sdDepartments.all();

                ctrl.editUser = function(ev) {
                    $mdDialog.show({
                        contentElement: "#editUser",
                        parent: angular.element(document.body),
                        targetEvent: ev
                    });
                };
                ctrl.cancelEditUser = function () {
                    $mdDialog.cancel();
                };
                ctrl.submitEditUser = function () {
                    $sdUsers.update(
                        ctrl.user.email, ctrl.user.display_name, ctrl.user.role, ctrl.user.active
                    ).then(function () {
                        $mdDialog.hide();
                        ctrl.fetchUser();
                    }, function () {
                        // @todo handle validation errors differently
                        $sdAlert.error("user_edit_failed");
                        ctrl.fetchUser();
                    });
                };

                ctrl.deleteUser = function () {
                    $mdDialog.show(
                        $mdDialog.confirm()
                            .title("Delete user")
                            .textContent("Would you like to delete user '" + ctrl.user.display_name
                                + "' with e-mailaddress " + ctrl.user.email + "?")
                            .ariaLabel("Delete user")
                            .ok('Yes')
                            .cancel('No')
                    ).then(function () {
                        $sdUsers.delete(ctrl.user.email).then(function () {
                            $state.go("users");
                        }, function () {
                            $sdAlert.error("user_delete_failed");
                        });
                    });
                };

                ctrl.saveDepartments = function () {
                    $sdUsers.saveDepartments(ctrl.user.email, ctrl.user_departments).then(function (response) {
                        ctrl.user_departments = [];
                        angular.forEach(response.data.user_departments, function (department) {
                            ctrl.user_departments.push(department.department_id);
                        });
                        $sdAlert.success('Department assignments saved');
                    }, function () {
                        $sdAlert.error("user_departments_saving_failed");
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
        ])

        .controller("changePasswordController", ["$sdAuth", "$sdAlert",
            function ($sdAuth, $sdAlert) {
                var ctrl = this;

                ctrl.changePassword = function () {
                    if (ctrl.new_password !== ctrl.confirm_new_password) {
                        $sdAlert.error("Password fields do not match.");
                        return;
                    }
                    if (ctrl.new_password.length < 12) {
                        $sdAlert.error("Password must be at least 12 characters.");
                        return;
                    }

                    $sdAuth.changePassword(ctrl.old_password, ctrl.new_password).then(function () {
                        ctrl.old_password = null;
                        ctrl.new_password = null;
                        $sdAlert.success("Password changed");
                    }, function () {
                        $sdAlert.error("Password change failed");
                    });
                };
            }
        ]);

})();
