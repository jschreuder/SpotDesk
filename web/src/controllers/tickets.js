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

        .controller("viewTicketController",
            ["$tickets", "$statuses", "$departments", "$state", "$stateParams", "$mdDialog", "$adminMessage",
            function ($tickets, $statuses, $departments, $state, $stateParams, $mdDialog, $adminMessage) {
                var ctrl = this;
                ctrl.ticket = null;
                ctrl.updates = [];
                ctrl.statuses = [{
                    name: "",
                    type: null
                }];
                ctrl.departments = [{
                    department_id: "",
                    name: "no_department"
                }];
                ctrl.change_status = null;
                ctrl.change_department_id = null;

                ctrl.fetchTicket = function () {
                    $tickets.fetchOne($stateParams.ticket_id).then(function (response) {
                        ctrl.ticket = response.data.ticket;
                        ctrl.updates = response.data.ticket_updates;
                    }, function () {
                        $adminMessage.error("ticket_load_failed");
                    });
                };
                ctrl.fetchTicket();

                $departments.fetch().then(function (response) {
                    angular.forEach(response.data.departments, function (department) {
                        ctrl.departments.push(department);
                    }, function () {
                        $adminMessage.error("departments_load_failed");
                    });
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

                ctrl.changeDepartment = function(ev) {
                    ctrl.change_department_id = ctrl.ticket.department_id;
                    $mdDialog.show({
                        contentElement: "#changeDepartment",
                        parent: angular.element(document.body),
                        targetEvent: ev
                    });
                };
                ctrl.cancelDepartmentChange = function () {
                    $mdDialog.cancel();
                };
                ctrl.submitDepartmentChange = function () {
                    $tickets.changeDepartment(
                        ctrl.ticket.ticket_id, ctrl.change_department_id
                    ).then(function () {
                        $mdDialog.hide();
                        ctrl.fetchTicket();
                    }, function () {
                        // @todo handle validation errors differently
                        $adminMessage.error("ticket_change_department_failed");
                    });
                };

                ctrl.changeStatus = function(ev) {
                    ctrl.change_status = ctrl.ticket.status;
                    $mdDialog.show({
                        contentElement: "#changeStatus",
                        parent: angular.element(document.body),
                        targetEvent: ev
                    });
                };
                ctrl.cancelStatusChange = function () {
                    $mdDialog.cancel();
                };
                ctrl.submitStatusChange = function () {
                    $tickets.changeStatus(
                        ctrl.ticket.ticket_id, ctrl.change_status
                    ).then(function () {
                        $mdDialog.hide();
                        ctrl.fetchTicket();
                    }, function () {
                        // @todo handle validation errors differently
                        $adminMessage.error("ticket_change_status_failed");
                    });
                };

                ctrl.deleteTicket = function () {
                    $mdDialog.show(
                        $mdDialog.confirm()
                            .title("Delete ticket")
                            .textContent("Would you like to delete ticket with subject '" + ctrl.ticket.subject+ "'?")
                            .ariaLabel("Delete ticket")
                            .ok('Yes')
                            .cancel('No')
                    ).then(function () {
                        $tickets.delete(ctrl.ticket.ticket_id).then(function () {
                            $state.go("tickets");
                        }, function () {
                            $adminMessage.error("ticket_delete_failed");
                        });
                    });
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
        ]);

})();
