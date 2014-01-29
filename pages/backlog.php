<?php

use Comsolit\Backlog\Backlog;
use Comsolit\Backlog\Util\EmbeddedJsonScriptTag;

require_once ('core.php');
require_once ('compress_api.php');
require_once ('last_visited_api.php');
require_once __DIR__ . '/../classes/Backlog.php';
require_once __DIR__ . '/../classes/Util/EmbeddedJsonScriptTag.php';

auth_ensure_user_authenticated();

$t_current_user_id = auth_get_current_user_id();

// // Improve performance by caching category data in one pass
// category_get_all_rows ( helper_get_current_project () );
// compress_enable ();

// // don't index my view page
// html_robots_noindex ();

html_page_top1( plugin_lang_get( 'menuname' ) );

html_page_top2();

print_recently_visited();

$t_per_page = config_get( 'my_view_bug_count' );
$t_bug_count = null;
$t_page_count = null;

$t_project_id = helper_get_current_project();
?>

<div>
  <h1><?php echo(plugin_lang_get( 'menuname' ));?></h1>

  <?php 
    //ComsolitBacklogPrintAPI::print_bugs();
    $backlog = new Backlog();
    $items = $backlog->getBacklogItems();
    $jsonTag = EmbeddedJsonScriptTag::create('backlogItems', $items);
    echo $jsonTag;

    require __DIR__ . '/../templates/backlog.php';
  ?>
</div>

<?php

html_status_legend();
html_page_bottom ();