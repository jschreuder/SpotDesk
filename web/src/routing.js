(function () {
    "use strict";

    angular.module("spotdesk").config(["$stateProvider", "$urlRouterProvider",
        function($stateProvider, $urlRouterProvider) {
            $urlRouterProvider.when("", "/tickets");

            $stateProvider.state({
                name: "tickets",
                url: "/tickets",
                templateUrl: "assets/templates/tickets.html",
                controller: "ticketsController",
                controllerAs: "ctrl",
                onEnter: ["$title", function($title) { $title.change("Open tickets") } ]
            });

            $stateProvider.state({
                name: "tickets_status_type",
                url: "/tickets/type/{status_type}",
                templateUrl: "assets/templates/tickets.html",
                controller: "ticketsController",
                controllerAs: "ctrl",
                onEnter: ["$title", "$stateParams", function($title, $stateParams) {
                    $title.change($stateParams.status_type + " tickets")
                }]
            });

            $stateProvider.state({
                name: "tickets_view",
                url: "/tickets/view/{ticket_id}",
                templateUrl: "assets/templates/tickets/view.html",
                controller: "viewTicketController",
                controllerAs: "ctrl",
                onEnter: ["$title", "$stateParams", function($title) {
                    $title.change("View tickets")
                }]
            });

            $stateProvider.state({
                name: "users",
                url: "/users",
                templateUrl: "assets/templates/users.html",
                controller: "usersController",
                controllerAs: "ctrl",
                onEnter: ["$title", function($title) { $title.change("Manage users") } ]
            });

            $stateProvider.state({
                name: "departments",
                url: "/departments",
                templateUrl: "assets/templates/departments.html",
                controller: "departmentsController",
                controllerAs: "ctrl",
                onEnter: ["$title", function($title) { $title.change("Manage departments") } ]
            });

            $stateProvider.state({
                name: "mailboxes",
                url: "/mailboxes",
                templateUrl: "assets/templates/mailboxes.html",
                controller: "mailboxesController",
                controllerAs: "ctrl",
                onEnter: ["$title", function($title) { $title.change("Manage mailboxes") } ]
            });
        }]);
})();
