describe("Comsolit Backlog Spec", function() {

  it("Comsolit Backlog Angular Module is defined", function(){
    var comsolitBacklogModule = angular.injector(['comsolitBacklog']);

    expect(comsolitBacklogModule).toBeDefined();
    expect(comsolitBacklogModule).not.toBeNull();
    expect(comsolitBacklogModule).toBeTruthy();
  });
});