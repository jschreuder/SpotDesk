(function () {
    "use strict";

    angular.module("spotdesk")

        .controller("mailboxesController", ["$sdMailboxes", "$sdDepartments", "$sdAlert", "$mdDialog",
            function ($sdMailboxes, $sdDepartments, $sdAlert, $mdDialog) {
                var ctrl = this;
                ctrl.order = "department_name";
                ctrl.mailboxes = [];
                ctrl.departments = $sdDepartments.all();

                ctrl.fetchMailboxes = function () {
                    $sdMailboxes.fetch().then(function (response) {
                        ctrl.mailboxes = [];
                        angular.forEach(response.data.mailboxes, function (mailbox) {
                            ctrl.mailboxes.push(mailbox);
                        });
                    }, function () {
                        $sdAlert.error("mailboxes_load_failed");
                    });
                };
                ctrl.fetchMailboxes();

                ctrl.createMailbox = function(ev) {
                    ctrl.mailbox = {
                        name: null,
                        department_id: null,
                        imap_server: null,
                        imap_port: null,
                        imap_security: null,
                        imap_user: null,
                        imap_pass: null
                    };
                    $mdDialog.show({
                        contentElement: "#createMailbox",
                        parent: angular.element(document.body),
                        targetEvent: ev
                    });
                };
                ctrl.cancelCreateMailbox = function () {
                    $mdDialog.cancel();
                };
                ctrl.submitCreateMailbox = function () {
                    $sdMailboxes.create(
                        ctrl.mailbox.name,
                        ctrl.mailbox.department_id,
                        ctrl.mailbox.imap_server,
                        ctrl.mailbox.imap_port,
                        ctrl.mailbox.imap_security,
                        ctrl.mailbox.imap_user,
                        ctrl.mailbox.imap_pass
                    ).then(function () {
                        $mdDialog.hide();
                        ctrl.fetchMailboxes();
                    }, function () {
                        // @todo handle validation errors differently
                        $sdAlert.error("mailbox_create_failed");
                    });
                };
            }
        ])

        .controller("viewMailboxController",
            ["$sdMailboxes", "$state", "$stateParams", "$sdDepartments", "$mdDialog", "$sdAlert",
            function ($sdMailboxes, $state, $stateParams, $sdDepartments, $mdDialog, $sdAlert) {
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

                ctrl.deleteMailbox = function () {
                    $mdDialog.show(
                        $mdDialog.confirm()
                            .title("Delete mailbox")
                            .textContent("Would you like to delete mailbox '" + ctrl.mailbox.name + "'?")
                            .ariaLabel("Delete mailbox")
                            .ok('Yes')
                            .cancel('No')
                    ).then(function () {
                        $sdMailboxes.delete(ctrl.mailbox.mailbox_id).then(function () {
                            $state.go("mailboxes");
                        }, function () {
                            $sdAlert.error("mailbox_delete_failed");
                        });
                    });
                };
            }
        ]);

})();
