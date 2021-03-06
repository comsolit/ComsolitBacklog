<?php
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

use Comsolit\Backlog\ActionData;
use Comsolit\Backlog\BacklogUpdateController;
use Comsolit\Backlog\Configuration;
use Comsolit\Backlog\Backlog;
require_once 'core.php';
require_once __DIR__ . '/../classes/ActionData.php';
require_once __DIR__ . '/../classes/BacklogUpdateController.php';
require_once __DIR__ . '/../classes/Configuration.php';

auth_ensure_user_authenticated();
// $t_current_user_id = auth_get_current_user_id();

//$t_user = auth_get_current_user_id();
//if(!access_has_bug_level(config_get('update_bug_threshold'), $f_bug_id)) access_denied();

$comsolitBacklogActionIO = getComsolitBacklogActionIO();
header('Content-Type: application/json', true, $comsolitBacklogActionIO['httpCode']);
echo json_encode($comsolitBacklogActionIO['data']);

function getComsolitBacklogActionIO() {
    try {
        return array(
            'httpCode' => 200,
            'data' => comsolitBacklogActionDo()
        );
    } catch (Exception $e) {
        return array(
            'httpCode' => 500,
            'data' => comsolitBacklogExceptionToArray($e)
        );
    }
}

function comsolitBacklogActionDo() {
    if($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('only POST requests allowed');

    $rawPostData = file_get_contents('php://input');
    $actionData = new ActionData($rawPostData);
    $configuration = Configuration::fromGlobalVariables();
    $projectId = 1; // TODO!!!!!!!!!!!!

    return BacklogUpdateController::run($actionData, Backlog::fromGlobalData());
}

function comsolitBacklogExceptionToArray(Exception $e) {
    return array(
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'traceString' => $e->getTraceAsString(),
        'trace' => $e->getTrace(),
        'previous' => method_exists($e, 'getPrevious') && $e->getPrevious()
        ? comsolitBacklogExceptionToArray($e->getPrevious())
        : null
    );
}