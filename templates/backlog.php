<div ng-app="comsolitBacklog" ng-controller="comsolitBacklogCtrl">

  <h4><?php echo plugin_lang_get( 'prioritized_items' )?></h4>

  <div style="border: 1px solid black; min-height: 10em;">
    <table class="width100" cellspacing="1" >
      <thead>
        <tr>
          <th>summary</th>
          <th>backlog_position</th>
        </tr>
      </thead>
      <tbody>
        <tr ng-repeat="item in items|prioritizedItems:true" style="background-color: #ffcd85">
          <td>{{item.summary}}</td>
          <td>{{item.backlog_position}}</td>
        </tr>
      </tbody>
    </table>
  </div>

  <h4><?php echo plugin_lang_get( 'unprioritized_items' )?></h4>
  <table class="width100" cellspacing="1" >
    <thead>
      <tr>
        <th>summary</th>
        <th>backlog_position</th>
      </tr>
    </thead>
    <tbody>
      <tr ng-repeat="item in items|prioritizedItems:false" style="background-color: #ffcd85">
        <td>{{item.summary}}</td>
        <td>{{item.backlog_position}}</td>
      </tr>
    </tbody>
  </table>
</div>
