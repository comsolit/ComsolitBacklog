<div ng-app="comsolitBacklog" ng-controller="comsolitBacklogCtrl">

  Queue ({{postQueue.length}}):
  <div style="min-height:4em" style="border: 1px solid black">
    <span ng-repeat="a in postQueue">
      <span ng-if="a.action === 'move'" class="move action">
        <span>move {{a.dragId}}</span>
        <span ng-if="a.dropId">under {{a.dropId}}</span>
      </span>
      <span ng-if="a.action === 'remove'" class="remove action">remove {{a.id}}</span>
    </span>
  </div>

  <h4><?php echo plugin_lang_get( 'prioritized_items' )?> ({{itemsInBacklog.length}})</h4>
  <div>
    <table style="width:100%" cellspacing="1">
      <thead>
        <tr>
          <th>nr</th>
          <th>id</th>
          <th>summary</th>
          <th>points</th>
          <th>backlog_position (test)</th>
          <th>action</th>
        </tr>
      </thead>
      <tbody>
        <tr comsolit-backlog-droppable="comsolitbldragover">
          <td colspan="99" style="min-heigth:2em;background-color:grey">first line</td>
        </tr>
      </tbody>

      <tbody>
        <tr style="background-color:green" ng-repeat="item in (itemsInBacklog = (backlogItems|prioritizedItems:true))|orderBy:'backlog_position' track by item.id" comsolit-backlog-draggable="comsolitbldragged" comsolit-backlog-droppable="comsolitbldragover" draggable="true" data-id="{{item.id}}">
          <td ng-bind="$index + 1"></td>
          <td ng-bind="item.id"></td>
          <td ng-bind="item.summary"></td>
          <td ng-bind="item.points"></td>
          <td ng-bind="item.backlog_position"></td>
          <td style="width:10em"><button ng-click="removeItem(item.id)">remove</button></td>
        </tr>
      </tbody>
    </table>
  </div>

  <h4><?php echo plugin_lang_get( 'unprioritized_items' )?> ({{itemsNotInBacklog.length}})</h4>
  Filter <input type="text" ng-model="unprioritizedTextFilter" /> <button ng-click="unprioritizedTextFilter=''">reset</button>
  <div>
    <table style="width:100%">
      <tbody>
        <tr style="background-color:green" ng-repeat="item in (itemsNotInBacklog = (backlogItems|prioritizedItems:false))|filter:unprioritizedTextFilter track by item.id"  comsolit-backlog-draggable="comsolitbldragged" draggable="true" data-id="{{item.id}}">
          <td ng-bind="item.id"></td>
          <td ng-bind="item.summary"></td>
          <td ng-bind="item.points"></td>
          <td ng-bind="item.backlog_position"></td>
          <td style="width:10em"><button ng-click="moveItem(item.id)">to first position</button></td>
        </tr>
      </tbody>
    </table
  </div>
</div>
