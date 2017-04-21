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
                controllerAs: "ctrl"
            });

            $stateProvider.state({
                name: "config",
                url: "/config",
                templateUrl: "assets/templates/config.html"
            });
        }]);
})();
