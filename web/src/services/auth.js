/** global: jsSHA */

(function () {
    "use strict";

    angular.module("spotdesk")
        .factory("$sdAuth",
            ["$http", "$cookies", "$state", "$sdAlert", "$sdHash", "authInterceptor",
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
            }]
        )

        .factory("$sdHash", function () {
            return function (password) {
                var sha = new jsSHA("SHA-512", "TEXT");
                sha.update(password);
                return sha.getHash("HEX");
            };
        })

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
        }]);
})();
