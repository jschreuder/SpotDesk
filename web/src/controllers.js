(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("ticketsController", ["$tickets", function ($tickets) {
            var ctrl = this;
            ctrl.tickets = $tickets.fetchOpen();
        }]);
})();
