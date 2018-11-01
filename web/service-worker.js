"use strict";

var CACHE_NAME = 'spotdesk-v1.1-dev';

var urlsToCache = [
    // main file
    "./",

    // stylesheets
    "./assets/style/spotdesk.css",

    // fonts
    "./node_modules/font-awesome/fonts/FontAwesome.otf?v=4.7.0",
    "./node_modules/font-awesome/fonts/fontawesome-webfont.eot?v=4.7.0",
    "./node_modules/font-awesome/fonts/fontawesome-webfont.svg?v=4.7.0",
    "./node_modules/font-awesome/fonts/fontawesome-webfont.ttf?v=4.7.0",
    "./node_modules/font-awesome/fonts/fontawesome-webfont.woff?v=4.7.0",
    "./node_modules/font-awesome/fonts/fontawesome-webfont.woff2?v=4.7.0",

    // 3rd party dependencies
    "./node_modules/angular/angular.js",
    "./node_modules/angular/angular-csp.css",
    "./node_modules/angular-animate/angular-animate.js",
    "./node_modules/angular-aria/angular-aria.js",
    "./node_modules/angular-cookies/angular-cookies.js",
    "./node_modules/angular-material/angular-material.css",
    "./node_modules/angular-material/angular-material.js",
    "./node_modules/@uirouter/angularjs/release/angular-ui-router.js",
    "./node_modules/angular-material-data-table/dist/md-data-table.js",
    "./node_modules/angular-material-data-table/dist/md-data-table.css",
    "./node_modules/font-awesome/css/font-awesome.css",
    "./node_modules/jssha/src/sha512.js",

    // views
    "./assets/templates/departments/list.html",
    "./assets/templates/mailboxes/list.html",
    "./assets/templates/statuses/list.html",
    "./assets/templates/tickets/list.html",
    "./assets/templates/tickets/view.html",
    "./assets/templates/users/list.html",
    "./assets/templates/users/view.html",

    // js sources
    "./src/controllers/departments.js",
    "./src/controllers/mailboxes.js",
    "./src/controllers/main.js",
    "./src/controllers/statuses.js",
    "./src/controllers/tickets.js",
    "./src/controllers/users.js",
    "./src/routing.js",
    "./src/services.js",
    "./src/services/auth.js",
    "./src/services/departments.js",
    "./src/services/tickets.js",
    "./src/services/users.js",
    "./src/spotdesk.js"
];

self.addEventListener('install', function(event) {
    // Perform install steps
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                return cache.addAll(urlsToCache);
            })
    );
});
