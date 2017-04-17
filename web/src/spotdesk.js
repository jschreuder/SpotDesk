(function () {
    "use strict";

    angular.module("spotdesk", ["ngMaterial", "ui.router"])

        .config(function($mdThemingProvider, $mdIconProvider){
            $mdIconProvider
                .icon("menu", "./assets/img/menu.svg", 24)
                .icon("mail", "./assets/img/mail.svg", 24);

            $mdThemingProvider.theme("default")
                .primaryPalette("green")
                .accentPalette("pink");
        })

        .controller("mainController", ["$mdSidenav", function ($mdSidenav) {
            var ctrl = this;

            ctrl.toggleSideNav = function() {
                $mdSidenav("left").toggle();
            };
        }]);
})();