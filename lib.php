<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Convenient wrappers and helper for using the X-Ray web service API.
 *
 * @package local_xray
 * @author Pablo Pagnone
 * @author German Vitale
 * @author Darko Miletic
 * @author David Castro <david.castro@blackboard.com>
 * @copyright Copyright (c) 2017 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Generate list of report links according to the current page
 * Result is returned as associative array ($reportname => $reporturl)
 *
 * @param  moodle_page $page
 * @param  context $context
 * @return array
 * @throws coding_exception
 */
function local_xray_navigationlinks(moodle_page $page, context $context) {
    static $reports = null;
    // Course selection check.
    if (!\local_xray\local\api\course_manager::is_course_selected($page->course->id)) {
        return $reports;
    }

    // Small caching to prevent double calculation call since we need the same information in both calls.
    // The forums in home page should not display the X-ray link.
    if (!is_null($reports) || $page->course->format == "singleactivity" || ($context->contextlevel < CONTEXT_COURSE) ||
        ($page->course->id == SITEID) || !has_capability('local/xray:view', $context)) {
        return $reports;
    }

    $reports     = [];
    $extraparams = [];

    $reportlist  = [
        'courseadmin' => [
            'risk'             => 'local/xray:risk_view',
            'activityreport'   => 'local/xray:activityreport_view',
            'gradebookreport'  => 'local/xray:gradebookreport_view',
            'discussionreport' => 'local/xray:discussionreport_view'
        ]
    ];

    if (in_array($page->pagetype, ['mod-forum-view', 'mod-hsuforum-view',
        'mod-forum-discuss', 'mod-hsuforum-discuss'])) {
        if (local_xray_reports()) {
            $extraparams['forumid'] = $page->cm->instance;
        } else {
            $extraparams['forum'] = $page->cm->instance;
        }
        $extraparams['cmid' ] = $context->instanceid;
        // Support for discussion of forum/hsforum.
        $d = $page->url->get_param('d');
        if (!empty($d)) {
            $extraparams['d'] = $d;
        }
        $reportlist = [
            'modulesettings' => [
                'discussionreportindividualforum' => 'local/xray:discussionreportindividualforum_view',
            ]
        ];
    }

    $baseurl = new moodle_url('/local/xray/view.php');
    foreach ($reportlist as $nodename => $reportsublist) {
        foreach ($reportsublist as $report => $capability) {
            if (has_capability($capability, $context)) {
                if (local_xray_reports()) {
                    $reports[$nodename][$report] = $baseurl->out(false, ['controller' => 'xrayreports',
                            'name'   => local_xray_name_conversion($report),
                            'courseid'   => $page->course->id,
                            'action'     => 'view'] + $extraparams);
                } else {
                    $reports[$nodename][$report] = $baseurl->out(false, ['controller' => $report,
                            'courseid'   => $page->course->id,
                            'action'     => 'view'] + $extraparams);
                }
            }
        }
    }

    return $reports;
}

/**
 * Extend navigations block.
 *
 * @param  settings_navigation $settings
 * @param  context $context
 * @return void
 * @throws coding_exception
 */
function local_xray_extends_settings_navigation(settings_navigation $settings, context $context) {
    global $PAGE;

    $reports = local_xray_navigationlinks($PAGE, $context);
    if (empty($reports)) {
        return;
    }

    foreach ($reports as $nodename => $reportsublist) {
        $coursenode = $settings->get($nodename);
        if ($coursenode === false) {
            continue;
        }
        $extranavigation = $coursenode->add(get_string('navigation_xray', 'local_xray'));
        foreach ($reportsublist as $reportstring => $url) {
            $extranavigation->add(get_string($reportstring, 'local_xray'), $url);
        }
        $extranavigation = null;
        $coursenode = null;
    }
}

/**
 * This is the version of the JS that should be used up to Moodle 2.8
 * New one will be required for Moodle 2.9+
 *
 * @param  global_navigation $nav
 * @return void
 */
