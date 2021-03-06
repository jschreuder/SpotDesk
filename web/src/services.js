(function () {
    "use strict";

    angular.module("spotdesk")

        .factory("$sdMailboxes", ["$http", function ($http) {
            return {
                fetch: function () {
                    return $http.get("/mailboxes");
                },

                fetchOne: function (mailbox_id) {
                    return $http.get("/mailboxes/" + mailbox_id);
                },

                create: function (name, department_id, imap_server, imap_port, imap_security, imap_user, imap_pass) {
                    return $http.post("/mailboxes", {
                        name: name,
                        department_id: department_id || null,
                        imap_server: imap_server,
                        imap_port: imap_port,
                        imap_security: imap_security,
                        imap_user: imap_user,
                        imap_pass: imap_pass
                    });
                },

                update: function (
                    mailbox_id, name, department_id, imap_server, imap_port, imap_security, imap_user, imap_pass
                ) {
                    return $http.put("/mailboxes/" + mailbox_id, {
                        name: name,
                        department_id: department_id || null,
                        imap_server: imap_server,
                        imap_port: imap_port,
                        imap_security: imap_security,
                        imap_user: imap_user,
                        imap_pass: imap_pass || null
                    });
                },

                delete: function (mailbox_id) {
                    return $http.delete("/mailboxes/" + mailbox_id);
                }
            };
        }])

        .factory("$sdStatuses", ["$http", function ($http) {
            return {
                fetch: function () {
                    return $http.get("/statuses");
                }
            };
        }]);
})();
