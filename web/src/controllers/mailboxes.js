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

        .controller("viewMailboxController", ["$sdMailboxes", "$stateParams", "$sdDepartments", "$mdDialog", "$sdAlert",
            function ($sdMailboxes, $stateParams, $sdDepartments, $mdDialog, $sdAlert) {
                var ctrl = this;
                ctrl.mailbox = null;
                ctrl.departments = $sdDepartments.all();

                ctrl.fetchMailbox = function () {
                    $sdMailboxes.fetchOne($stateParams.mailbox_id).then(function (response) {
                        ctrl.mailbox = response.data.mailbox;
                    }, function () {
                        $sdAlert.error("mailbox_load_failed");
                    });
                };
                ctrl.fetchMailbox();

                ctrl.editMailbox = function(ev) {
                    $mdDialog.show({
                        contentElement: "#editMailbox",
                        parent: angular.element(document.body),
                        targetEvent: ev
                    });
                };
                ctrl.cancelEditMailbox = function () {
                    $mdDialog.cancel();
                };
                ctrl.submitEditMailbox = function () {
                    $sdMailboxes.update(
                        ctrl.mailbox.mailbox_id,
                        ctrl.mailbox.name,
                        ctrl.mailbox.department_id,
                        ctrl.mailbox.imap_server,
                        ctrl.mailbox.imap_port,
                        ctrl.mailbox.imap_security,
                        ctrl.mailbox.imap_user,
                        ctrl.mailbox.imap_pass
                    ).then(function () {
                        $mdDialog.hide();
                        ctrl.fetchMailbox();
                    }, function () {
                        // @todo handle validation errors differently
                        $sdAlert.error("mailbox_edit_failed");
                        ctrl.fetchMailbox();
                    });
                };
            }
        ]);

})();
