(function () {
    "use strict";

    angular.module("spotdesk", ["ngMaterial", "ngCookies", "ui.router", "md.data.table"])

        // Main configuration
        .config(["$httpProvider", "$mdThemingProvider",
            function($httpProvider , $mdThemingProvider){
                $httpProvider.interceptors.push("authInterceptor");

                // Theming is not allowed due to CSP
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

                srvc.login = function (username, password, persist) {
                    srvc.persistToken = persist && true;
                    $http.post("/login", {
                        user: username,
                        pass: password
                    }).then(function successCallback() {
                        if (!srvc.loggedIn()) {
                            $adminMessage.error("auth_login_failed");
                        }
                        $state.reload();
                    }, function errorCallback() {
                        $adminMessage.error("auth_login_failed");
                    });
                };

                authInterceptor.$auth = srvc;
                return srvc;
            }
        ])

        // Intercepts all requests to add session token and to capture refreshed session tokens
        .factory("authInterceptor", function () {
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
                    if (response.status === 401) {
                        interceptor.$auth.persistToken = false;
                        interceptor.$auth.updateToken(null);
                    }
                    return response;
                }
            };
            return interceptor;
        })

        // Controller for the entire layout
        .controller("mainController", ["$mdSidenav", "$auth", "$title", "$adminMessage",
            function ($mdSidenav, $auth, $title, $adminMessage) {
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

                    $auth.login(ctrl.user.name, ctrl.user.pass, ctrl.user.persist);
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