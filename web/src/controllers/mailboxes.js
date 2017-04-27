(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("mailboxesController", ["$sdMailboxes", "$sdAlert", function ($sdMailboxes, $sdAlert) {
            var ctrl = this;
            ctrl.order = "department_name";
            ctrl.selected = [];
            ctrl.mailboxes = [];

            $sdMailboxes.fetch().then(function (response) {
                ctrl.mailboxes = [];
                angular.forEach(response.data.mailboxes, function (mailbox) {
                    ctrl.mailboxes.push(mailbox);
                });
            }, function () {
                $sdAlert.error("mailboxes_load_failed");
            });
        }]);

})();