function local_xray_extends_navigation(global_navigation $nav) {
    global $PAGE;
    ($nav); // Just to remove unused param warning.

    static $search = [
        'topics' => '#region-main',
        'weeks' => '#region-main',
        'flexpage' => '#page-content',
        'folderview' => '#region-main',
        'onetopic' => '#region-main',
        'singleactivity' => '.notexist', // Not sure what to do here?
        'social' => '#region-main',
        'tabbedweek' => '#region-main',
        'topcoll' => '#region-main',
    ];

    $courseformat = $PAGE->course->format;
    if (!isset($search[$courseformat])) {
        $search[$courseformat] = '#region-main';
    }

    $reportview = ($PAGE->pagetype == 'local-xray-view');
    $courseview = false;
    if ($PAGE->has_set_url()) {
        $courseview = $PAGE->url->compare(new moodle_url('/course/view.php', ['id' => $PAGE->course->id]), URL_MATCH_BASE);
        if ($courseview) {
            $buieditid = (int)optional_param('bui_editid'  , 0, PARAM_INT);
            $buihideid = (int)optional_param('bui_hideid'  , 0, PARAM_INT);
            $buidelid  = (int)optional_param('bui_deleteid', 0, PARAM_INT);
            if (!empty($buidelid) || !empty($buihideid) || !empty($buieditid)) {
                $courseview = false;
            }
        }
    }
    if (!$reportview && !$courseview) {
        return;
    }

    $menu = '';
    if (!$reportview) {
        $reportcontroller = optional_param('controller', '', PARAM_ALPHA);
        $reports = local_xray_navigationlinks($PAGE, $PAGE->context);
        /* @var local_xray_renderer $renderer */
        $renderer = $PAGE->get_renderer('local_xray');
        $menu = $renderer->print_course_menu($reportcontroller, $reports);
    }

    if (!empty($menu)) {
        $menuappend = 0;
        // Easy way to force include on every page (provided that navigation block is present).
        $PAGE->requires->yui_module(['moodle-local_xray-custmenu'],
            'M.local_xray.custmenu.init',
            [[
                'menusearch' => $search[$courseformat],
                'menuappend' => $menuappend,
                'items'      => $menu,
            ]],
            null,
            false
        );
    }

}

/**
 * Moodle 2.9+ compliant method.
 *
 * @link  https://tracker.moodle.org/browse/MDL-49643 MDL-49643
 * @param settings_navigation $settings
 * @param context $context
 */
function local_xray_extend_settings_navigation(settings_navigation $settings, context $context) {
    local_xray_extends_settings_navigation($settings, $context);
}

/**
 * Moodle 2.9+ compliant method.
 *
 * @link  https://tracker.moodle.org/browse/MDL-49643 MDL-49643
 * @param global_navigation $nav
 */
function local_xray_extend_navigation(global_navigation $nav) {
    local_xray_extends_navigation($nav);
}

/**
 * Check if the user is enrolled as a teacher in a course.
 *
 * @param int $courseid
 * @param int $userid
 * @return bool.
 */
function local_xray_is_teacher_in_course ($courseid, $userid) {
    global $CFG;
    require_once($CFG->dirroot.'/local/xray/locallib.php');
    $usercourses = local_xray_get_teacher_courses($userid);
    if (array_key_exists($courseid, $usercourses)) {
        return true;
    }
    return false;
}

/**
 * Check if the Email Report is enabled in control panel.
 *
 * @return bool
 */
function local_xray_email_enable() {
    $result = false;
    $cfgxray = get_config('local_xray');
    if (isset($cfgxray->emailreport) && $cfgxray->emailreport == 1) {
        $result = true;
    }
    return $result;
}

/**
 * Check if the user is subscribed.
 *
 * @param int $courseid
 * @param int $userid
 * @return bool
 */
function local_xray_is_subscribed($userid, $courseid) {
    global $DB;
    return $DB->record_exists('local_xray_subscribe', array('courseid' => $courseid, 'userid' => $userid));
}

/**
 * Get support user.
 *
 * @return stdClass user record.
 */
function local_xray_get_support_user() {
    return core_user::get_support_user();
}

/**
 * Check if the email should be sent today.
 *
 * @return bool.
 */
