(function () {
    "use strict";

    angular.module("spotdesk")
        .controller("mainController", ["$mdSidenav", "$mdDialog", "$sdAuth", "$sdTitle", "$sdAlert",
            function ($mdSidenav, $mdDialog, $sdAuth, $sdTitle, $sdAlert) {
                var ctrl = this;

                ctrl.loggedIn = $sdAuth.loggedIn;
                ctrl.user = {
                    name: null,
                    pass: null,
                    persist: false
                };

                ctrl.login = function () {
                    if (!ctrl.user.name || !ctrl.user.pass) {
                        $sdAlert.error("auth_missing_field");
                        return;
                    }

                    $sdAuth.login(ctrl.user.name, ctrl.user.pass, ctrl.user.persist, function () {
                        ctrl.user.name = null;
                        ctrl.user.pass = null;
                        ctrl.user.persist = false;
                    });
                };

                ctrl.logout = function () {
                    $mdDialog.show(
                        $mdDialog.confirm()
                            .title("Log out")
                            .textContent("Are you sure you want to log out?")
                            .ariaLabel("Log out")
                            .ok('Yes')
                            .cancel('No')
                    ).then(function () {
                        $sdAuth.logout();
                    });
                };

                ctrl.showTitle = function () {
                    if ($sdAuth.loggedIn()) {
                        return $sdTitle.get();
                    }
                    return "Login";
                };

                ctrl.toggleSideNav = function() {
                    $mdSidenav("left").toggle();
                };
            }
        ]);
})();
