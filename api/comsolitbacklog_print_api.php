<?php

require_once ('core.php');
require_once ('current_user_api.php');
require_once ('bug_api.php');
require_once ('string_api.php');
require_once ('date_api.php');
require_once ('icon_api.php');
require_once ('helper_api.php');
require_once ('plugin_api.php');
require_once ('filter_api.php');

/**
 * Provides functions as API for printing backlog items.
 */
class ComsolitBacklogPrintAPI {


    public static function print_bugs() {

        $t_icon_path = config_get ( 'icon_path' );

        $rows = filter_get_bug_rows ( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, $c_filter [$t_box_title] );
        // Improve performance by caching category data in one pass
        if (helper_get_current_project () == 0) {
            $t_categories = array ();
            foreach ( $rows as $t_row ) {
                $t_categories [] = $t_row->category_id;

            }

            category_cache_array_rows ( array_unique ( $t_categories ) );
        }

        // $t_filter = array_merge( $c_filter[$t_box_title], $t_filter );

        $box_title = plugin_lang_get ( 'issues' );

        echo ('<table class="width100" cellspacing="1"><tr><td class="form-title" colspan="2">');

        print_link ( html_entity_decode ( config_get ( 'bug_count_hyperlink_prefix' ) ) . '&' . $url_link_parameters [$t_box_title], $box_title, false, 'subtle' );
        echo '&#160;';
        print_bracket_link ( html_entity_decode ( config_get ( 'bug_count_hyperlink_prefix' ) ) . '&' . $url_link_parameters [$t_box_title], '^', true, 'subtle' );

        if (count ( $rows ) > 0) {
            $v_start = $t_filter [FILTER_PROPERTY_ISSUES_PER_PAGE] * ($f_page_number - 1) + 1;
            $v_end = $v_start + count ( $rows ) - 1;
        } else {
            $v_start = 0;
            $v_end = 0;
        }
        echo "($v_start - $v_end / $t_bug_count)";
        echo ('</td></tr>');

        // -- Loop over bug rows and create $v_* variables --
        $t_count = count ( $rows );
        for($i = 0; $i < $t_count; $i ++) {
            $t_bug = $rows [$i];

            $t_summary = string_display_line_links ( $t_bug->summary );
            $t_last_updated = date ( config_get ( 'normal_date_format' ), $t_bug->last_updated );

            // choose color based on status
            $status_color = get_status_color ( $t_bug->status, auth_get_current_user_id (), $t_bug->project_id );

            // Check for attachments
            $t_attachment_count = 0;
            // TODO: factor in the allow_view_own_attachments configuration option
            // instead of just using a global check.
            if ((file_can_view_bug_attachments ( $t_bug->id, null ))) {
                $t_attachment_count = file_bug_attachment_count ( $t_bug->id );
            }

            // grab the project name
            $project_name = project_get_field ( $t_bug->project_id, 'name' );

            echo ('<tr bgcolor=' . $status_color . '>');
            // -- Bug ID and details link + Pencil shortcut --
            echo ('<td class="center" valign="top" width="0" nowrap="nowrap"><span class="small">');
            print_bug_link ( $t_bug->id );

            echo '<br />';

            if (! bug_is_readonly ( $t_bug->id ) && access_has_bug_level ( $t_update_bug_threshold, $t_bug->id )) {
                echo '<a href="' . string_get_bug_update_url ( $t_bug->id ) . '"><img border="0" src="' . $t_icon_path . 'update.png' . '" alt="' . lang_get ( 'update_bug_button' ) . '" /></a>';
            }

            if (ON == config_get ( 'show_priority_text' )) {
                print_formatted_priority_string ( $t_bug );
            } else {
                print_status_icon ( $t_bug->priority );
            }

            if ($t_attachment_count > 0) {
                $t_href = string_get_bug_view_url ( $t_bug->id ) . '#attachments';
                $t_href_title = sprintf ( lang_get ( 'view_attachments_for_issue' ), $t_attachment_count, $t_bug->id );
                $t_alt_text = $t_attachment_count . lang_get ( 'word_separator' ) . lang_get ( 'attachments' );
                echo "<a href=\"$t_href\" title=\"$t_href_title\"><img src=\"${t_icon_path}attachment.png\" alt=\"$t_alt_text\" title=\"$t_alt_text\" /></a>";
            }

            if (VS_PRIVATE == $t_bug->view_state) {
                echo '<img src="' . $t_icon_path . 'protected.gif" width="8" height="15" alt="' . lang_get ( 'private' ) . '" />';
            }
            // -- Summary --
            echo ('</span></td><td class="left" valign="top" width="100%"><span class="small">');

            if (ON == config_get ( 'show_bug_project_links' ) && helper_get_current_project () != $t_bug->project_id) {
                echo '[', string_display_line ( project_get_name ( $t_bug->project_id ) ), '] ';
            }
            echo ($t_summary . '<br />');

            // type project name if viewing 'all projects' or bug is in subproject
            echo string_display_line ( category_full_name ( $t_bug->category_id, true, $t_bug->project_id ) );

            if ($t_bug->last_updated > strtotime ( '-' . $t_filter [FILTER_PROPERTY_HIGHLIGHT_CHANGED] . ' hours' )) {
                echo ' - <b>' . $t_last_updated . '</b>';
            } else {
                echo ' - ' . $t_last_updated;
            }

            echo ('</span></td></tr>');

            // -- end of Repeating bug row --
        }

        echo ('</table>');

        // Free the memory allocated for the rows in this box since it is not longer needed.
        unset ( $rows );
    }
}