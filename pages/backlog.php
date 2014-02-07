<?php

require_once ('core.php');
require_once ('compress_api.php');
require_once ('last_visited_api.php');

auth_ensure_user_authenticated();

if((ALL_PROJECTS == helper_get_current_project())) {
    print_header_redirect( 'login_select_proj_page.php?ref=' . urlencode('plugin.php?page=' . gpc_get_string('page')));
}

html_robots_noindex ();

html_page_top1(plugin_lang_get('menuname'));
html_page_top2();
print_recently_visited();

require __DIR__ . '/../templates/backlog.phtml';

html_status_legend();
html_page_bottom ();