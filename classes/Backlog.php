<?php namespace Comsolit\Backlog;

require_once ('filter_api.php');

class Backlog {

    private static $backlogItemColumns = array(
        'id',
        'project_id',
        'reporter_id',
        'handler_id',
        'duplicate_id',
        'priority',
        'severity',
        'reproducibility',
        'status',
        'resolution',
        'projection',
        'category_id',
        'date_submitted',
        'last_updated',
        'eta',
        'os',
        'os_build',
        'platform',
        'version',
        'fixed_in_version',
        'target_version',
        'build',
        'view_state',
        'summary',
        'sponsorship_total',
        'sticky',
        'due_date',
        'profile_id',
        'description',
        'steps_to_reproduce',
        'additional_information',
        '_stats',
        'attachment_count',
        'bugnotes_count',
        'loading',
        'bug_text_id',
        'backlog_position',
    );

    public function getBacklogItems() {
        $rows = filter_get_bug_rows($f_page_number, $t_per_page, $t_page_count, $t_bug_count, $c_filter [$t_box_title] );
        $arrayRows = array();
        foreach($rows as $row) {
            $arrayRow = array();
            foreach(self::$backlogItemColumns as $name) {
                $arrayRow[$name] = $row->$name;
            }
            $arrayRows[] = $arrayRow;
        }
        return $arrayRows;
    }
}