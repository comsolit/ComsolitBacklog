<?php

class ComsolitBacklogPlugin extends MantisPlugin {

    private static $javaScriptResources = array(
        'angular/angular.js',
        'comsolitbacklog.js'
	);

    public function register() {
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

    public function schema() {
        return array(
            array( 'AddColumnSQL', array( 'mantis_bug_table', 'backlog_position F NOTNULL DEFAULT 0' ))
        );
    }

    public function hooks() {
        return array (
            'EVENT_MENU_MAIN_FRONT' => 'add_to_main_menu',
            'EVENT_LAYOUT_RESOURCES' => 'printResourcesInHead'
        );
    }

    public function init() {
    }

    /**
     * adds backlog link to the main menu
     */
    public function add_to_main_menu($p_event) {
        return array (
            '<a href="' . plugin_page( 'backlog' ) . '">' . plugin_lang_get( 'menuname' ) . '</a>'
        );
    }

    /**
     * loads js and css resources
     */
    public function printResourcesInHead($p_event) {
        foreach(self::$javaScriptResources as $javaScriptResource) {
            echo '<script type="text/javascript" src="' , plugin_file($javaScriptResource) , '"></script>' , "\n";
        }
        echo '<link rel="stylesheet" type="text/css" href="' . plugin_file("comsolitbacklog.css") . '" />';
    }
}
