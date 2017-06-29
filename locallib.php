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
 * X-Ray local library utilities.
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

// Constants.

define('XRAYSUBSCRIBECOURSE', 0);
define('XRAYSUBSCRIBEON', 1);
define('XRAYSUBSCRIBEOFF', 2);

define('XRAYNEVER', 'never');
define('XRAYDAILY', 'daily');
define('XRAYWEEKLY', 'weekly');

/**
 * Get headline data for the template.
 * @param $courseid
 * @param $userid
 * @return stdClass
 */
function local_xray_template_data($courseid, $userid) {

    // Get headline data.
    $xrayreports = local_xray_reports();
    $headlinedata = \local_xray\dashboard\dashboard::get($courseid, $userid, $xrayreports);

    if ($headlinedata instanceof \local_xray\dashboard\dashboard_data) {
        // Add info in the template.
        $data = new stdClass();

        // Styles.
        $linksnum = array(
            'title' => get_string('link_gotoreport', 'local_xray'),
            'style' => 'text-decoration: none; color: #777777; font-weight: bolder;'
        );
        $gotoreport = array('title' => get_string('link_gotoreport', 'local_xray'));
        $reporticonpdf = array('width' => '37px');
        $reporticonarrowpdf = array('width' => '25px');
        $reportdata = array('style' => 'color:#777777;font-weight:bolder;font-size:20px;');

        // Add report date.
        $data->reportdate = $headlinedata->reportdate;

        // Risk.
        // Icon and link.
        $xrayriskicon = local_xray_get_email_icons('xray-risk');
        $data->riskiconpdf = html_writer::img($xrayriskicon, get_string('risk', 'local_xray'), $reporticonpdf);
        $data->riskicon = html_writer::img($xrayriskicon, get_string('risk', 'local_xray'));

        $riskurl = new moodle_url("/local/xray/view.php",
            array("controller" => "risk", "courseid" => $courseid, "action" => "view"));

        $data->risklink = html_writer::link($riskurl, get_string('risk', 'local_xray'), $gotoreport);

        $riskarrowurl = new moodle_url("/local/xray/view.php",
            array("controller" => "risk", "courseid" => $courseid, "header" => 1), "riskMeasures");

        // Calculate colour status.
        $statusclassrisk = local_xray\dashboard\dashboard_data::get_status_with_average($headlinedata->usersinrisk,
            $headlinedata->risktotal,
            $headlinedata->averagerisksevendaybefore,
            $headlinedata->maximumtotalrisksevendaybefore,
            true,
            true); // This arrow will be inverse to all.

        $xrayriskarrow = local_xray_get_email_icons($statusclassrisk[2]);
        $data->riskarrowpdf = html_writer::img($xrayriskarrow, $statusclassrisk[1], $reporticonarrowpdf);
        $riskarrow = html_writer::img($xrayriskarrow, $statusclassrisk[1]);
        $data->riskarrow = html_writer::link($riskarrowurl, $riskarrow, $gotoreport);

        // Number for risk.
        $a = new stdClass();
        $a->first = $headlinedata->usersinrisk;
        $a->second = $headlinedata->risktotal;
        $data->riskdatapdf = html_writer::span(get_string('headline_number_of', 'local_xray', $a), '', $reportdata);
        $data->riskdata = html_writer::link($riskarrowurl, get_string('headline_number_of', 'local_xray', $a),
            $linksnum);

        $data->studentsrisk = get_string('headline_studentatrisk', 'local_xray');

        // Number of students at risk in the last 7 days.
        $a = new stdClass();
        $a->previous = $headlinedata->averagerisksevendaybefore;
        $a->total = $headlinedata->maximumtotalrisksevendaybefore;
        $data->riskaverageweek = get_string("averageofweek_integer", 'local_xray', $a);

        // Activity.
        // Icon and link.
        $xrayactivityicon = local_xray_get_email_icons('xray-activity');
        $data->activityiconpdf = html_writer::img($xrayactivityicon, get_string('activityreport', 'local_xray'),
            $reporticonpdf);
        $data->activityicon = html_writer::img($xrayactivityicon, get_string('activityreport', 'local_xray'));

        $activityurl = new moodle_url("/local/xray/view.php",
            array("controller" => "activityreport", "courseid" => $courseid, "action" => "view"));

        $data->activitylink = html_writer::link($activityurl, get_string('activityreport', 'local_xray'), $gotoreport);

        $activityarrowurl = new moodle_url("/local/xray/view.php",
            array("controller" => "activityreport", "courseid" => $courseid, "header" => 1), "studentList");

        // Calculate colour status.
        $statusclassactivity = local_xray\dashboard\dashboard_data::get_status_with_average(
            $headlinedata->usersloggedinpreviousweek,
            $headlinedata->usersactivitytotal,
            $headlinedata->averageuserslastsevendays,
            $headlinedata->userstotalprevioussevendays,
            false,
            true
        );

        $xrayactivityarrow = local_xray_get_email_icons($statusclassactivity[2]);
        $data->activityarrowpdf = html_writer::img($xrayactivityarrow, $statusclassactivity[1], $reporticonarrowpdf);
        $activityarrow = html_writer::img($xrayactivityarrow, $statusclassactivity[1]);
        $data->activityarrow = html_writer::link($activityarrowurl, $activityarrow, $gotoreport);

        $a = new stdClass();
        $a->first = $headlinedata->usersloggedinpreviousweek;
        $a->second = $headlinedata->usersactivitytotal;
        $data->activitydatapdf = html_writer::span(get_string('headline_number_of', 'local_xray', $a), '', $reportdata);
        $data->activitydata = html_writer::link($activityarrowurl, get_string('headline_number_of', 'local_xray', $a),
            $linksnum);

        $data->activityloggedstudents = get_string('headline_loggedstudents', 'local_xray');

        // Number of students logged in in last 7 days.
        $a = new stdClass();
        $a->current = $headlinedata->averageuserslastsevendays;
        $a->total = $headlinedata->userstotalprevioussevendays;
        $data->activitylastweekwasof = get_string("headline_lastweekwasof_activity", 'local_xray', $a);

        // Gradebook.
        // Icon and link.
        $xraygradeicon = local_xray_get_email_icons('xray-grade');
        $data->gradebookiconpdf = html_writer::img($xraygradeicon, get_string('gradebookreport', 'local_xray'),
            $reporticonpdf);
        $data->gradebookicon = html_writer::img($xraygradeicon, get_string('gradebookreport', 'local_xray'));

        $gradebookurl = new moodle_url("/local/xray/view.php",
            array("controller" => "gradebookreport", "courseid" => $courseid, "action" => "view"));

        $data->gradebooklink = html_writer::link($gradebookurl, get_string('gradebookreport', 'local_xray'), $gotoreport);

        $gradebookarrowurl = new moodle_url("/local/xray/view.php",
            array("controller" => "gradebookreport", "courseid" => $courseid, "header" => 1), "courseGradeTable");

        // Calculate colour status.
        $statusclass = local_xray\dashboard\dashboard_data::get_status_simple($headlinedata->averagegradeslastsevendays,
            $headlinedata->averagegradeslastsevendayspreviousweek,
            true);

        $xraygradebookarrow = local_xray_get_email_icons($statusclass[2]);
        $data->gradebookarrowpdf = html_writer::img($xraygradebookarrow, $statusclass[1], $reporticonarrowpdf);
        $gradebookarrow = html_writer::img($xraygradebookarrow, $statusclass[1]);
        $data->gradebookarrow = html_writer::link($gradebookarrowurl, $gradebookarrow, $gotoreport);

        $data->gradebooknumberpdf = html_writer::span(get_string('headline_number_percentage', 'local_xray',
            $headlinedata->averagegradeslastsevendays), '', $reportdata);
        $data->gradebooknumber = html_writer::link($gradebookarrowurl, get_string('headline_number_percentage', 'local_xray',
            $headlinedata->averagegradeslastsevendays), $linksnum);
        $data->gradebookheadline = get_string('headline_average', 'local_xray');
        $data->gradebookaverageofweek = get_string(
            "averageofweek_gradebook",
            'local_xray',
            $headlinedata->averagegradeslastsevendayspreviousweek
        );

        // Discussion.
        // Icon and link.
        $xraydiscussionsicon = local_xray_get_email_icons('xray-discussions');
        $data->discussioniconpdf = html_writer::img($xraydiscussionsicon, get_string('discussionreport', 'local_xray'),
            $reporticonpdf);
        $data->discussionicon = html_writer::img($xraydiscussionsicon, get_string('discussionreport', 'local_xray'));

        $discussionurl = new moodle_url("/local/xray/view.php",
            array("controller" => "discussionreport", "courseid" => $courseid, "action" => "view"));

        $data->discussionlink = html_writer::link($discussionurl, get_string('discussionreport', 'local_xray'), $gotoreport);

        $discussionarrowurl = new moodle_url("/local/xray/view.php",
            array("controller" => "discussionreport", "courseid" => $courseid, "header" => 1), "discussionMetrics");

        // Calculate colour status.
        $statusclassdiscussion = local_xray\dashboard\dashboard_data::get_status_simple($headlinedata->postslastsevendays,
            $headlinedata->postslastsevendayspreviousweek,
            true);

        $xraydiscussionsarrow = local_xray_get_email_icons($statusclassdiscussion[2]);
        $data->discussionarrowpdf = html_writer::img($xraydiscussionsarrow, $statusclassdiscussion[1], $reporticonarrowpdf);
        $discussionarrow = html_writer::img($xraydiscussionsarrow, $statusclassdiscussion[1]);
        $data->discussionarrow = html_writer::link($discussionarrowurl, $discussionarrow, $gotoreport);
        $data->discussiondatapdf = html_writer::span($headlinedata->postslastsevendays, '', $reportdata);
        $data->discussiondata = html_writer::link($discussionarrowurl, $headlinedata->postslastsevendays, $linksnum);
        $data->discussionposts = get_string('headline_posts', 'local_xray');
        $data->discussionlastweekwas = get_string(
            "headline_lastweekwas_discussion",
            'local_xray',
            $headlinedata->postslastsevendayspreviousweek
        );

        // Recommended Actions.
        $data->recommendationslist = '';
        $data->recommendations = false;
        $data->recommendationspdf = $headlinedata->recommendations;
        if ($headlinedata->countrecommendations) {
            $data->recommendations = true;
            $recommendationnumber = 1;
            foreach ($headlinedata->recommendations as $recommendation) {
                // Add the recommendation.
                // Title.
                $data->recommendationstitle = get_string('recommendedactions' , 'local_xray');
                // Number.
                $data->recommendationslist .= html_writer::tag('td', $recommendationnumber, array('align' => 'left',
                    'class' => 'MsoNormal',
                    'style' => "color:#777777;font-family:'Segoe UI',sans-serif,Arial,Helvetica,Lato;".
                               "font-size:29px;line-height:24px;padding-right:5px;",
                    'valign' => 'top'));

                // Recommendation text.
                $data->recommendationslist .= html_writer::tag('td', $recommendation, array('align' => 'left',
                    'class' => 'MsoNormal',
                    'style' => "color:#777777;font-family:'Segoe UI',sans-serif,Arial,Helvetica,Lato;".
                               "font-size:13px;line-height:24px;"));

                $data->recommendationslist .= html_writer::end_tag('tr');
                $data->recommendationslist .= html_writer::tag('tr', '', array('style' => "height:16px;"));
                $recommendationnumber++;
            }
        }

        $result = $data;
    } else {
        $result = false;
    }

    return $result;
}

