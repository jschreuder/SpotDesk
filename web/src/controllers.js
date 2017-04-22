(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("ticketsController", ["$tickets", "$stateParams", function ($tickets, $stateParams) {
            var ctrl = this;
            ctrl.tickets = $tickets.fetch($stateParams.status_type || "open");
        }])

        .controller("usersController", ["$users", function ($users) {
            var ctrl = this;
            ctrl.users = $users.fetch();
        }])

        .controller("departmentsController", ["$departments", function ($departments) {
            var ctrl = this;
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
            ctrl.mailboxes = $mailboxes.fetch();
        }]);
})();
