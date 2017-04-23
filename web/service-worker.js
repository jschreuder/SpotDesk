"use strict";

var CACHE_NAME = 'spotdesk';

var urlsToCache = [
    // main file
    "./",

    // stylesheets
    "./assets/style/spotdesk.css",

    // 3rd party dependencies
    "./node_modules/angular/angular.js",
    "./node_modules/angular/angular-csp.css",
    "./node_modules/angular-animate/angular-animate.js",
    "./node_modules/angular-aria/angular-aria.js",
    "./node_modules/angular-cookies/angular-cookies.js",
    "./node_modules/angular-material/angular-material.css",
    "./node_modules/angular-material/angular-material.js",
    "./node_modules/angular-ui-router/release/angular-ui-router.js",
    "./node_modules/angular-material-data-table/dist/md-data-table.js",
    "./node_modules/angular-material-data-table/dist/md-data-table.css",
    "./node_modules/font-awesome/css/font-awesome.css",

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
    "./src/controllers/statuses.js",
    "./src/controllers/tickets.js",
    "./src/controllers/users.js",
    "./src/routing.js",
    "./src/services.js",
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
