(function () {
    "use strict";

    angular.module("spotdesk", ["ngMaterial", "ngCookies", "ui.router", "md.data.table"])

        // Main configuration
        .config(["$httpProvider", "$mdThemingProvider", "$mdToastProvider",
            function($httpProvider , $mdThemingProvider, $mdToastProvider) {
                $httpProvider.interceptors.push("authInterceptor");

                $mdThemingProvider.theme("default")
                    .primaryPalette("green")
                    .accentPalette("pink");
            }
        ])

        .factory("$sdAlert", ["$mdDialog", "$mdToast", function ($mdDialog, $mdToast) {
            var srvc = this;

            srvc.success = function (message) {
                $mdToast.show(
                    $mdToast.simple()
                        .parent(angular.element(document.getElementById("content")))
                        .textContent(message)
                        .position('top right')
                );
            };

            srvc.error = function (message, title) {
                if (title === undefined) {
                    title = "Error";
                }
                $mdDialog.show(
                    $mdDialog.alert()
                        .parent(angular.element(document.getElementById("content")))
                        .clickOutsideToClose(true)
                        .title(title)
                        .textContent(message)
                        .ariaLabel(title)
                        .ok('OK')
                );
            };

            return srvc;
        }])

        .factory("$sdHash", function () {
            return function (password) {
                var sha = new jsSHA("SHA-512", "TEXT");
                sha.update(password);
                return sha.getHash("HEX");
            };
        })

        // Authentication service
        .factory("$sdAuth", ["$http", "$cookies", "$state", "$sdAlert", "$sdHash", "authInterceptor",
            function ($http, $cookies, $state, $sdAlert, $sdHash, authInterceptor) {
                var srvc = this;

                srvc.persistToken = false;
                srvc.token = null;

                srvc.loggedIn = function() {
                    // Check login status
                    var status = srvc.token !== null;
                    if (status) {
                        return true;
                    }

                    // Check cookie for persisted login status
                    var cookieToken = $cookies.get("spotdesk-authorization");
                    if (cookieToken) {
                        srvc.token = cookieToken;
                        return true;
                    }

                    // No current login
                    return false;
                };

                srvc.updateToken = function (token) {
                    srvc.token = token;
                    if (srvc.persistToken) {
                        $cookies.put("spotdesk-authorization", token);
                    } else {
                        $cookies.remove("spotdesk-authorization");
                    }
                };

                srvc.login = function (username, password, persist, successCallback) {
                    srvc.persistToken = persist && true;
                    $http.post("/login", {
                        user: username,
                        pass: $sdHash(password)
                    }).then(function () {
                        if (!srvc.loggedIn()) {
                            $sdAlert.error("auth_login_failed");
                        }
                        $state.reload();
                        successCallback();
                    }, function () {
                        $sdAlert.error("auth_login_failed");
                    });
                };

                srvc.changePassword = function (old_password, new_password) {
                    return $http.put("/change_password", {
                        old_password: $sdHash(old_password),
                        new_password: $sdHash(new_password)
                    });
                };

                srvc.logout = function () {
                    srvc.persistToken = false;
                    srvc.updateToken(null);
                };

                authInterceptor.$auth = srvc;
                return srvc;
            }
        ])

        // Intercepts all requests to add session token and to capture refreshed session tokens
        .factory("authInterceptor", ["$q", function ($q) {
            var interceptor = {
                $auth: null,
                request: function (config) {
                    if (!interceptor.$auth || !interceptor.$auth.loggedIn() || config.url[0] !== "/") {
                        return config;
                    }

                    config.headers["SpotDesk-Authorization"] = interceptor.$auth.token;
                    return config;
                },

                response: function (response) {
                    if (interceptor.$auth && response.headers("SpotDesk-Authorization")) {
                        interceptor.$auth.updateToken(response.headers("SpotDesk-Authorization"));
                    }
                    return response;
                },
                responseError: function (response) {
                    if (response.status === 401) {
                        interceptor.$auth.persistToken = false;
                        interceptor.$auth.updateToken(null);
                    }
                    return $q.reject(response);
                }
            };
            return interceptor;
        }])

        // Controller for the entire layout
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