(function(){
  "use strict";

  var comsolitBacklog = angular.module('comsolitBacklog', ['ng']);

  comsolitBacklog.factory('getEmbeddedData', ['$document', function getEmbeddedDataFactory($document){
    return function(name){
      var
        context = arguments.length === 2 ? arguments[1] : 'body',
        node = $document[0].querySelector(context + ' script.embedded-json-data[data-name="' + name + '"]');
      return node.innerHTML;
    };

  }]);

  comsolitBacklog.factory('backlogItems', ['getEmbeddedData', function backlogItemsFactory(getEmbeddedData){
    return angular.fromJson(getEmbeddedData('backlogItems'));
  }]);

  comsolitBacklog.service('backlog', ['backlogItems', '$log', function(backlogItems, $log){
    var
      minPos, maxPos, itemsById = {},
      min = function(xs){return Math.min.apply(null, xs);},
      max = function(xs){return Math.max.apply(null, xs);};

    this.items = backlogItems;

    for(var i=0; i < backlogItems.length; ++i) {
      var item = backlogItems[i];
      item.backlog_position = parseFloat(item.backlog_position) || 0.0;
      itemsById[item.id] = item;
    }

    minPos = newMin(Number.NEGATIVE_INFINITY);
    maxPos = newMax(Number.POSITIVE_INFINITY);

    function isPrioritized(x){
      return x.backlog_position > 0;
    }

    function positions(){
      return backlogItems
        .filter(isPrioritized)
        .map(function(x){return x.backlog_position;});
    }

    function newMax(oldMax){
      return max(positions().filter(function(x){return x<oldMax;}));
    }

    function newMin(oldMin){
      return min(positions().filter(function(x){return x>oldMin;}));
    }

    function rebalance(){
      $log.debug('backlog.rebalance()');
      var newPos = 0;
      angular.forEach(
        backlogItems.filter(isPrioritized).sort(function(a,b){return a.backlog_position - b.backlog_position;}),
        function(v){
          v.backlog_position = newPos = newPos + Math.pow(2, 16);
        }
      );
      minPos = newMin(Number.NEGATIVE_INFINITY);
      maxPos = newMax(Number.POSITIVE_INFINITY);
    }

    function calcNewPos(dropPos, oldPos) {
      $log.debug('backlog.calcNewPos(dropPos:' + dropPos + ', oldPos:' + oldPos + ')');
      var newPos;

      if(!dropPos) { // item dragged on the first line
        if(oldPos && oldPos === minPos) return -1; // item was already the first
        if(oldPos === maxPos) maxPos = newMax(oldPos);
        if(maxPos === Number.NEGATIVE_INFINITY) return [minPos = maxPos = Math.pow(2, 16), false]; // backlog was empty
        return [minPos = minPos / 2, minPos < 1];
      }

      if(dropPos === maxPos) {  // item dropped on the last item
        if(oldPos === minPos) minPos = newMin(oldPos);
        return [maxPos = maxPos + 2048, maxPos > Math.pow(2, 32)];
      }

      var nextPosition = newMin(dropPos);
      if(nextPosition === oldPos) return -1; // item dropped on the item before itself
      if(oldPos === maxPos) maxPos = newMax(oldPos);
      if(oldPos === minPos) minPos = newMin(oldPos);
      return [(dropPos + nextPosition) / 2, Math.abs(dropPos - nextPosition) < 1];
    }

    this.moveItem = function(dragId, dropId){
      $log.debug('backlog.moveItem(dragId: ' + dragId + ', dropId: ' + dropId + ')');
      var
        dragItem = itemsById[dragId],
        oldPos = dragItem.backlog_position,
        dropItem = dropId && itemsById[dropId],
        dropPos = dropItem && dropItem.backlog_position;

      if(dragId === dropId) return false; // item dropped on itself

      var newPos = calcNewPos(dropPos, oldPos);
      if(newPos < 0) return false;
      dragItem.backlog_position = newPos[0];
      if(newPos[1]) rebalance();
      return true;
    };

    this.removeItem = function(id){
      $log.debug('backlog.removeItem(id: ' + id + ')');
      var oldPos = itemsById[id].backlog_position;
      if(oldPos === maxPos) maxPos = newMax(oldPos);
      if(oldPos === minPos) minPos = newMin(oldPos);
      itemsById[id].backlog_position = 0;
    };
  }]);

  comsolitBacklog.factory('fatalErrorModal', function($document){
    return new function FatalErrorModal(){

      function createElement(name) {
        return angular.element($document[0].createElement(name));
      }

      function createModal(content) {
        var
          modalOutter = createElement('div'),
          modalInner = createElement('div');

        modalOutter.css({
          position: 'fixed',
          top: '0',
          left: '0',
          width: '100%',
          height: '100%',
          'background-color': 'rgba(0, 0, 0, 0.8)',
          'z-index': '1000'
        });

        modalInner.css({
          opacity: '1',
          'background-color': 'rgba(255, 255, 255, 1)',
          color: 'rgba(0, 0, 0, 1)'
        });

        return modalOutter
          .append('<div style="color:red;text-align:center;font-weight:bold;font-size:2em">An error occured. Please reload the page!</div>')
          .append(modalInner.append(content));
      }

      function bodyAppend(element) {
        return angular.element($document[0].getElementsByTagName('body')[0]).append(element);
      }

      function showModal(content) {
        bodyAppend(createModal(content));
      }

      function row(k, v) {
        return '<tr><td>' + k + '</td><td>' + v + '</td>';
      }

      function exceptionDataToTable(e) {
        return '<table>'
          + row('message', e['message'])
          + row('file', e['file'] + '(' + e['line'] + ')')
          + row('trace', e['traceString']).replace(/\n/g, '<br />')
          + '</table>';
      }

      this.showHtmlPage = function(data, status, headers) {
        showModal(data);
      };

      this.showErrorData = function(data, status, headers) {
        showModal(exceptionDataToTable(data));
      };
    };
  });

  comsolitBacklog.service('postQueue', function($http, $log, fatalErrorModal){
    var queue = this.queue = []

    this.push = function(action){
      if(queue.push(action) === 1) post(action);
    }

    function postSuccess(data, status, headers, config){
      // TODO: mantis does not send a correct http error code, so we still might need to check for a mantis error page here
      var contentType = headers('Content-Type');
      if(contentType !== 'application/json') {
        $log.error('invalid Content-Type: ' + contentType);
        if(new RegExp('html', 'i').test('html')) fatalErrorModal.showHtmlPage(data, status, headers);
        return;
      }
      queue.shift();
      if(queue.length) post(queue[0]);
    }

    function postError(data, status, headers){
      $log.error('postError status: ' . status);
      fatalErrorModal.showErrorData(data);
    }

    function post(action){
      $log.info('post ' + angular.toJson(action));
      $http.post(
        'plugin.php?page=ComsolitBacklog/backlog_action',
        action
      ).success(postSuccess).error(postError);
    }
  });

  comsolitBacklog.controller('comsolitBacklogCtrl', function($scope, backlog, postQueue){

    $scope.backlogItems = backlog.items;
    $scope.postQueue = postQueue.queue;

    $scope.moveItem = function(dragId, dropId){
      var result = backlog.moveItem(dragId, dropId);
      if(result) postQueue.push({
          action: 'move',
          dragId: dragId,
          dropId: dropId || null // the dropId property is not serialized for value 'undefined'
      });
      return result;
    };

    $scope.removeItem = function(id){
      backlog.removeItem(id);
      postQueue.push({
        action: 'remove',
        id: id
      });
    };
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

  comsolitBacklog.directive('comsolitBacklogDraggable', function() {
    return function(scope, element, attributes) {
      var cssClassDragged = attributes['comsolitBacklogDraggable'];

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

  comsolitBacklog.directive('comsolitBacklogDroppable', function() {
    return function(scope, element, attributes) {
      var cssClassDragOver = attributes['comsolitBacklogDroppable'];

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

        var takeAction = scope.moveItem(dragId, dropId);
        if(takeAction) scope.$apply();
      });
    };
  });
})();