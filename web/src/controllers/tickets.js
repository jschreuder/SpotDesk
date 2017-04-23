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
        ]);

})();
