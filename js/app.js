var app = angular.module("quizz", ["ngRoute"]);

app.config(['$httpProvider', function($httpProvider) {
        $httpProvider.defaults.useXDomain = true;
        delete $httpProvider.defaults.headers.common['X-Requested-With'];
    }
]);

/* Routeur */
app.config(['$locationProvider', '$routeProvider', function config($locationProvider, $routeProvider) {
    $locationProvider.hashPrefix('!');
    $routeProvider.
        when('/', {
            templateUrl: 'html/debutJeu.html',
            controller: "creerController"
        }).
        when('/jeu', {
            templateUrl: "html/jeu.html",
            controller: "quizzController"
        }).
		when('/finJeu', {
            templateUrl: "html/finJeu.html",
            controller: "quizzController"
        }).
        otherwise({redirectTo: '/'});
    }
]);

/* Filtre pour afficher du HTML */
app.filter('unsafe', function($sce) {
    return function(val) {
        return $sce.trustAsHtml(val);
    };
});
