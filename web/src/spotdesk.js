/** global: jsSHA */

(function () {
    "use strict";

    angular.module("spotdesk", ["ngMaterial", "ngCookies", "ui.router", "md.data.table"])
        .config(["$httpProvider", "$mdThemingProvider",
            function($httpProvider , $mdThemingProvider) {
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

        .factory("$sdTitle", function () {
            return {
                _current: null,

                get: function () {
                    return this._current;
                },

                change: function (newTitle) {
                    this._current = newTitle;
                }
            };
        });
})();
