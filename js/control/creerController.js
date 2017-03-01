app.controller("creerController", ["$scope", "$http", "$location",
function($scope, $http, $location) {
    var storageAvailable = function(type) {
		try {
			var storage = window[type],
				x = '__storage_test__';
			storage.setItem(x, x);
			storage.removeItem(x);
			return true;
		}
		catch(e) {
			return false;
		}
	};

	if(!storageAvailable('localStorage')) {
		alert('localStorage indisponible sur votre navigateur !');
		return false;
	}

    var htmlEntities = function(str) {
		return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	};

    var errorHandler = function(e) {
		console.log(e);
	}
	
    $scope.partieSolo = function()
	{
        // Mode de jeu
		localStorage.removeItem("mode");
		localStorage.setItem('mode', 'solo');
		$location.path('/jeu');
	};
	
	$scope.partieMulti = function()
	{
        // Mode de jeu
		localStorage.removeItem("mode");
		localStorage.setItem('mode', 'multi');
		$location.path('/jeu');
	};
}]);
