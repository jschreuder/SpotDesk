(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("statusesController", ["$sdStatuses", "$sdAlert", function ($sdStatuses, $sdAlert) {
            var ctrl = this;
            ctrl.order = "name";
            ctrl.statuses = [];

            $sdStatuses.fetch().then(function (response) {
                ctrl.statuses = [];
                angular.forEach(response.data.statuses, function (status) {
                    ctrl.statuses.push(status);
                }, function () {
                    $sdAlert.error("statuses_load_failed");
                });
            });
        }]);

})();