/**
 * Check capabilities to send emails.
 *
 * @param $courseid
 * @param $userid
 * @return bool
 * @throws coding_exception
 */
function local_xray_email_capability($courseid, $userid) {
    if (has_capability("local/xray:view", context_course::instance($courseid), $userid)) {
        return true;
    }
    return false;
}

/**
 * Is user teacher in any course
 * Very efficient and secure way of checking
 * @param int $userid
 * @return bool
 * @throws dml_exception A DML specific exception is thrown for any errors.
 */
function  local_xray_is_teacher($userid = 0) {
    global $USER, $DB;
    $params = [
        'contextlevel' => CONTEXT_COURSE,
        'capability'   => 'local/xray:teacherrecommendations_view',
        'permission'   => CAP_ALLOW,
        'userid'       => empty($userid) ? (int)$USER->id : $userid
    ];
    $sql = "
                SELECT ra.id
                  FROM {role_assignments}   ra
                  JOIN {role_capabilities}  rc ON ra.roleid = rc.roleid
                  JOIN {context}           ctx ON ra.contextid = ctx.id
                 WHERE rc.capability = :capability
                       AND
                       rc.permission = :permission
                       AND
                       ra.userid = :userid
                       AND
                       ctx.contextlevel = :contextlevel
                ";

    return $DB->record_exists_sql($sql, $params);
}

