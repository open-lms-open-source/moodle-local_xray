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
 * @return stdClass
 */
function local_xray_template_data($courseid, $userid){
    global $OUTPUT;
    // Get headline data.
    $headlinedata = \local_xray\dashboard\dashboard::get($courseid, $userid);

    if ($headlinedata instanceof \local_xray\dashboard\dashboard_data) {
        // Add info in the template.
        $data = new stdClass();

        // Styles.
        $linksnum = array('title' => get_string('link_gotoreport', 'local_xray'), 'style' => 'text-decoration: none; color: #777777; font-weight: bolder;');
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
        $statusclassactivity = local_xray\dashboard\dashboard_data::get_status_with_average($headlinedata->usersloggedinpreviousweek,
            $headlinedata->usersactivitytotal,
            $headlinedata->averageuserslastsevendays,
            $headlinedata->userstotalprevioussevendays,
            false,
            true);

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
        $data->gradebookaverageofweek = get_string("averageofweek_gradebook", 'local_xray', $headlinedata->averagegradeslastsevendayspreviousweek);

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
        $data->discussionlastweekwas = get_string("headline_lastweekwas_discussion", 'local_xray', $headlinedata->postslastsevendayspreviousweek);

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
                    'style' => "color:#777777;font-family:'Segoe UI',sans-serif,Arial,Helvetica,Lato;font-size:29px;line-height:24px;padding-right:5px;",
                    'valign' => 'top'));

                // Recommendation text.
                $data->recommendationslist .= html_writer::tag('td', $recommendation, array('align' => 'left',
                    'class' => 'MsoNormal',
                    'style' => "color:#777777;font-family:'Segoe UI',sans-serif,Arial,Helvetica,Lato;font-size:13px;line-height:24px;"));

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
 * Add the link in the profile user for subscriptions.
 */
function local_xray_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    // Validate user.
    if ((!has_capability("local/xray:globalsub_view", context_system::instance()) &&
            !local_xray_get_teacher_courses($user->id)) || !local_xray_email_enable()) {
        return false;
    }
    // Url for subscription.
    $subscriptionurl = new moodle_url("/local/xray/view.php",
        array("controller" => "globalsub"));

    $node = new core_user\output\myprofile\node('miscellaneous', 'local_xray', get_string('profilelink', 'local_xray'), null, $subscriptionurl);
    $tree->add_node($node);
    return true;
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