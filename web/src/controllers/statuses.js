(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("statusesController", ["$statuses", "$adminMessage", function ($statuses, $adminMessage) {
            var ctrl = this;
            ctrl.order = "name";
            ctrl.selected = [];
            ctrl.statuses = [];

            $statuses.fetch().then(function (response) {
                ctrl.statuses = [];
                angular.forEach(response.data.statuses, function (status) {
                    ctrl.statuses.push(status);
                }, function () {
                    $adminMessage.error("statuses_load_failed");
                });
            });
        }]);

})();
