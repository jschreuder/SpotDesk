(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("mailboxesController", ["$sdMailboxes", "$sdAlert", function ($sdMailboxes, $sdAlert) {
            var ctrl = this;
            ctrl.order = "department_name";
            ctrl.mailboxes = [];

            $sdMailboxes.fetch().then(function (response) {
                ctrl.mailboxes = [];
                angular.forEach(response.data.mailboxes, function (mailbox) {
                    ctrl.mailboxes.push(mailbox);
                });
            }, function () {
                $sdAlert.error("mailboxes_load_failed");
            });
        }])

        .controller("viewMailboxController", ["$sdMailboxes", "$stateParams", function ($sdMailboxes, $stateParams) {
            var ctrl = this;
            ctrl.mailbox = null;

            ctrl.fetchMailbox = function () {
                $sdMailboxes.fetchOne($stateParams.mailbox_id).then(function (response) {
                    ctrl.mailbox = response.data.mailbox;
                }, function () {
                    $sdAlert.error("mailbox_load_failed");
                });
            };
            ctrl.fetchMailbox();
        }]);

})();