/**
 * Get all the courses where the user is enrolled as editingteacher or teacher.
 *
 * @param int $userid
 * @return array of objects, or empty array if no records were found.
 */
function local_xray_get_teacher_courses($userid) {

    global $DB;

    $sql = "SELECT DISTINCT c.id AS courseid
                FROM {user} u
                JOIN {user_enrolments} ue ON ue.userid = u.id
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {context} ct ON ct.id = ra.contextid AND ct.contextlevel = :contextcourse
                JOIN {course} c ON c.id = ct.instanceid AND e.courseid = c.id
                JOIN {role} r ON r.id = ra.roleid AND (r.shortname = 'editingteacher' OR r.shortname = 'teacher')
                WHERE u.id = :userid AND e.status = 0 AND u.suspended = 0 AND u.deleted = 0";

    $params = array('userid' => $userid, 'contextcourse' => CONTEXT_COURSE);
    return $DB->get_records_sql($sql, $params);
}

/**
 * Check if the user is enrolled as a teacher in a course.
 *
 * @param int $courseid
 * @param int $userid
 * @return bool.
 */
function local_xray_is_teacher_in_course ($courseid, $userid) {
    return has_capability(
        'local/xray:teacherrecommendations_view',
        context_course::instance($courseid),
        $userid,
        false
    );
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

    // Set document information.
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
    $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
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
    $activityicontable->data = local_xray_report_head_row(
        get_string('activityreport', 'local_xray'),
        $headlinedata->activityiconpdf
    );

    $gradebookicontable = new html_table();
    $gradebookicontable->data = local_xray_report_head_row(
        get_string('gradebookreport', 'local_xray'),
        $headlinedata->gradebookiconpdf
    );

    $discussionicontable = new html_table();
    $discussionicontable->data = local_xray_report_head_row(
        get_string('discussionreport', 'local_xray'),
        $headlinedata->discussioniconpdf
    );

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

    $table->data[] = array(
        html_writer::table($riskdatatable),
        html_writer::table($activitydatatable),
        html_writer::table($gradebooknumbertable),
        html_writer::table($discussiondatatable)
    );
    $table->data[] = array(
        $headlinedata->studentsrisk,
        $headlinedata->activityloggedstudents,
        $headlinedata->gradebookheadline,
        $headlinedata->discussionposts
    );
    $table->data[] = array(
        $headlinedata->riskaverageweek,
        $headlinedata->activitylastweekwasof,
        $headlinedata->gradebookaverageofweek,
        $headlinedata->discussionlastweekwas
    );

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

    // Output the HTML content.
    $pdf->writeHTML($html, true, 0, true, 0);
    // Reset pointer to the last page.
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