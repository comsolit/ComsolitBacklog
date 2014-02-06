<?php

use Comsolit\Backlog\Backlog;
use Comsolit\Backlog\Util\EmbeddedJsonScriptTag;
use Comsolit\Backlog\Configuration;

require_once ('core.php');
require_once ('compress_api.php');
require_once ('last_visited_api.php');
require_once __DIR__ . '/../classes/Backlog.php';
require_once __DIR__ . '/../classes/Configuration.php';
require_once __DIR__ . '/../classes/Util/EmbeddedJsonScriptTag.php';

auth_ensure_user_authenticated();

if((ALL_PROJECTS == helper_get_current_project())) {
    print_header_redirect( 'login_select_proj_page.php?ref=' . urlencode('plugin.php?page=' . gpc_get_string('page')));
}

html_robots_noindex ();

html_page_top1(plugin_lang_get('menuname'));
html_page_top2();
print_recently_visited();
?>

<div>
  <h1><?php echo plugin_lang_get('menuname');?></h1>

  <?php 
    $backlog = new Backlog(helper_get_current_project(), auth_get_current_user_id(), Configuration::fromGlobalVariables());
    echo EmbeddedJsonScriptTag::create('backlogItems', $backlog->getBacklogItems());
    require __DIR__ . '/../templates/backlog.php';
  ?>
</div>

<?php

html_status_legend();
html_page_bottom ();