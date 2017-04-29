(function () {
    "use strict";

    angular.module("spotdesk").config(["$stateProvider", "$urlRouterProvider",
        function($stateProvider, $urlRouterProvider) {
            $urlRouterProvider.when("", "/tickets");

            $stateProvider.state({
                name: "tickets",
                url: "/tickets",
                templateUrl: "assets/templates/tickets/list.html",
                controller: "ticketsController",
                controllerAs: "ctrl",
                onEnter: ["$sdTitle", function($title) { $title.change("Open tickets") } ]
            });

            $stateProvider.state({
                name: "tickets_status_type",
                url: "/tickets/type/{status_type}",
                templateUrl: "assets/templates/tickets/list.html",
                controller: "ticketsController",
                controllerAs: "ctrl",
                onEnter: ["$sdTitle", "$stateParams", function($title, $stateParams) {
                    $title.change($stateParams.status_type + " tickets")
                }]
            });

            $stateProvider.state({
                name: "tickets_view",
                url: "/tickets/view/{ticket_id}",
                templateUrl: "assets/templates/tickets/view.html",
                controller: "viewTicketController",
                controllerAs: "ctrl",
                onEnter: ["$sdTitle", "$stateParams", function($title) {
                    $title.change("View ticket")
                }]
            });

            $stateProvider.state({
                name: "change_password",
                url: "/change_password",
                templateUrl: "assets/templates/change_password.html",
                controller: "changePasswordController",
                controllerAs: "ctrl",
                onEnter: ["$sdTitle", function($title) { $title.change("Change password") } ]
            });

            $stateProvider.state({
                name: "users",
                url: "/users",
                templateUrl: "assets/templates/users/list.html",
                controller: "usersController",
                controllerAs: "ctrl",
                onEnter: ["$sdTitle", function($title) { $title.change("Manage users") } ]
            });

            $stateProvider.state({
                name: "users_view",
                url: "/users/view/{email}",
                templateUrl: "assets/templates/users/view.html",
                controller: "viewUserController",
                controllerAs: "ctrl",
                onEnter: ["$sdTitle", "$stateParams", function($title) {
                    $title.change("View user")
                }]
            });

            $stateProvider.state({
                name: "departments",
                url: "/departments",
                templateUrl: "assets/templates/departments/list.html",
                controller: "departmentsController",
                controllerAs: "ctrl",
                onEnter: ["$sdTitle", function($title) { $title.change("Manage departments") } ]
            });

            $stateProvider.state({
                name: "departments_view",
                url: "/departments/view/{department_id}",
                templateUrl: "assets/templates/departments/view.html",
                controller: "viewDepartmentController",
                controllerAs: "ctrl",
                onEnter: ["$sdTitle", "$stateParams", function($title) {
                    $title.change("View department")
                }]
            });

            $stateProvider.state({
                name: "mailboxes",
                url: "/mailboxes",
                templateUrl: "assets/templates/mailboxes/list.html",
                controller: "mailboxesController",
                controllerAs: "ctrl",
                onEnter: ["$sdTitle", function($title) { $title.change("Manage mailboxes") } ]
            });

            $stateProvider.state({
                name: "mailboxes_view",
                url: "/mailboxes/view/{mailbox_id}",
                templateUrl: "assets/templates/mailboxes/view.html",
                controller: "viewMailboxController",
                controllerAs: "ctrl",
                onEnter: ["$sdTitle", function($title) { $title.change("View mailbox") } ]
            });

            $stateProvider.state({
                name: "statuses",
                url: "/statuses",
                templateUrl: "assets/templates/statuses/list.html",
                controller: "statusesController",
                controllerAs: "ctrl",
                onEnter: ["$sdTitle", function($title) { $title.change("Manage statuses") } ]
            });

            $stateProvider.state({
                name: "statuses_view",
                url: "/statuses/view/{status}",
                templateUrl: "assets/templates/statuses/view.html",
                controller: "viewStatusController",
                controllerAs: "ctrl",
                onEnter: ["$sdTitle", function($title) { $title.change("View status") } ]
            });
        }]);
})();
