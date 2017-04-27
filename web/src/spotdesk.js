(function () {
    "use strict";

    angular.module("spotdesk", ["ngMaterial", "ngCookies", "ui.router", "md.data.table"])

        // Main configuration
        .config(["$httpProvider", "$mdThemingProvider",
            function($httpProvider , $mdThemingProvider){
                $httpProvider.interceptors.push("authInterceptor");

                $mdThemingProvider.theme("default")
                    .primaryPalette("green")
                    .accentPalette("pink");
            }
        ])

        .factory("$adminMessage", ["$mdDialog", function ($mdDialog) {
            var srvc = this;

            srvc.error = function (errorMessage) {
                $mdDialog.show(
                    $mdDialog.alert()
                        .parent(angular.element(document.getElementById("content")))
                        .clickOutsideToClose(true)
                        .title('error')
                        .textContent(errorMessage)
                        .ariaLabel('Error')
                        .ok('OK')
                );
            };

            return srvc;
        }])

        // Authentication service
        .factory("$auth", ["$http", "$cookies", "$state", "$adminMessage", "authInterceptor",
            function ($http, $cookies, $state, $adminMessage, authInterceptor) {
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
                        pass: password
                    }).then(function () {
                        if (!srvc.loggedIn()) {
                            $adminMessage.error("auth_login_failed");
                        }
                        $state.reload();
                        successCallback();
                    }, function () {
                        $adminMessage.error("auth_login_failed");
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
        .controller("mainController", ["$mdSidenav", "$mdDialog", "$auth", "$title", "$adminMessage",
            function ($mdSidenav, $mdDialog, $auth, $title, $adminMessage) {
                var ctrl = this;

                ctrl.loggedIn = $auth.loggedIn;
                ctrl.user = {
                    name: null,
                    pass: null,
                    persist: false
                };

                ctrl.login = function () {
                    if (!ctrl.user.name || !ctrl.user.pass) {
                        $adminMessage.error("auth_missing_field");
                        return;
                    }

                    $auth.login(ctrl.user.name, ctrl.user.pass, ctrl.user.persist, function () {
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
                        $auth.logout();
                    });
                };

                ctrl.showTitle = function () {
                    if ($auth.loggedIn()) {
                        return $title.get();
                    }
                    return "Login";
                };

                ctrl.toggleSideNav = function() {
                    $mdSidenav("left").toggle();
                };
            }
        ]);
})();