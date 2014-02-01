"use strict";

describe("Comsolit Backlog Spec", function() {

  beforeEach(function(){
	module('comsolitBacklog');
  });

  it("Angular Module is defined", function(){
    expect(angular.injector(['comsolitBacklog'])).toBeTruthy();
  });

  describe("getEmbeddedData", function(){
    var getEmbeddedData, querySelectorMock;

    beforeEach(function(){
      querySelectorMock = jasmine.createSpy("querySelectorSpy").andReturn({innerHTML: "hallo Welt innerHTML"});
      var windowMock = {document: {querySelector: querySelectorMock}};

      module(function($provide){
        $provide.value('$window', windowMock);
      });

      inject(function(_getEmbeddedData_){
        getEmbeddedData = _getEmbeddedData_;
      });
    });

    it("calls query selector", function(){
      getEmbeddedData('hi');
      expect(querySelectorMock).toHaveBeenCalled();
    });

    it("selector contains name", function(){
      getEmbeddedData('hi');
      expect(querySelectorMock.mostRecentCall.args[0]).toContain('hi');
    });

    it("selector contains context selector", function(){
      getEmbeddedData('hi', '.mycontext');
      expect(querySelectorMock.mostRecentCall.args[0]).toContain('.mycontext ');
    });

    it("selector contains context selector", function(){
      getEmbeddedData('hi', '#mycontext');
      expect(querySelectorMock.mostRecentCall.args[0]).toEqual('#mycontext script.embedded-json-data[data-name="hi"]');
    });

    it("returns innerHTML", function(){
      expect(getEmbeddedData('hi')).toEqual("hallo Welt innerHTML");
    });
  });

  describe("items", function(){
	beforeEach(function(){
	  module(function($provide){
		$provide.constant('getEmbeddedData', function(){
		  return "{\"hallo\": 42}";
		});
	  });
	});

    it("items parses JSON", inject(function(backlogItems){
	  expect(backlogItems).toEqual({hallo:42});
	}));
  });

  describe("Backlog", function(){
	var backlog, items, moveItem, removeItem;

	function getItemById(id){
	  for(var i = 0; i < items.length; ++i){
		if(items[i].id === id) return items[i];
	  }
	  throw "no item with id " + id;
	}

	function expectBacklog(expected){
	  var actual = items
		.filter(function(x){return x.backlog_position>0;})
	    .sort(function(a,b){return a.backlog_position-b.backlog_position;})
	    .map(function(x){return x.id;});

	  expect(actual).toEqual(expected);
	}

    function moveItems(moves){
      var result;
      for(var i = 0; i < moves.length; ++i){
        var move = moves[i];
        if(angular.isNumber(move)) {
          moveItem(move);
        } else if(angular.isArray(move)) {
          if(angular.isNumber(move[move.length-1])) moveItem(move[0], move[1]);
          else if(move.length === 2) expect(moveItem(move[0])).toBe(move[1]);
          else if(move.length === 3) expect(moveItem(move[0], move[1])).toBe(move[2]);
          else throw "WTF?";
        } else throw "unsupported";
      }
    }

	beforeEach(function(){
	  module(function($provide){
        $provide.constant("backlogItems", [
          {id:1, backlog_position:0},
		  {id:2, backlog_position:0.0},
		  {id:3, backlog_position:"0"},
		  {id:4, backlog_position:null},
		  {id:5, backlog_position:null}
		]);
	  });

	  inject(function(_backlog_){
		backlog = _backlog_;
		items = backlog.items;
		moveItem = backlog.moveItem;
	    removeItem = backlog.removeItem;
	  });
	});

	it("backlog_position is parsed as Float", function(){
	  for(var i = 0; i < items.length; ++i){
		expect(items[i].backlog_position).toBe(0.0);
	  }
	});

	it("items is of length 5", function(){
	  expect(items.length).toEqual(5);
	});

	it("prioritize first item", function(){
	  var result = moveItem(1);
      expect(result).toBe(true);
	  expect(getItemById(1).backlog_position).toBeGreaterThan(0);
	});

	it("remove item from backlog", function(){
	  removeItem(2);
	  expect(getItemById(2).backlog_position).toBe(0);
	});

	it("prioritize all items", function(){
	  moveItem(1);
	  moveItem(2);
	  moveItem(3);
	  moveItem(4);
	  moveItem(5);

	  expectBacklog([5,4,3,2,1]);
	});

	it("move all items on first item", function(){
	  moveItem(1);
	  moveItem(2,1);
	  moveItem(3,2);
	  moveItem(4,3);
	  moveItem(5,4);
	  expectBacklog([1,2,3,4,5]);
	});


	it("move item on itself", function(){
      var old = {};
	  moveItem(1);
	  old[1] = getItemById(1).backlog_position;
      moveItem(2, 1);
	  old[2] = getItemById(2).backlog_position;
	  expect(moveItem(2, 2)).toBe(false);
	  expect(getItemById(1).backlog_position).toBe(old[1]);
	  expect(getItemById(2).backlog_position).toBe(old[2]);
	});

	it("move new item between two other Items", function(){
	  moveItem(2);
	  moveItem(3, 2);
	  moveItem(4, 2);
	  expectBacklog([2, 4, 3]);
	});

	it("move existing item one up", function(){
      moveItem(1);
	  moveItem(2);
      moveItem(3);
      moveItem(4);
	  expect(moveItem(2, 4)).toBe(true);
	  expectBacklog([4, 2, 3, 1]);
	});

    it("move existing item one down", function(){
      moveItem(1);
	  moveItem(2);
      moveItem(3);
      moveItem(4);
	  expect(moveItem(3, 2)).toBe(true);
	  expectBacklog([4, 2, 3, 1]);
	});

	it("move existing item to first", function(){
      moveItem(1);
	  moveItem(2);
      moveItem(3);
      moveItem(4);
	  expect(moveItem(2)).toBe(true);
	  expectBacklog([2, 4, 3, 1]);
	});

	it("move existing item to last", function(){
      moveItem(1);
	  moveItem(2);
      moveItem(3);
      moveItem(4);
	  expect(moveItem(3, 1)).toBe(true);
	  expectBacklog([4, 2, 1, 3]);
	});


	it("move first item in middle and new first on top", function(){
      moveItems([3, 2, 1, [1, 2]]);
	  expectBacklog([2, 1, 3]);
      moveItems([[2, false], [2, 2, false]]);
	});

	it("move item on item before itself", function(){
      moveItems([4, 3, 2, 1, [3, 2, false]]);
	  expectBacklog([1, 2, 3, 4]);
	});
  });
});