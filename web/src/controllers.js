(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("ticketsController", ["$tickets", "$stateParams", function ($tickets, $stateParams) {
            var ctrl = this;
            ctrl.order = "-last_update";
            ctrl.promise = null;
            ctrl.query = {
                status_type: $stateParams.status_type || "open",
                limit: 15,
                page: 1,
                sort: "-last_update"
            };
            ctrl.tickets = [];
            ctrl.selected = [];

            ctrl.getTickets = function () {
                var sort_by = ctrl.query.sort,
                    sort_direction = "asc";
                if (sort_by[0] === "-") {
                    sort_by = sort_by.substring(1);
                    sort_direction = "desc";
                }

                ctrl.promise = $tickets.fetch(
                    ctrl.query.status_type, ctrl.query.limit, ctrl.query.page, sort_by, sort_direction
                ).then(function (response) {
                    ctrl.tickets = [];
                    angular.forEach(response.data.tickets, function (ticket) {
                        ctrl.tickets.push(ticket);
                    });
                    ctrl.tickets.total_count = response.data.total_count;
                });
            };
            ctrl.getTickets();
        }])

        .controller("viewTicketController", ["$tickets", "$statuses", "$stateParams", "$mdDialog", "$adminMessage",
            function ($tickets, $statuses, $stateParams, $mdDialog, $adminMessage) {
                var ctrl = this;
                ctrl.ticket = null;
                ctrl.updates = [];
                ctrl.statuses = [{
                    name: "",
                    type: null
                }];

                ctrl.fetchTicket = function () {
                    $tickets.fetchOne($stateParams.ticket_id).then(function (response) {
                        ctrl.ticket = response.data.ticket;
                        ctrl.updates = response.data.ticket_updates;
                    }, function () {
                        $adminMessage.error("ticket_load_failed");
                    });
                };
                ctrl.fetchTicket();

                $statuses.fetch().then(function (response) {
                    angular.forEach(response.data.statuses, function (status) {
                        ctrl.statuses.push(status);
                    }, function () {
                        $adminMessage.error("statuses_load_failed");
                    });
                });
                ctrl.getStatus = function (statusName) {
                    var found = null;
                    ctrl.statuses.forEach(function (status) {
                        if (status.name === statusName) {
                            found = status;
                        }
                    });
                    return found;
                };

                ctrl.addReply = function(ev) {
                    ctrl.reply = {
                        message: "",
                        internal: false,
                        status_update: null
                    };
                    $mdDialog.show({
                        contentElement: "#addTicketReply",
                        parent: angular.element(document.body),
                        targetEvent: ev
                    });
                };
                ctrl.cancelReply = function () {
                    $mdDialog.cancel();
                };
                ctrl.submitReply = function () {
                    $tickets.addReply(
                        ctrl.ticket.ticket_id, ctrl.reply.message, ctrl.reply.internal, ctrl.reply.status_update
                    ).then(function () {
                        $mdDialog.hide();
                        ctrl.fetchTicket();
                    }, function () {
                        // @todo handle validation errors differently
                        $adminMessage.error("ticket_update_failed");
                    });
                };
            }
        ])

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
        }])

        .controller("mailboxesController", ["$mailboxes", "$adminMessage", function ($mailboxes, $adminMessage) {
            var ctrl = this;
            ctrl.order = "department_name";
            ctrl.selected = [];
            ctrl.mailboxes = [];

            $mailboxes.fetch().then(function (response) {
                ctrl.mailboxes = [];
                angular.forEach(response.data.mailboxes, function (mailbox) {
                    ctrl.mailboxes.push(mailbox);
                });
            }, function () {
                $adminMessage.error("mailboxes_load_failed");
            });
        }])

        .controller("statusesController", ["$statuses", "$adminMessage", function ($statuses, $adminMessage) {
            var ctrl = this;
            ctrl.order = "name";
            ctrl.selected = [];
            ctrl.statuses = [];

            $statuses.fetch().then(function (response) {
                ctrl.statuses = [];
                angular.forEach(response.data.statuses, function (status) {
                    ctrl.statuses.push(status);
                }, function () {
                    $adminMessage.error("statuses_load_failed");
                });
            });
        }]);
})();
