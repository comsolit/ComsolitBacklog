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