function local_xray_send_email_today() {
    global $CFG;
    require_once($CFG->dirroot.'/local/xray/locallib.php');
    // Check frequency.
    $frequency = get_config('local_xray', 'emailfrequency');
    // If the frequency is never, the email should not be sent.
    if ($frequency == XRAYNEVER) {
        return false;
    }
    // If the frequency is daily, the email should be sent.
    if ($frequency == XRAYDAILY) {
        return true;
    }
    // By default, the email should be sent weekly.
    // Days of the week.
    $day = array(0 => 'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday',
        4 => 'thursday', 5 => 'friday', 6 => 'saturday');
    // Get current day of week.
    $currentdayofweek = date("w");
    // By default, Sunday is the day to run.
    $daytorun = 0;
    // Check if the Control Panel variable weeklyday is set.
    $cfgxray = get_config('local_xray');
    if (isset($cfgxray->weeklyday) && isset($day[$cfgxray->weeklyday])) {
        $daytorun = $cfgxray->weeklyday;
    }
    if ($currentdayofweek == $daytorun) {
        return true;
    }
    return false;
}

/**
 * Get the url for the icons email.
 *
 * @return string.
 */
function local_xray_get_email_icons($imagename) {
    // Add format.
    $imagename = $imagename.'.gif';
    // Default value.
    $baseurl = 'https://cdn.xrayanalytics.net';
    $cfgxray = get_config('local_xray');
    if (isset($cfgxray->iconsurl)) {
        $baseurl = $cfgxray->iconsurl;
    }
    return sprintf('%s/pix/1/%s', $baseurl, $imagename);
}

/**
 * Check if the course has the Single Activity format.
 *
 * @return bool.
 */
function local_xray_single_activity_course($courseid) {
    global $DB;
    return $DB->record_exists('course', array('id' => $courseid, 'format' => 'singleactivity'));
}

/**
 * Create PDF.
 *
 * @param object $headlinedata
 * @param string $subject
 * @return object $pdf
 */
function local_xray_create_pdf($headlinedata, $subject) {
    global $CFG;

    require_once("$CFG->libdir/pdflib.php");

    $pdf = new pdf();

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor(get_string('pluginname', 'local_xray'));
    $pdf->SetTitle($subject);
    $pdf->SetSubject($subject);
    $pdf->SetKeywords('MOODLE, XRAY');
    // Set header and footer fonts.
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    // Set default monospaced font.
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    // Set margins.
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    // Set auto page breaks.
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    // Set image scale factor.
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    // Set font.
    $pdf->SetFont('helvetica', '', 9);
    // Add a page.
    $pdf->AddPage();
    // Create some HTML content.

    $title = new html_table();
    $title->attributes = array('style' => 'padding-bottom: 20px;font-weight:bolder;font-size:22px;');
    $title->data[] = array($subject);
    $html = html_writer::table($title);

    $riskicontable = new html_table();
    $riskicontable->data = local_xray_report_head_row(get_string('risk', 'local_xray'), $headlinedata->riskiconpdf);

    $activityicontable = new html_table();
    $activityicontable->data = local_xray_report_head_row(get_string('activityreport', 'local_xray'), $headlinedata->activityiconpdf);

    $gradebookicontable = new html_table();
    $gradebookicontable->data = local_xray_report_head_row(get_string('gradebookreport', 'local_xray'), $headlinedata->gradebookiconpdf);

    $discussionicontable = new html_table();
    $discussionicontable->data = local_xray_report_head_row(get_string('discussionreport', 'local_xray'), $headlinedata->discussioniconpdf);

    $table = new html_table();
    $table->data[] = array(html_writer::table($riskicontable),
        html_writer::table($activityicontable),
        html_writer::table($gradebookicontable),
        html_writer::table($discussionicontable));

    $riskdatatable = new html_table();
    $riskdatatable->data[] = array($headlinedata->riskdatapdf, $headlinedata->riskarrowpdf);

    $activitydatatable = new html_table();
    $activitydatatable->data[] = array($headlinedata->activitydatapdf, $headlinedata->activityarrowpdf);

    $gradebooknumbertable = new html_table();
    $gradebooknumbertable->data[] = array($headlinedata->gradebooknumberpdf, $headlinedata->gradebookarrowpdf);

    $discussiondatatable = new html_table();
    $discussiondatatable->data[] = array($headlinedata->discussiondatapdf, $headlinedata->discussionarrowpdf);

    $table->data[] = array(html_writer::table($riskdatatable), html_writer::table($activitydatatable), html_writer::table($gradebooknumbertable), html_writer::table($discussiondatatable));
    $table->data[] = array($headlinedata->studentsrisk, $headlinedata->activityloggedstudents, $headlinedata->gradebookheadline, $headlinedata->discussionposts);
    $table->data[] = array($headlinedata->riskaverageweek, $headlinedata->activitylastweekwasof, $headlinedata->gradebookaverageofweek, $headlinedata->discussionlastweekwas);

    $html .= html_writer::table($table);

    // Add recommendations.
    if ($headlinedata->recommendations) {
        $recommendationtitle = new html_table();
        $recommendationtitle->attributes = array('style' => 'padding: 10px 10px 0 10px;font-weight:bolder;');
        $recommendationtitle->data[] = array(get_string('recommendedactions' , 'local_xray'));
        $html .= html_writer::table($recommendationtitle);

        $recommendationtable = new html_table();
        $recommendationtable->attributes = array('style' => 'padding: 10px 10px 0 10px;');
        $recommendationnumber = 1;
        foreach ($headlinedata->recommendationspdf as $recommendation) {
            $recommendationtable->data[] = local_xray_add_recommendation_pdf($recommendationnumber, $recommendation);
            $recommendationnumber++;
        }
        $html .= html_writer::table($recommendationtable);
    }

    // Add date of email.
    $xraydate = new html_table();
    $xraydate->attributes = array('style' => 'padding: 20px 10px 0 10px;font-weight:bolder;text-align:center;');


    $date = new DateTime($headlinedata->reportdate);
    $xrayemaildate = userdate($date->getTimestamp(), get_string('strftimedayshort', 'langconfig'), 'UTC');
    $xraydate->data[] = array(get_string('xrayemaildate', 'local_xray', $xrayemaildate));
    $html .= html_writer::table($xraydate);

    // Output the HTML content
    $pdf->writeHTML($html, true, 0, true, 0);
    // Reset pointer to the last page
    $pdf->lastPage();

    return $pdf;
}

