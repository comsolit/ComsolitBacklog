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

    private $projectId;
    private $userId;
    private $configuration;

    public function __construct($projectId, $userId, Configuration $configuration) {
        $this->projectId = $projectId;
        $this->userId = $userId;
        $this->configuration = $configuration;
    }

    public function getProjectId() {
        return $this->projectId;
    }

    public function getUserId() {
        return $this->userId;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration() {
        return $this->configuration;
    }

    private function getBugRows() {
        $f_page_number = 0;
        $t_per_page = -1;
        $t_page_count = 0;
        $t_bug_count = 0;
        $c_filter = array(
            '_view_type' => 'advanced',
            FILTER_PROPERTY_CATEGORY => $this->configuration->getRequired('categories'),
            FILTER_PROPERTY_STATUS_ID => array(
                $this->configuration->getRequired('prioritizedStatus'),
                $this->configuration->getRequired('unprioritizedStatus')
            )
        );

        return filter_get_bug_rows($f_page_number, $t_per_page, $t_page_count, $t_bug_count, $c_filter, $this->projectId, $this->userId);
    }

    public function getBacklogItems() {
        $arrayRows = array();
        foreach($this->getBugRows() as $row) {
            $arrayRow = array();
            foreach(self::$backlogItemColumns as $name) {
                $arrayRow[$name] = $row->$name;
            }
            $arrayRow['priorityString'] = get_enum_element('priority', $row->priority, $this->userId, $this->project_id );
            $arrayRows[] = $arrayRow;
        }
        return $arrayRows;
    }

    public function moveToTop($id) {
        $subQuery = $this->subselectIfPositions(
            'min(positions.backlog_position)/2',
            'pow(2, 16)'
        );
        $query = self::updateBug($subQuery);
        db_query_bound($query, array($this->configuration->getRequired('prioritizedStatus'), $id));
    }

    public function moveBelow($id, $targetId) {
        $subQuery = $this->subselectIfPositions(
            '0.5 * (min(positions.backlog_position) + ' . self::subselectTargetPosition($targetId) . ')',
            'pow(2, 16) + ' . self::subselectTargetPosition($targetId),
            self::subselectTargetPosition($targetId)
        );
        $query = self::updateBug($subQuery);
        db_query_bound($query, array($this->configuration->getRequired('prioritizedStatus'), $id));
    }

    public function remove($id) {
        $query = self::updateBug(0);
        db_query_bound($query, array($this->configuration->getRequired('unprioritizedStatus'), $id));
    }

    /**
     * A rebalancing operation is needed if the minimum backlog_position distance between two items is below a certain limit.
     *
     * @param number $minimumDistance
     * @return boolean
     */
    public function isRebalancingNeeded($minimumDistance = 1) {
        //WHERE a.status = 40 AND a.backlog_position > 0 and a.project_id = 1
        $query = 'SELECT COUNT(*) > 0 as rebalancingNeeded FROM ' . db_get_table('mantis_bug_table') . ' AS a '
            .' JOIN ' . db_get_table('mantis_bug_table') . ' as b ON '
                .'a.status = b.status AND a.project_id = b.project_id AND a.id < b.id AND b.backlog_position > 0 '
                .' AND abs(a.backlog_position - b.backlog_position) < ' . db_param()
            .' JOIN mantis_category_table as acat on a.category_id = acat.id'
                .' AND acat.name IN (' . self::implodeQuoted($this->configuration->getRequired('categories')) . ')'
            .' JOIN mantis_category_table as bcat on b.category_id = bcat.id'
                .' AND bcat.name IN (' . self::implodeQuoted($this->configuration->getRequired('categories')) . ')'
            .' WHERE a.status = '.db_param()
            .' AND a.project_id = '.db_param()
            .' AND a.backlog_position > 0';

        return (bool)db_result(db_query_bound($query, array(
            $minimumDistance,
            $this->configuration->getRequired('prioritizedStatus'),
            $this->projectId
        )));
    }

    public function rebalance() {
        $query = 'UPDATE mantis_bug_table SET backlog_position = '
            .'pow(2, 16) * (SELECT cnt FROM '
                .'(SELECT id, '
                    .'(SELECT count(*) FROM mantis_bug_table as b WHERE b.backlog_position <= a.backlog_position AND ' . $this->whereConditions('b'). ') '
                .'as cnt FROM mantis_bug_table as a WHERE ' . $this->whereConditions('a'). ') '
            .'as c WHERE c.id = mantis_bug_table.id) WHERE ' . $this->whereConditions('mantis_bug_table');
        db_query($query);
    }

    /** Builds part of an SQL WHERE condition to constrain to the prioritized items of the current project
     *
     * @param unknown $table prefix for all columns used in the conditions
     * @return string
     */
    private function whereConditions($table) {
        return ' '.
            $table . '.status = ' . (int)$this->configuration->getRequired('prioritizedStatus')
            . ' AND ' . $table . '.project_id = ' . (int)$this->projectId
            . ' AND ' . $table . '.backlog_position > 0 '
            . ' AND EXISTS (SELECT * FROM mantis_category_table WHERE mantis_category_table.id = '
                . $table . '.category_id AND mantis_category_table.name IN (' . self::implodeQuoted($this->configuration->getRequired('categories')) . ')) ';
    }
    /**
     * Build an instance of self from global Variables and global functions
     *
     * @return \Comsolit\Backlog\Backlog
     */
    public static function fromGlobalData() {
        return new Backlog(helper_get_current_project(), auth_get_current_user_id(), Configuration::fromGlobalVariables());
    }

    /**
     * Build Subquery named 'positions' to select all backlog_position values of the current backlog greater than $minPos
     *
     * @param number $minPos Can also be a subquery, default to zero
     * @return string
     */
    private function subselectPositions($minPos = 0) {
        return '(SELECT backlog_position FROM mantis_bug_table JOIN mantis_category_table on category_id = mantis_category_table.id '
            .'WHERE mantis_category_table.name IN (' . self::implodeQuoted($this->configuration->getRequired('categories')) . ')'
            .' AND  mantis_bug_table.status = ' . (int)$this->configuration->getRequired('prioritizedStatus')
            .' AND mantis_bug_table.project_id = ' . (int)$this->projectId
            .' AND backlog_position > ' . $minPos . ')'
            .' as positions';
    }

    private function subselectIfPositions($then, $else, $minPos = 0) {
        // then and else are 'inverse' because the function contract is about whether there are backlog_position rows
        return '(SELECT * FROM (SELECT IF(min(positions.backlog_position) is null, '
            .$else .', '
            .$then .') FROM '
            .$this->subselectPositions($minPos) . ') as newvalue)';
    }

    /**
     * Build a subselect string to select the backlog_position of the target item
     *
     * @param int $targetId
     * @return string
     */
    private static function subselectTargetPosition($targetId) {
        return '(SELECT backlog_position FROM ' . db_get_table('mantis_bug_table') . ' WHERE id = '.(int)$targetId.')';
    }

    /**
     * Build an update query string to move a backlog item
     *
     * @param string $pos a compley subquery string to calculate the new position
     * @return string
     */
    private static function updateBug($pos) {
        return 'UPDATE ' . db_get_table('mantis_bug_table')
            . ' SET status = ' .db_param()
            . ' , backlog_position = ' . $pos
            . ' WHERE id = ' .db_param();
    }

    private static function implodeQuoted(array $values) {
        return implode(',', array_map(function($x){
            return '"'.$x.'"';
        }, $values));
    }
}