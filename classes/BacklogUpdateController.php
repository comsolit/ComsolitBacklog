<?php namespace Comsolit\Backlog;

class BacklogUpdateController {

    private $configuration;
    private $projectId;

    public function __construct($projectId, Configuration $configuration) {
        $this->projectId = $projectId;
        $this->configuration = $configuration;
    }

    public function moveToTop($id) {
        return 'moveToTop: ' . $id;
    }

    public function moveBelow($id, $targetId) {
        return 'moveBelow: ' . $id. ', '.$targetId;
    }

    public function remove($id) {
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

    public static function run(ActionData $actionData, $projectId, Configuration $configuration) {
        $ctrl = new self($projectId, $configuration);
        $result = $ctrl->callAction($actionData);
        // TODO check for rebalancing needed
        return $result;
    }
}
