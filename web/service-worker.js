"use strict";

var CACHE_NAME = 'spotdesk';
var urlsToCache = [
    "./index.html",
    "./assets/img/mail.svg",
    "./assets/img/menu.svg",
    "./assets/style/spotdesk.css",
    "./assets/templates/config.html",
    "./assets/templates/tickets.html",
    "./node_modules/angular/angular.js",
    "./node_modules/angular-animate/angular-animate.js",
    "./node_modules/angular-aria/angular-aria.js",
    "./node_modules/angular-material/angular-material.js",
    "./node_modules/angular-ui-router/release/angular-ui-router.js",
    "./src/spotdesk.js",
    "./src/routing.js"
];

self.addEventListener('install', function(event) {
    // Perform install steps
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
    );
});
