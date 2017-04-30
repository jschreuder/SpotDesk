<!doctype html>

<html>

<head>
    <meta charset="utf-8" />

    <title><?php echo $siteTitle; ?></title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no" />
    <link rel="manifest" href="./manifest.json" />

    <link rel="stylesheet" href="./node_modules/angular/angular-csp.css" />
    <link rel="stylesheet" href="./node_modules/angular-material/angular-material.css" />
    <link rel="stylesheet" href="./node_modules/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="./node_modules/angular-material-data-table/dist/md-data-table.css" />
    <link rel="stylesheet" href="assets/style/spotdesk.css" />
</head>

<body ng-app="spotdesk" ng-csp ng-controller="mainController as main" layout="column" ng-cloak>

<div flex layout="row" ng-show="main.loggedIn()">
    <md-sidenav ng-click="main.toggleSideNav()" md-is-locked-open="$mdMedia('gt-sm')" md-component-id="left" class="md-whiteframe-z2" >
        <md-toolbar layout="row">
            <h1><?php echo $siteTitle; ?></h1>
        </md-toolbar>

        <md-list>
            <md-list-item class="header">
                Tickets
            </md-list-item>
            <md-list-item>
                <md-button ui-sref="tickets" ui-sref-active="active">
                    <i class="fa fa-envelope-open menu-icon" aria-hidden="true"></i>
                    Open
                </md-button>
            </md-list-item>
            <md-list-item>
                <md-button ui-sref="tickets_status_type({ status_type: 'paused' })" ui-sref-active="active">
                    <i class="fa fa-pause-circle menu-icon" aria-hidden="true"></i>
                    Paused
                </md-button>
            </md-list-item>
            <md-list-item>
                <md-button ui-sref="tickets_status_type({ status_type: 'closed' })" ui-sref-active="active">
                    <i class="fa fa-envelope menu-icon" aria-hidden="true"></i>
                    Closed
                </md-button>
            </md-list-item>
            <md-list-item class="header">
                User
            </md-list-item>
            <md-list-item>
                <md-button ui-sref="change_password" ui-sref-active="active">
                    <i class="fa fa-key menu-icon" aria-hidden="true"></i>
                    Change password
                </md-button>
            </md-list-item>
            <md-list-item>
                <md-button ng-click="main.logout()">
                    <i class="fa fa-sign-out menu-icon" aria-hidden="true"></i>
                    Logout
                </md-button>
            </md-list-item>
            <md-list-item class="header">
                Configuration
            </md-list-item>
            <md-list-item>
                <md-button ui-sref="users" ui-sref-active="active">
                    <i class="fa fa-users menu-icon" aria-hidden="true"></i>
                    Users
                </md-button>
            </md-list-item>
            <md-list-item>
                <md-button ui-sref="departments" ui-sref-active="active">
                    <i class="fa fa-building menu-icon" aria-hidden="true"></i>
                    Departments
                </md-button>
            </md-list-item>
            <md-list-item>
                <md-button ui-sref="mailboxes" ui-sref-active="active">
                    <i class="fa fa-folder-open menu-icon" aria-hidden="true"></i>
                    Mailboxes
                </md-button>
            </md-list-item>
            <md-list-item>
                <md-button ui-sref="statuses" ui-sref-active="active">
                    <i class="fa fa-tags menu-icon" aria-hidden="true"></i>
                    Statuses
                </md-button>
            </md-list-item>
        </md-list>
    </md-sidenav>

    <md-content layout="column" flex>
        <md-toolbar layout="row">
            <md-button ng-click="main.toggleSideNav()" class="menu" hide-gt-sm aria-label="Show User List">
                <i class="fa fa-bars" aria-hidden="true"></i>
            </md-button>
            <h1>{{ main.showTitle() }}</h1>
        </md-toolbar>

        <ui-view flex id="content">
            <h3>Loading</h3>
        </ui-view>
    </md-content>
</div>

<div id="login" flex layout="row" ng-hide="main.loggedIn()">
    <md-content layout="column" flex>
        <md-card>
            <md-card-content>
                <md-input-container>
                    <label for="user_name">Username</label>
                    <input id="user_name" ng-model="main.user.name" type="email">
                </md-input-container>

                <md-input-container>
                    <label for="user_pass">Password</label>
                    <input id="user_pass" ng-model="main.user.pass" type="password">
                </md-input-container>

                <md-input-container class="md-block">
                    <md-switch class="md-primary" ng-model="main.user.persist">
                        Persist login in cookie
                    </md-switch>
                </md-input-container>

                <div>
                    <md-button type="submit" md-no-ink class="md-primary" ng-click="main.login()">Login</md-button>
                </div>
            </md-card-content>
        </md-card>
    </md-content>
</div>

<script src="./node_modules/angular/angular.js"></script>
<script src="./node_modules/angular-animate/angular-animate.js"></script>
<script src="./node_modules/angular-aria/angular-aria.js"></script>
<script src="./node_modules/angular-cookies/angular-cookies.js"></script>
<script src="./node_modules/angular-material/angular-material.js"></script>
<script src="./node_modules/angular-ui-router/release/angular-ui-router.js"></script>
<script src="./node_modules/angular-material-data-table/dist/md-data-table.js"></script>
<script src="./node_modules/jssha/src/sha512.js"></script>
<script src="./src/spotdesk.js"></script>
<script src="./src/routing.js"></script>
<script src="./src/services.js"></script>
<script src="./src/services/auth.js"></script>
<script src="./src/services/departments.js"></script>
<script src="./src/services/tickets.js"></script>
<script src="./src/services/users.js"></script>
<script src="./src/controllers/departments.js"></script>
<script src="./src/controllers/mailboxes.js"></script>
<script src="./src/controllers/main.js"></script>
<script src="./src/controllers/statuses.js"></script>
<script src="./src/controllers/tickets.js"></script>
<script src="./src/controllers/users.js"></script>

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('./service-worker.js').then(function(registration) {
                // Registration was successful
                console.log('ServiceWorker registration successful with scope: ', registration.scope);
            }, function(err) {
                // registration failed :(
                console.log('ServiceWorker registration failed: ', err);
            });
        });
    }
</script>

</body>

</html>
