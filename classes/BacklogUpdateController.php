<?php namespace Comsolit\Backlog;
// Copyright 2014 comsolit AG
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

require_once __DIR__ . '/Backlog.php';

class BacklogUpdateController {

    private $backlog;

    public function __construct(Backlog $backlog) {
        $this->backlog = $backlog;
    }

    public function moveToTop($id) {
        $this->backlog->moveToTop($id);
        if($this->backlog->isRebalancingNeeded()) $this->backlog->rebalance();
        return 'moveToTop: ' . $id;
    }

    public function moveBelow($id, $targetId) {
        $this->backlog->moveBelow($id, $targetId);
        if($this->backlog->isRebalancingNeeded()) $this->backlog->rebalance();
        return 'moveBelow: ' . $id. ', '.$targetId;
    }

    public function remove($id) {
        $this->backlog->remove($id);
        return 'remove: ' . $id;
    }

    private function callAction(ActionData $actionData) {
        switch ($actionData->action) {
            case 'move':
                if($actionData->dropId) return $this->moveBelow($actionData->dragId, $actionData->dropId);
                else return $this->moveToTop($actionData->dragId);
                break;
            case 'remove':
                return $this->remove($actionData->id);
                break;
            default:
                throw new \Exception('Invalid action: ' . $actionData->action);
        }
    }

    public static function run(ActionData $actionData, Backlog $backlog) {
        $ctrl = new self($backlog);
        $result = $ctrl->callAction($actionData);
        // TODO check for rebalancing needed
        return $result;
    }
}
