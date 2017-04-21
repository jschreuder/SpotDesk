(function () {
    "use strict";

    angular.module("spotdesk")

        .factory("$tickets", ["$http", function ($http) {
            return {
                fetchOpen: function () {
                    var result = [];
                    $http.get("/tickets").then(function (response) {
                        console.log(response);
                        angular.forEach(response.data.tickets, function (ticket) {
                            result.push(ticket);
                        });
                    }, function () {
                        alert("tickets_load_failed")
                    });
                    return result;
                }
            };
        }]);
})();
