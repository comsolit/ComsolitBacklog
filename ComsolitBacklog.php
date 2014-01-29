<?php
class ComsolitBacklogPlugin extends MantisPlugin {

    function register() {
        $this->name = 'ComsolitBacklog';        // Proper name of plugin
        $this->description = '';                // Short description of the plugin
        $this->page = '';                       // Default plugin page

        $this->version = '0.1';                 // Plugin version string
        $this->requires = array(                // Plugin dependencies, array of basename => version pairs
            'MantisCore' => '1.2.0',            // Should always depend on an appropriate version of MantisBT
        );

        $this->author = '';                     // Author/team name
        $this->contact = '';                    // Author/team e-mail address
        $this->url = '';                        // Support webpage
    }

    function schema() {
        return array(
            array( 'AddColumnSQL', array( 'mantis_bug_table', 'backlog_position F NOTNULL DEFAULT 0' ))
        );
    }

    public function hooks() {
        return array (
            'EVENT_MENU_MAIN_FRONT' => 'add_to_main_menu',
            'EVENT_LAYOUT_RESOURCES' => 'resources'
        );
    }

    /**
     * init - requires the api files
     */
    public function init() {
        require_once 'api/comsolitbacklog_print_api.php';
    }

    /**
     * adds backlog link to the main menu
     */
    function add_to_main_menu($p_event) {
        return array (
            '<a href="' . plugin_page( 'backlog' ) . '">' . plugin_lang_get( 'menuname' ) . '</a>'
        );
    }

    /**
     * loads js and css resources
     */
    public function resources($p_event) {
        $resources = '<script type="text/javascript" src="' . plugin_file( 'angular/angular.min.js' ) . '"></script> ';
        // '<link rel="stylesheet" type="text/css" href="' . plugin_file("backlog.css") . '" />';

        return $resources;
    }
}
