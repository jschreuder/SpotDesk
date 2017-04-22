(function () {
    "use strict";

    angular.module("spotdesk", ["ngMaterial", "ngCookies", "ui.router", "md.data.table"])

        .config(["$httpProvider", "$mdThemingProvider",
            function($httpProvider, $mdThemingProvider){
                $httpProvider.interceptors.push("authInterceptor");

                $mdThemingProvider.theme("default")
                    .primaryPalette("green")
                    .accentPalette("pink");
            }
        ])

        .factory("$auth", ["$http", "$cookies", "authInterceptor", function ($http, $cookies, authInterceptor) {
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
                        console.log("SpotDesk: successful return status, but no authorization token found.");
                        alert("auth_login_failed");
                    }
                }, function errorCallback() {
                    alert("auth_login_failed");
                });
            };

            authInterceptor.$auth = srvc;
            return srvc;
        }])

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

        .controller("mainController", ["$mdSidenav", "$auth", "$title", function ($mdSidenav, $auth, $title) {
            var ctrl = this;

            ctrl.loggedIn = $auth.loggedIn;
            ctrl.user = {
                name: null,
                pass: null,
                persist: false
            };

            ctrl.login = function () {
                if (!ctrl.user.name || !ctrl.user.pass) {
                    alert("auth_missing_field");
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
        }]);
})();