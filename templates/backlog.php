<div ng-app="comsolitBacklog" ng-controller="comsolitBacklogCtrl">
  <table class="width100" cellspacing="1" >
    <thead>
      <tr>
        <th>summary</th>
        <th>backlog_position</th>
      </tr>
    </thead>
    <tbody>
      <tr ng-repeat="item in items" style="background-color: #ffcd85">
        <td>{{item.summary}}</td>
        <td>{{item.backlog_position}}</td>
      </tr>
    </tbody>
  </table>
</div>
