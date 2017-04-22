(function () {
    "use strict";

    angular.module("spotdesk").config(["$stateProvider", "$urlRouterProvider",
        function($stateProvider, $urlRouterProvider) {
            $urlRouterProvider.when("", "/");

            $stateProvider.state({
                name: "tickets",
                url: "/",
                templateUrl: "assets/templates/tickets.html",
                controller: "ticketsController",
                controllerAs: "ctrl",
                onEnter: ["$title", function($title) { $title.change("Open tickets") } ]
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
        }]);
})();
