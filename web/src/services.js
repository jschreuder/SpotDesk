(function () {
    "use strict";

    angular.module("spotdesk")

        .factory("$title", function () {
            return {
                _current: null,

                get: function () {
                    return this._current;
                },

                change: function (newTitle) {
                    this._current = newTitle;
                }
            };
        })

        .factory("$tickets", ["$http", function ($http) {
            return {
                fetchOpen: function () {
                    var result = [];
                    $http.get("/tickets").then(function (response) {
                        angular.forEach(response.data.tickets, function (ticket) {
                            result.push(ticket);
                        });
                    }, function () {
                        alert("tickets_load_failed")
                    });
                    return result;
                }
            };
        }])

        .factory("$users", ["$http", function ($http) {
            return {
                fetch: function () {
                    var result = [];
                    $http.get("/users").then(function (response) {
                        angular.forEach(response.data.users, function (user) {
                            result.push(user);
                        });
                    }, function () {
                        alert("users_load_failed")
                    });
                    return result;
                }
            };
        }])

        .factory("$departments", ["$http", function ($http) {
            return {
                fetch: function () {
                    var result = [];
                    $http.get("/departments").then(function (response) {
                        angular.forEach(response.data.departments, function (department) {
                            result.push(department);
                        });
                    }, function () {
                        alert("departments_load_failed")
                    });
                    return result;
                }
            };
        }])

        .factory("$mailboxes", ["$http", function ($http) {
            return {
                fetch: function () {
                    var result = [];
                    $http.get("/mailboxes").then(function (response) {
                        angular.forEach(response.data.mailboxes, function (mailbox) {
                            result.push(mailbox);
                        });
                    }, function () {
                        alert("mailboxes_load_failed")
                    });
                    return result;
                }
            };
        }]);
})();