/**
 * Add a recommendation in the PDF.
 *
 * @param int $recommendationnumber
 * @param string $recommendation
 * @return array of cells.
 */
function local_xray_add_recommendation_pdf($recommendationnumber, $recommendation) {

    $cellicon = new html_table_cell();
    $cellicon->text = $recommendationnumber;
    $cellicon->style = 'width:36px;color:#777777;font-weight:bolder;';
    $cellname = new html_table_cell();
    $cellname->text = $recommendation;
    $cellname->style = 'width:80%;';

    return array($cellicon, $cellname);
}

/**
 * Add report icon with the report title.
 *
 * @param string $reporttitle
 * @param string $reporticon
 * @return array of rows.
 */
function local_xray_report_head_row($reporttitle, $reporticon) {

    $cell1 = new html_table_cell();
    $cell1->text = $reporticon;
    $cell1->rowspan = 3;
    $cell1->style = 'width:42px;padding: 0;';
    $cell2 = new html_table_cell();
    $row = new html_table_row();
    $row->cells = array($cell1, $cell2);

    $cell2 = new html_table_cell();
    $cell2->text = $reporttitle;
    $row2 = new html_table_row();
    $row2->cells = array($cell2);

    $cell2 = new html_table_cell();
    $row3 = new html_table_row();
    $row3->cells = array($cell2);

    return array($row, $row2, $row3);
}

/**
 * Check is the shiny course reports are available.
 *
 * @return bool.
 */
function local_xray_reports() {
    global $CFG;
    if (isset($CFG->local_xray_shiny_reports) && $CFG->local_xray_shiny_reports) {
        return true;
    }
    return false;
}

/**
 * Change the report names to adjust to new X-Ray names.
 *
 * @return string.
 */
function local_xray_name_conversion($reportname, $inverse = false) {
    if ($inverse) {
        $add = 'report';
        switch ($reportname) {
            case 'activity':
            case 'discussion':
            case 'gradebook':
                return $reportname.$add;
                break;
            case 'activityindividual':
            case 'discussionindividual':
            case 'discussionindividualforum':
                return str_replace('individual', 'reportindividual', $reportname);
                break;
            default:
                return $reportname;
        }
    }
    return str_replace('report', '', $reportname);
}