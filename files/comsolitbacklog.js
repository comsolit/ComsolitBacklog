(function(){
  "use strict";

  var
    cssClassDragOver = 'comsolitbldragover',
    cssClassDragged = 'comsolitbldragged',
    comsolitBacklog = angular.module('comsolitBacklog', ['ng']),
    moveItem // function defined in comsolitBacklogCtrl
    ;

  comsolitBacklog.factory('getEmbeddedData', ['$window', function getEmbeddedDataFactory($window){
    return function(name){
      var
        context = arguments.length === 2 ? arguments[1] : 'body',
        node = $window.document.querySelector(context + ' script.embedded-json-data[data-name="' + name + '"]');
      return node.innerHTML;
    };

  }]);

  comsolitBacklog.factory('backlogItems', ['getEmbeddedData', function backlogItemsFactory(getEmbeddedData){
    return angular.fromJson(getEmbeddedData('backlogItems'));
  }]);

  comsolitBacklog.service('backlog', ['backlogItems', function(backlogItems){
	var
	  that = this,
	  minPos = -1, maxPos = -1, itemsById = {},
      min = function(xs){return Math.min.apply(null, xs);},
      max = function(xs){return Math.max.apply(null, arr);};

	  that.items = backlogItems;

	  for(var i=0; i < backlogItems.length; ++i) {
        var item = backlogItems[i];
        item.backlog_position = parseFloat(item.backlog_position) || 0.0;
        if(!minPos === -1 || (item.backlog_position && item.backlog_position < minPos)) minPos = item.backlog_position;
        if(!maxPos === -1 || (item.backlog_position && item.backlog_position > maxPos)) maxPos = item.backlog_position;
        itemsById[item.id] = item;
      }

	  function searchNextPosition(pos) {
        var nextPos = maxPos;

        for(var i = 0; i < backlogItems.length; ++i) {
          var itemPos = backlogItems[i].backlog_position;
          if(itemPos < maxPos && itemPos > pos) nextPos = itemPos;
        }

        return nextPos;
      }

      function positions(){
        return backlogItems
          .map(function(x){return x.backlog_position;})
          .filter(function(x){return x>0;});
      }

      function newMax(oldMax){
        return max(positions().filter(function(x){return x<oldMax;}));
      }

      function newMin(oldMin){
        return min(positions().filter(function(x){return x>oldMin;}));
      }

      function calcNewPos(dropPos, oldPos) {
        var newPos;

        if(!dropPos) { // item dragged on the first line
          if(oldPos && oldPos === minPos) return -1; // item was already the first
          if(oldPos === maxPos) maxPos = newMax(oldPos);
		  if(maxPos === -1) return minPos = maxPos = Math.pow(2,64); // backlog was empty
          return minPos = minPos / 2;
st      }

        if(dropPos === maxPos) {  // item dropped on the last item
          if(oldPos === minPos) minPos = newMin(oldPos);
          return maxPos = maxPos * 2;
        }

        var nextPosition = searchNextPosition(dropPos);
        if(nextPosition === oldPos) return -1; // item dropped on the item before itself
        if(oldPos === maxPos) maxPos = newMax(oldPos);
        if(oldPos === minPos) minPos = newMin(oldPos);
        return (dropPos + nextPosition) / 2;
      }

      that.moveItem = function(dragId, dropId){
        var
          dragItem = itemsById[dragId],
          oldPos = dragItem.backlog_position,
          dropItem = dropId && itemsById[dropId],
          dropPos = dropItem && dropItem.backlog_position;

        if(dragId === dropId) return false; // item dropped on itself

        var newPos = calcNewPos(dropPos, oldPos);
        if(newPos < 0) return false;
        dragItem.backlog_position = newPos;
		return true;
      };

	  that.removeItem = function(id){
		itemsById[id].backlog_position = 0;
	  };
	}]);

  comsolitBacklog.controller('comsolitBacklogCtrl', function($scope, backlogItems){
    $scope.items = backlogItems.items;
	$scope.backlog = backlogItems;
  });

  comsolitBacklog.filter('prioritizedItems', function(){
    var filters = {
      'false': function(x){return 0.0 === parseFloat(x.backlog_position);},
      'true': function(x){return parseFloat(x.backlog_position) > 0;}
    };
    return function(items, prioritized){
      var filter = filters[prioritized];
      return items.filter(filter, items);
    };
  });

  comsolitBacklog.directive('comsolitbldraggable', function() {
    return function(scope, element) {

      element.on('dragstart', function(e){
        var target = angular.element(e.target);
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('Text', target.attr('data-id'));
        e.dataTransfer.setDragImage(e.target, 0, 0);
        target.addClass(cssClassDragged);
      });

      element.on('dragend', function(e){
        var target = angular.element(e.target);
        target.removeClass(cssClassDragged);
      });

    };
  });

  comsolitBacklog.directive('comsolitbldroppable', function(backlogItems) {
    return function(scope, element) {

      element.on('dragenter', function(e){
        var target = angular.element(this);
        target.addClass(cssClassDragOver);
      });

      element.on('dragleave', function(e){
        var target = angular.element(this);
        target.removeClass(cssClassDragOver);
      });

      element.on('dragover', function(e){
        e.preventDefault();
        return false;
      });

      element.on('drop', function(e){
        var
          target = angular.element(this),
          dragId = e.dataTransfer.getData('Text'),
          dropId = target.attr('data-id')
        ;

        target.removeClass(cssClassDragOver);

        backlogItems.moveItem(dragId, dropId);
      });
    };
  });
})();