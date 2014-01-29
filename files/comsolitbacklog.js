(function(){
  comsolitBacklog = angular.module('comsolitBacklog', ['ng']);

  comsolitBacklog.controller('comsolitBacklogCtrl', function($scope){
    $scope.items = getEmbeddedJSON('backlogItems');
  });

  function getEmbeddedJSON(name) {
    var context = arguments.length === 2 ? arguments[1] : 'body';

    var node = document.querySelector(context + ' script.embedded-json-data[data-name="' + name + '"]');
    var unparsed = node.innerHTML;
    return JSON.parse(unparsed);
  }

})();