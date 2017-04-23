(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("mailboxesController", ["$mailboxes", "$adminMessage", function ($mailboxes, $adminMessage) {
            var ctrl = this;
            ctrl.order = "department_name";
            ctrl.selected = [];
            ctrl.mailboxes = [];

            $mailboxes.fetch().then(function (response) {
                ctrl.mailboxes = [];
                angular.forEach(response.data.mailboxes, function (mailbox) {
                    ctrl.mailboxes.push(mailbox);
                });
            }, function () {
                $adminMessage.error("mailboxes_load_failed");
            });
        }]);

})();
