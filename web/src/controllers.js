(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("ticketsController", ["$tickets", "$stateParams", function ($tickets, $stateParams) {
            var ctrl = this;
            ctrl.selected = [];
            ctrl.tickets = $tickets.fetch($stateParams.status_type || "open");
        }])

        .controller("viewTicketController", ["$tickets", "$stateParams", function ($tickets, $stateParams) {
            var ctrl = this;
            ctrl.ticket = $tickets.fetchOne($stateParams.ticket_id);
        }])

        .controller("usersController", ["$users", function ($users) {
            var ctrl = this;
            ctrl.selected = [];
            ctrl.users = $users.fetch();
        }])

        .controller("departmentsController", ["$departments", function ($departments) {
            var ctrl = this;
            ctrl.selected = [];
            ctrl.departments = $departments.fetch();
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

        .controller("mailboxesController", ["$mailboxes", function ($mailboxes) {
            var ctrl = this;
            ctrl.selected = [];
            ctrl.mailboxes = $mailboxes.fetch();
        }]);
})();
