<?php namespace Comsolit\Backlog;

require_once __DIR__ . '/Backlog.php';

class BacklogUpdateController {

    private $backlog;

    public function __construct(Backlog $backlog) {
        $this->backlog = $backlog;
    }

    public function moveToTop($id) {
        $this->backlog->moveToTop($id);
        return 'moveToTop: ' . $id;
    }

    public function moveBelow($id, $targetId) {
        $this->backlog->moveBelow($id, $targetId);
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
