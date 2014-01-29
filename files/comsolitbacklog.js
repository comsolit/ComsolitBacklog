(function(){
  comsolitBacklog = angular.module('comsolitBacklog', ['ng']);

  comsolitBacklog.controller('comsolitBacklogCtrl', function($scope){
    $scope.items = getEmbeddedJSON('backlogItems');
  });

  comsolitBacklog.filter('prioritizedItems', function(){
    var filters = {
      'false': function(x){return 0.0 === parseFloat(x.backlog_position)},
      'true': function(x){return parseFloat(x.backlog_position) > 0}
    };
    return function(items, prioritized){
      var filter = filters[prioritized];
      return items.filter(filter, items);
    };
  });

  function getEmbeddedJSON(name) {
    var context = arguments.length === 2 ? arguments[1] : 'body';

    var node = document.querySelector(context + ' script.embedded-json-data[data-name="' + name + '"]');
    var unparsed = node.innerHTML;
    return JSON.parse(unparsed);
  }

})();