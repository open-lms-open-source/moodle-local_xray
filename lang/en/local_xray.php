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
 * Local xray lang file
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
$string['navigation_xray'] = 'X-Ray'; 
$string['navitationcourse_xray'] = 'X-Ray';
$string['pluginname'] = 'X-Ray';
$string['reports'] = 'Reports';

/* Capabilities */
$string['xray:activityreportindividual_view'] = 'View Activity Report Individual';
$string['xray:activityreport_view'] = 'View Activity Report';
$string['xray:discussionreport_view'] = 'View Discussion Report';
$string['xray:discussionreportindividualforum_view'] = 'View Discussion Report Individual Forum';
$string['xray:discussionendogenicplagiarism_view'] = 'View Discussion Endogenic Plagiarism';
$string['xray:discussiongrading_view'] = 'View Discussion Grading';
$string['xray:gradebookreport_view'] = 'View Gradebook Report';
$string['xray:risk_view'] = 'View Risk Report';
$string['xray:view'] = 'X-ray View';

/* Report Activity Report*/
$string['activityreport'] = 'Activity Report';
$string['activityreport_students_activity'] = 'Students Activity';
$string['activityreport_activity_of_course_by_day'] = 'Activity of course by day';
$string['activityreport_activity_by_time_of_day'] = 'Activity by time of day';
$string['activityreport_activity_last_two_weeks'] = 'Activity Last two weeks';
$string['activityreport_activity_last_two_weeks_by_weekday'] = 'Activity Last two weeks by weekday';
$string['activityreport_activity_by_participant1'] = 'Activity by participant 1';
$string['activityreport_activity_by_participant2'] = 'Activity by participant 2';
$string['activityreport_first_login'] = 'First Login';
$string['activityreport_first_login_non_starters'] = 'First Login non starters';
$string['activityreport_first_login_to_course'] = 'First Login to Course';
$string['activityreport_first_login_date_observed'] = 'First Login Date Observed';

/* Report Activity Report Individual*/
$string['activityreportindividual'] = 'Activity Report Individual';
$string['activityreportindividual_activity_by_date'] = 'Activity by Date';
$string['activityreportindividual_activity_last_two_weeks'] = 'Activity Last Two weeks';
$string['activityreportindividual_activity_last_two_weeks_byday'] = 'Activity Last Two weeks by weekday';

/* Discussion report*/
$string['discussionreport'] = 'Discussion Report';
$string['discussionreport_average_words_weekly_by_post'] = 'Average words weekly by post';
$string['discussionreport_discussion_activity_by_week'] = 'Discussion Activity by Week ';
$string['discussionreport_main_terms'] = 'Main Terms';
$string['discussionreport_social_structure_coefficient_of_critical_thinking'] = 'Social structure coefficient of critical thinking';
$string['discussionreport_social_structure_with_contributions_adjusted'] = 'Social structure with contributions adjusted';
$string['discussionreport_social_structure_with_words_count'] = 'Social structure with words count';
$string['discussionreport_social_structure'] = 'Social structure';
$string['discussionreport_participation_metrics'] = 'Participation Metrics';

/* Discussion report individual*/
$string['discussionreportindividual'] = 'Discussion Report Individual';
$string['discussionreportindividual_social_structure'] = 'Social Structure';
$string['discussionreportindividual_main_terms'] = 'Main Terms';
$string['discussionreportindividual_main_terms_histogram'] = 'Main Terms Histogram';

/* Discussion report individual forum*/
$string['discussionreportindividualforum'] = 'Discussion Report Individual Forum';
$string['discussionreportindividualforum_wordshistogram'] = 'Words Histogram';
$string['discussionreportindividualforum_socialstructure'] = 'Social Structure';
$string['discussionreportindividualforum_wordcloud'] = 'Word Cloud';

/* Discussion report Endogenic Plagiarism*/
$string['discussionendogenicplagiarism'] = 'Discussion Endogenic Plagiarism';
$string['discussionendogenicplagiarism_heatmap_endogenic_plagiarism_students'] = 'Heatmap Endogenic Plagiarism (Students only)';
$string['discussionendogenicplagiarism_heatmap_endogenic_plagiarism_instructors'] = 'Heatmap Endogenic Plagiarism (Including Instructor)';

/* Risk report*/
$string['risk'] = 'Risk';
$string['risk_risk_measures'] = 'Risk Measures';
$string['risk_total_risk_profile'] = 'Total Risk Profile';
$string['risk_academic_vs_social_risk'] = 'Academic Versus Social Risk';

/* Discussiongrading report*/
$string['discussiongrading'] = 'Risk';
$string['discussiongrading_students_grades_based_on_discussions'] = 'Students Grades based on discussions';
$string['discussiongrading_barplot_of_suggested_grades'] = 'Barplot of suggested grades';

/* Gradebook report*/
$string['gradebookreport'] = 'Gradebook Report';

/* Columns reports */
$string['table_fetchingdata'] = 'Fetching Data, Please wait...';
$string['fullname'] = 'Fullname';
$string['firstname'] = 'Firstname';
$string['lastname'] = 'Lastname';
$string['lastactivity'] = 'Last Activity';
$string['discussionposts'] = 'Discussion Posts';
$string['postslastweek'] = 'Posts Last Week';
$string['timespentincourse'] = 'Time spent in course';
$string['regularityweekly'] = 'Regularity (weekly)';
$string['posts'] = 'Posts';
$string['contribution'] = 'Contribution';
$string['ctc'] = 'CTC';
$string['regularityofcontributions'] = 'Regularity of contributions';
$string['regularityofctc'] = 'Regularity of CTC';
$string['reportdate'] = 'Date of report';
$string['academicrisk'] = 'Academic Risk';
$string['socialrisk'] = 'Social Risk';
$string['totalrisk'] = 'Total Risk';
$string['averageresponselag'] = 'Average Response Lag';
$string['averagenoofwords'] = 'Average No of Words';
$string['weeks'] = 'Weeks';
$string['numposts'] = 'Number of posts';
$string['wordcount'] = 'Word coutn';
$string['regularity_contributions'] = 'Regularity contributions';
$string['critical_thinking_coefficient'] = 'Critical thinking coefficient';
$string['grade'] = 'Grade';	

/* Webservice */
$string['error_xray'] = 'Error to connect with Xray.';

/* Settings */
$string['enabledreports'] = 'Enabled Reports';
$string['enabledreports_desc'] = 'Reports enabled for view in moodle.';
$string['xrayclientid'] = 'Identifier client';
$string['xrayclientid_desc'] = 'Identifier client for xray';
$string['xraypassword'] = 'Xray Password';
$string['xraypassword_desc'] = '';
$string['xrayurl'] = 'Xray Url';
$string['xrayurl_desc'] = '';
$string['xrayusername'] = 'Xray Username';
$string['xrayusername_desc'] = '';

/* webservice api */
$string['xrayws_error_nocurl'   ] = 'cURL module must be present and enabled!';
$string['xrayws_error_nourl'    ] = 'You must specify URL!';
$string['xrayws_error_nomethod' ] = 'You must specify request method!';

/* Web service errors returned from XRay*/
$string['xrayws_error_invalid_credentials' ] = 'Web service credentials are not valid!';
$string['xrayws_error_unauthorised' ] = 'Not authorised to access web service!';
$string['xrayws_error_not_found' ] = 'Requested report not found!';
