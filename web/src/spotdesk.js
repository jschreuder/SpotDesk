(function () {
    "use strict";

    angular.module("spotdesk", ["ngMaterial", "ui.router"])

        .config(["$httpProvider", "$mdThemingProvider", "$mdIconProvider",
            function($httpProvider, $mdThemingProvider, $mdIconProvider){
                $httpProvider.interceptors.push("authInterceptor");

                $mdIconProvider
                    .icon("menu", "./assets/img/menu.svg", 24)
                    .icon("mail", "./assets/img/mail.svg", 24);

                $mdThemingProvider.theme("default")
                    .primaryPalette("green")
                    .accentPalette("pink");
            }
        ])

        .factory("$auth", ["$http", "authInterceptor", function ($http, authInterceptor) {
            var srvc = this;

            srvc.token = null;

            srvc.loggedIn = function() {
                return srvc.token !== null;
            };

            srvc.login = function (username, password) {
                $http.post("/login", {
                    user: username,
                    pass: password
                }).then(function successCallback(response) {
                    var token = response.headers("SpotDesk-Authorization");
                    if (!token) {
                        console.log("SpotDesk: successful return status, but no authorization token found.");
                        alert("auth_login_failed");
                        return;
                    }

                    srvc.token = token;
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
                }
            };
            return interceptor;
        })

        .controller("mainController", ["$mdSidenav", "$auth", function ($mdSidenav, $auth) {
            var ctrl = this;

            ctrl.loggedIn = $auth.loggedIn;
            ctrl.user = {
                name: null,
                pass: null
            };

            ctrl.login = function () {
                if (!ctrl.user.name || !ctrl.user.pass) {
                    alert("auth_missing_field");
                    return;
                }

                $auth.login(ctrl.user.name, ctrl.user.pass);
            };

            ctrl.toggleSideNav = function() {
                $mdSidenav("left").toggle();
            };
        }]);
})();