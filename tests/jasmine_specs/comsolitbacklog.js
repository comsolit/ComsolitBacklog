"use strict";

describe("Comsolit Backlog Spec", function() {

  it("Angular Module is defined", function(){
    expect(angular.injector(['comsolitBacklog'])).toBeTruthy();
  });

  describe("getEmbeddedData", function(){
    var getEmbeddedData, querySelectorMock;

    beforeEach(function(){
      querySelectorMock = jasmine.createSpy("querySelectorSpy").andReturn({innerHTML: "hallo Welt innerHTML"});
      var windowMock = {document: {querySelector: querySelectorMock}};

      module('comsolitBacklog');
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
	  module('comsolitBacklog');
	  module(function($provide){
		$provide.constant('getEmbeddedData', function(){
		  return "{\"hallo\": 42}";
		});
	  });
	});

    it("", inject(function(backlogItems){
	  expect(backlogItems).toEqual({hallo:42});
	}));
  });
});