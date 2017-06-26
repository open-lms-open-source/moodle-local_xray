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
 * @package   local_xray
 * @author    Pablo Pagnone
 * @author    German Vitale
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die();

/** @var string[] $string */
$string['navigation_xray'] = 'X-Ray Learning Analytics ';
$string['navitationcourse_xray'] = 'X-Ray Learning Analytics';
$string['pluginname'] = 'X-Ray Learning Analytics';
$string['reports'] = 'Reports';
$string['analytics'] = 'Course Analytics';
$string['xraydisplayheading'] = 'Course Integration';
$string['xraydisplayheading_desc'] = 'Control the display of information and report links on the course frontpage.';
$string['xraydisplaysystemheading'] = 'System Reports';
$string['xraydisplaysystemheading_desc'] = 'Control the display of System Reports.';
$string['displaymenu'] = 'Show reports menu';
$string['displaymenu_desc'] = 'Control the display reports menu on the course frontpage.';
$string['displaysystemreports'] = 'Show System Reports';
$string['displaysystemreports_desc'] = 'By default, X-Ray System Reports don\'t appear in the Reports menu. '.
                                       'Check the box to show them.';
$string['displayheaderdata'] = 'Show Analytics';
$string['displayheaderdata_desc'] = 'Control the display course analytics on the course frontpage.';
$string['debuginfo'] = 'Debug information:';
$string['cachedef_request'] = 'X-Ray request cache';

// Capabilities.
$string['xray:activityreportindividual_view'] = 'View Student Activity Report';
$string['xray:activityreport_view'] = 'View Activity Report';
$string['xray:adminrecommendations_view'] = 'View Recommendations for Admin';
$string['xray:dashboard_view'] = 'View Dashboard Report';
$string['xray:discussionreport_view'] = 'View Discussion Report';
$string['xray:discussionreportindividual_view'] = 'View Student Discussion Report';
$string['xray:discussionreportindividualforum_view'] = 'View Forum Activity Report';
$string['xray:discussionendogenicplagiarism_view'] = 'View Word Overlap';
$string['xray:discussiongrading_view'] = 'View Grading';
$string['xray:globalsub_view'] = 'View Global Subscription Page';
$string['xray:gradebookreport_view'] = 'View Gradebook Report';
$string['xray:gradebookreportindividualquiz_view'] = 'View Gradebook Report Indivisual Quiz';
$string['xray:risk_view'] = 'View Risk Status Report';
$string['xray:subscription_view'] = 'Subscribe to email report';
$string['xray:systemreports_view'] = 'View System Reports';
$string['xray:teacherrecommendations_view'] = 'View Recommendations for Teacher';
$string['xray:courseselection_view'] = 'X-Ray Course Selection';
$string['xray:view'] = 'X-Ray Learning Analytics View';

// Categories for numbers values.
$string['high'] = 'High';
$string['low'] = 'Low';
$string['medium'] = 'Medium';

$string['highlyregular'] = 'Highly regular';
$string['irregular'] = 'Irregular';
$string['regular'] = 'Regular';

// Report Activity Report.
$string['activityreport'] = 'Activity';
// Report Activity Report Individual.
$string['activityreportindividual'] = 'Student Activity Report';
// Discussion report.
$string['discussionreport'] = 'Discussions';
// Discussion report individual.
$string['discussionreportindividual'] = 'Student Discussion Report';
// Discussion report individual forum.
$string['discussionreportindividualforum'] = 'Forum Activity Report';
// Discussion report Endogenic Plagiarism.
$string['discussionendogenicplagiarism'] = 'Word Overlap';
// Risk report.
$string['risk'] = 'Risk Status';
// Discussiongrading report.
$string['discussiongrading'] = 'Grading';
// Gradebook report.
$string['gradebookreport'] = 'Gradebook';
// System Reports.
$string['systemreports'] = 'System Reports';
$string['help'] = 'Help';

// Columns reports.
$string['reportdate'] = 'Date of report';
$string['weeks'] = 'Weeks';
$string['week'] = 'Week';

// Error to load tables and images.
$string['error_loadimg'] = 'Unable to load image, please try reloading the page. If the image still doesn’t load, '.
    'contact your system administrator.';

// Error Webservice.
$string['error_xray'] = 'Can’t connect to X-Ray Learning Analytics, please try reloading the page. '.
                        'If you still can’t connect, contact your system administrator.';
$string['error_compress'] = 'Unable to create compressed file. Please contact your system administrator.';
$string['error_generic'] = '{$a}';
$string['error_fexists'] = 'File "{$a}" already exists!';
$string['error_fnocreate'] = 'Unable to create "{$a}" file!';
$string['error_systemreports_nourl'] = 'The System Reports URL is missing from the X-Ray Learning Analytics configuration page.';
$string['error_systemreports_gettoken'] = 'Error to get token for access to system reports.';
$string['error_systemreports_disabled'] = 'System Reports aren\'t displaying. This was turned off on the '.
                                           'X-Ray Learning Analytics configuration page.';

// Settings.
$string['enabledreports'] = 'Enabled Reports';
$string['enabledreports_desc'] = 'Reports enabled for view in moodle.';
$string['xrayclientid'] = 'X-Ray Learning Analytics Client Identifier';
$string['xrayclientid_desc'] = 'Client Identifier for X-Ray Learning Analytics';
$string['xraypassword'] = 'X-Ray Learning Analytics Password';
$string['xraypassword_desc'] = 'Password for logging into X-Ray Learning Analytics server.';
$string['xrayurl'] = 'X-Ray Learning Analytics Url';
$string['xrayurl_desc'] = 'Location of X-Ray Learning Analytics';
$string['xrayusername'] = 'X-Ray Learning Analytics Username';
$string['xrayusername_desc'] = 'User for logging into X-Ray Learning Analytics server.';
$string['xrayawsheading'] = 'Data Synchronization';
$string['xrayawsheading_desc'] = 'In this section you can configure automated data synchronization with X-Ray Learning Analytics.';
$string['enablesync'] = 'Data Sync';
$string['enablesync_desc'] = 'Enable automated data synchronization with X-Ray Learning Analytics.';
$string['awskey'] = 'AWS Key';
$string['awskey_desc'] = 'Access key for AWS web services';
$string['awssecret'] = 'AWS Secret';
$string['awssecret_desc'] = 'Access key for AWS web services';
$string['s3bucket'] = 'S3 bucket';
$string['s3bucket_desc'] = 'Name of the bucket to use for storing data uploads.';
$string['s3bucketregion'] = 'S3 region';
$string['s3bucketregion_desc'] = 'Region of the destination bucket.';
$string['enablepacker'] = 'Use native compression';
$string['enablepacker_desc'] = 'If enabled permits the use of OS native compression tools.';
$string['packertar'] = 'GNU tar executable';
$string['packertar_desc'] = 'Configure location of <a href="http://www.gnu.org/software/tar/" target="_blank" '.
    'title="GNU tar">GNU tar</a> executable on your server. Make sure to install '.
    '<a href="http://www.gnu.org/software/gzip/" target="_blank" title="GNU Gzip">GNU Gzip</a> as well.';
$string['exportlocation'] = 'Export location';
$string['exportlocation_desc'] = 'Configure local directory for temporary storage of exported data. If left empty '.
    '(or if path not valid) Moodle tempdir is used.';
$string['exporttime'] = 'Export time';
$string['exporttime_desc'] = 'Set maximum permitted time for exporting data. If set to 0 no time limit is imposed.';
$string['export_progress'] = 'Reset export progress';
$string['export_progress_desc'] = 'During export progress of the currently exported information is stored in the '.
    'database. Checking this option will reset that data.';
$string['curlcache'] = 'Web service cache timeout';
$string['curlcache_desc'] = 'Determines for how long to store cached web service responses.'.
    ' If set to 0 no caching is performed. Note - only successful responses are cached.';

$string['xrayadminserver'] = 'X-Ray Learning Analytics Administration server';
$string['xrayadminserver_desc'] = 'Server location.';
$string['xrayadmin'] = 'Admin user';
$string['xrayadmin_desc'] = 'User for logging into admin server.';
$string['xrayadminkey'] = 'Admin key';
$string['xrayadminkey_desc'] = 'Access key for logging into admin server.';
$string['s3protocol'] = 'Upload protocol';
$string['s3protocol_desc'] = 'Determines a protocol that will be used in upoloading exported information.';
$string['http'] = 'HTTP protocol';
$string['https'] = 'Secure HTTP protocol';
$string['s3uploadretry'] = 'Upload retries';
$string['s3uploadretry_desc'] = 'How many times should the system try to re-upload exported information if the upload fails.';

$string['useast1'] = 'US Standard (N. Virginia)';
$string['uswest2'] = 'US West (Oregon)';
$string['uswest1'] = 'US West (N. California)';
$string['euwest1'] = 'EU (Ireland)';
$string['eucentral1'] = 'EU (Frankfurt)';
$string['apsoutheast1'] = 'Asia Pacific (Singapore)';
$string['apsoutheast2'] = 'Asia Pacific (Sydney)';
$string['apnortheast1'] = 'Asia Pacific (Tokyo)';
$string['saeast1'] = 'South America (Sao Paulo)';

$string['systemreportsurl'] = 'System Reports URL';
$string['systemreportsurl_desc'] = 'URL to connect with System Reports.';

// Webservice api.
$string['xrayws_error_nocurl'   ] = 'cURL module must be present and enabled!';
$string['xrayws_error_nourl'    ] = 'You must specify URL!';
$string['xrayws_error_nomethod' ] = 'You must specify request method!';

// Web service errors returned from XRay.
$string['xrayws_error_server'] = '{$a}';
$string['xrayws_error_curl'] = '{$a}';
$string['xrayws_error_graphs'] = 'Error to get image ({$a->url}) for "{$a->graphelement}": {$a->error}.';
$string['xrayws_error_graphs_incorrect_contentype'] = 'Incorrect content-type received from xray webservice: {$a}.';

// Scheduled task.
$string['datasync'] = 'Data Synchronization';
$string['syncfailed'] = 'X-Ray Learning Analytics data sync failed';
$string['unexperror'] = 'Unexpected error: ';
$string['syncfailedexplanation'] = 'Failed to synchronize data with X-Ray Learning Analytics.';
$string['synclog'] = 'X-Ray Learning Analytics data sync info message';
$string['synclogexplanation'] = 'Regular log entry for data sync.';
$string['getreportfailed'] = 'Error: Couldn’t get the X-Ray report';
$string['dataprune'] = 'Data pruning';
$string['reportviewed'] = 'Report viewed';
$string['sendemails'] = 'X-Ray E-Mails';
$string['emaillog'] = 'X-Ray E-Mail Log';

// Course Header.
$string['atrisk'] = 'At risk';
$string['dashboard'] = 'Dashboard';
$string['headline_lastweekwas_discussion'] = 'Week before was {$a}.';
$string['averageofweek_integer'] = 'Average of the week before was {$a->previous} of {$a->total}.';
$string['averageofweek_gradebook'] = 'Average of the week before was {$a}%.';
$string['headline_lastweekwasof_activity'] = 'Week before was {$a->current} of {$a->total}.';
$string['headline_studentatrisk'] = 'Students at <b>Risk</b> yesterday.';
$string['headline_loggedstudents'] = '<b>Logged in</b> students in the last 7 days.';
$string['headline_posts'] = '<b>Posts</b> in the last 7 days.';
$string['headline_average'] = '<b>Average course grade</b> yesterday.';
$string['link_gotoreport'] = 'Go to report';
$string['arrow_increase'] = 'This is an increase.';
$string['arrow_decrease'] = 'This is a decrease.';
$string['arrow_same'] = 'This stays the same.';
$string['headline_number_of'] = '{$a->first} of {$a->second}';
$string['headline_number_percentage'] = '{$a}%';

// Jquery Tables (with plugin datatables).
$string['error_datatables'] = 'Error to get data for this table. Please try again reloading the page. '.
    'If error persist, contact with the administrator please.';
$string['sProcessingMessage'] = 'Fetching Data, Please wait...';
$string['sFirst'] = 'First';
$string['sLast'] = 'Last';
$string['sNext'] = 'Next';
$string['sPrevious'] = 'Previous';
$string['sProcessing'] = 'Processing...';
$string['sLengthMenu'] = 'Show _MENU_';
$string['sZeroRecords'] = 'No matching records found';
$string['sEmptyTable'] = 'No data available in table';
$string['sInfo'] = 'Showing _START_';
$string['sInfoEmpty'] = 'Showing 0';
$string['sLoadingRecords'] = 'Loading...';
$string['sSortAscending'] = ': activate to sort column ascending';
$string['sSortDescending'] = ': activate to sort column descending';

// Close modal.
$string['close'] = 'Close';
// Close Report Tables.
$string['closetable'] = 'Close table';

// Accessible data.
$string['accessibledata'] = 'Accessible Data';
$string['accessible_view_data'] = 'View data';
$string['accessible_view_data_for'] = 'Accessible Data for {$a} (new window)';
$string['accessible_emptydata'] = 'Without data available for Accessible version.';
$string['accessible_error'] = 'Accessible version for this graph was not found in X-Ray Learning Analytics.';
$string['riskreports_help'] = 'Help with Risk Status';
$string['activityreports_help'] = 'Help with Activity';
$string['gradebookreports_help'] = 'Help with Gradebook';
$string['discussionreports_help'] = 'Help with Discussions';
$string['accessibledata_of'] = 'Accessible Data of {$a}';

// Tables names all report.
$string['activityreport_nonStarters'] = 'Inactive Students';// Activity report.
$string['activityreport_studentList'] = 'Activity Metrics'; // Activity report.
$string['risk_nonStarters'] = 'Inactive Students'; // Risk report.
$string['risk_riskMeasures'] = 'Risk Metrics'; // Risk report.
$string['gradebookreport_courseGradeTable'] = 'Student Grades'; // Gradebook report.
$string['gradebookreport_gradableItemsTable'] = 'Summary of Graded Items'; // Gradebook report.
$string['discussionreport_discussionMetrics'] = 'Participation Metrics'; // Discussion report.
$string['discussionreport_discussionActivityByWeek'] = 'Activity by Week'; // Discussion report.
$string['discussionreport_studentDiscussionGrades'] = 'Grade Recommendation'; // Discussion report.
$string['discussionreportindividual_discussionMetrics'] = 'Participation Metrics'; // Discussion report individual.
$string['discussionreportindividual_discussionActivityByWeek'] = 'Activity by Week'; // Discussion report individual.

// Help tables all reports.
$string['activityreport_nonStarters_help'] = 'The following students have not been active in your course yet. ';// Activity.
$string['activityreport_studentList_help'] = 'Seeing how students are active in a course give insight into their'.
    ' engagement and practices. This table shows student activity and regularity. The lower the number in the Visit'.
    ' Regularity column the more regular they are. You can also see reports on individual students. Click the report'.
    ' at the beginning of the row to see how that student is doing. '; // Activity report.
$string['risk_nonStarters_help'] = 'The following students have not been active in your course yet.'; // Risk report.
$string['risk_riskMeasures_help'] = 'This table helps you identify the students who are at risk of droping out,'.
    ' withdrawing from, or failing in your course. Higher numbers indicate greater risk. The total risk is based'.
    ' on a student\'s grades (academic) as well as their participation in course discussions (social).'; // Risk report.
$string['gradebookreport_courseGradeTable_help'] = 'This table shows you how well students are doing on the graded'.
    ' items in the course. The percentage score for the course grade and the average score in different type'.
    ' of graded items is shown for each student.'; // Gradebook report.
$string['gradebookreport_gradableItemsTable_help'] = 'This table shows how students have done as a percentage on each'.
    ' item. It also shows you the relationship of this item to the student\'s overall course grade so far.'; // Gradebook report.
$string['discussionreport_discussionMetrics_help'] = 'This table shows how a student participates in'.
    ' discussions. Original contribution and critical thought scores are determined by an analysis of the words'.
    ' used by the students. Original contribution is based on a word count with stop words, such as pronouns and'.
    ' prepositions, filtered out to determine the ratio of unique words used. Critical thought is based on the number'.
    ' of reflective statements students use. For example, "I agree" or "Me too!". Click the report beside a student'.
    ' name to see how they are doing.'; // Discussion report.
$string['discussionreport_discussionActivityByWeek_help'] = ''; // Discussion report. We dont show help for this.
$string['discussionreport_studentDiscussionGrades_help'] = 'Grading recommendations are based on the frequency of'.
    ' posts, original contribution, and evidence of critical thought. Each are weighted equally by default.'; // Discussion report.
$string['discussionreportindividual_discussionMetrics_help'] = 'This table shows how a student participates'.
    ' in discussions. Original contribution and critical thought scores are determined by an analysis of the words'.
    ' used by the students. Original contribution is based on a word count with stop words, such as pronouns'.
    ' and prepositions, filtered out to determine the ratio of unique words used. Critical thought is based on the'.
    ' number of reflective statements students use. For example, "I agree" or "Me too!". Click the report beside'.
    ' a student name to see how they are doing.'; // Discussion report individual.
// Discussion report individual. We dont show help for this.
$string['discussionreportindividual_discussionActivityByWeek_help'] = '';

// Graphs Activity report.
$string['activityreport_activityLevelTimeline'] = 'Course Activity by Date';
$string['activityreport_compassTimeDiagram'] = 'Activity by Time of Day';
$string['activityreport_barplotOfActivityByWeekday'] = 'Activity Over Last Two Weeks by Weekday';
$string['activityreport_barplotOfActivityWholeWeek'] = 'Activity Over Last Two Weeks';
$string['activityreport_activityByWeekAsFractionOfTotal'] = 'Relative Activity Compared to Other Students in Class';
$string['activityreport_activityByWeekAsFractionOfOwn'] = 'Relative Activity Compared to Self';
$string['activityreport_firstloginPiechartAdjusted'] = 'Pie Chart of First Time Access Distribution';

// Help Graphs Activity report.
$string['activityreport_activityLevelTimeline_help'] = 'This graph shows an estimate of time spent in your course'.
    ' (blue line) and a forecast (dotted line) for the next two weeks. The dark-gray line shows the estimated'.
    ' average hours active over a period of time. The shaded area it shows how close the representation of the estimated'.
    ' average is to the true average of the class. Activity forecasted for the next two weeks is indicated with'.
    ' a dotted line. Spikes in activity which are outside of the expected range are highlighted.';
$string['activityreport_compassTimeDiagram_help'] = 'This diagram is of a 24-hour day. It is based on the time set on'.
    ' your institution\'s server.  A line shows when students are spending time in your course. Your course is busiest'.
    ' when the line approaches the outside edges of the 24-hour circle. This information  can help you plan activities'.
    ' that require full participation.';
$string['activityreport_barplotOfActivityByWeekday_help'] = 'This chart shows the estimated time spent in the course '.
    'broken down by weekday.'.
    ' The blue bars represent the activity from the past seven days. The yellow bars show what the activity was '.
    'like seven days before.';
$string['activityreport_barplotOfActivityWholeWeek_help'] = 'This chart shows the estimated time spent in the course'.
    ' over a week. The blue bars represent the activity from the last seven days. The yellow bars show what'.
    ' the activity was like the seven days before. ';
$string['activityreport_activityByWeekAsFractionOfTotal_help'] = 'Each dot in this chart represents the time a student'.
    ' spent in your course in a given week compared to other students. Bigger dots indicate more activity.'.
    ' Names in parentheses indicate persons, who are not enrolled currently as students for this course.';
$string['activityreport_activityByWeekAsFractionOfOwn_help'] = 'Each dot in this chart represents the time a student'.
    ' spent in your course in a given week compared to other weeks. Bigger dots indicate more activity. Names in parentheses'.
    ' indicate persons, who are not enrolled currently as students for this course.';
$string['activityreport_firstloginPiechartAdjusted_help'] = 'The diagram shows you when students logged into your course'.
    ' and how many haven\'t yet. The first day of the course may not be the scheduled start date. It starts the day the'.
    ' first participant accessed the course. The pattern seen here may be an indication of future engagement.';

// Graphs Activity Individual report.
$string['activityreportindividual_activityLevelTimeline'] = 'Activity by Date';
$string['activityreportindividual_barplotOfActivityByWeekday'] = 'Activity Over Last Two Weeks by Weekday';
$string['activityreportindividual_barplotOfActivityWholeWeek'] = 'Activity Over Last Two Weeks';

// Help Graphs Activity Individual report.
$string['activityreportindividual_activityLevelTimeline_help'] = 'The graph shows an estimate of time spent in your'.
    ' course (blue line) and a forecast (dotted line) for the next two weeks. The dark-gray line shows the estimated'.
    ' average hours active over a period of time. The shaded area it shows how close the representation of the'.
    ' estimated average is to the true average of the class. Activity forecasted for the next two weeks is indicated'.
    ' with a dotted line. Spikes in activity which are outside of the expected range are highlighted.';
$string['activityreportindividual_barplotOfActivityByWeekday_help'] = 'The chart shows the estimated time spent in the'.
    ' course broken down by weekday. The blue bars represent the activity from the last seven days. The yellow bars'.'
     show what the activity was like the seven days before.';
$string['activityreportindividual_barplotOfActivityWholeWeek_help'] = 'The chart shows the estimated time spent in the'.
    ' course over a week. The blue bars represent the activity from the last seven days. The yellow bars show what the'.
    ' activity was like seven days before. ';

// Graphs Risk report.
$string['risk_riskDensity'] = 'Total Risk Profile';
$string['risk_balloonPlotRiskHistory'] = 'Risk History';

// Help Graphs Risk report.
$string['risk_riskDensity_help'] = 'This graphic shows the distribution of estimated risk in the course. '.
    'Green represents those who are not considered at risk. Red shows those who are at high risk, and yellow those'.
    ' who are at medium risk.';
$string['risk_balloonPlotRiskHistory_help'] = 'The risk development over time is displayed for each student. '.
    'Color changes of the dots indicate changes in the risk categories. Green represents low risk, yellow '.
    'medium risk, and red high risk. The size of the dots shows the risk estimate. Small dots represent a low risk '.
    'of failure, and big dots a high risk.';

// Graphs Risk report.
$string['gradebookreport_studentScoreDistribution'] = 'Distribution of Grades';
$string['gradebookreport_scoreDistributionByItem'] = 'Distribution of Scores';
$string['gradebookreport_scatterPlot'] = 'Automatic Discussion Forum Grading versus Course Grade';
$string['gradebookreport_itemsHeatmap'] = 'Comparison of Scores';

// Help Graphs Gradebook report.
$string['gradebookreport_studentScoreDistribution_help'] = 'This graph shows how scores are distributed'.
    ' over your students. Peak(s) show what grades the majority of students are getting overall, with separate lines'.
    ' for each type of gradable item. A bell shaped distribution pattern could indicate that there was no bias or'.
    ' inconsistencies. Different distribution patterns may indicate significant differences in the difficulty'.
    ' level of the graded items.';
$string['gradebookreport_scoreDistributionByItem_help'] = 'This boxplot shows the distribution of student scores on'.
    ' graded items or item categories. A graded item or category is represented by a box and any vertical lines above and'.
    ' below it. Diamonds are student scores. The thick horizontal line shows the average score on the graded item. There are'.
    ' four grade ranges represented for each graded item. The top 25% (vertical line on top of the box), the 25% above average'.
    ' (area of the box above the average score), the 25% below average (area of the box below the average score), and the'.
    ' bottom 25% (vertical line below the box). The longer or taller the range the more spread out the scores are in it.';
$string['gradebookreport_scatterPlot_help'] = 'This graph shows a comparison of the automatic X-Ray grading on'.
    ' the basis of the quality of the discussion posts to grades on all graded items. Each dot in this diagram represents the'.
    ' values for a student. The black line shows the estimated relationship between the two grading methods. The shaded area'.
    ' (confidence interval) gives a range for the estimated relationship. If all dots are close to the black line, the two'.
    ' grading methods are consistent.';
$string['gradebookreport_itemsHeatmap_help'] = 'This heatmap shows how each student did on a graded item or'.
    ' item category compared to the rest of the class. Darker colors indicate higher scores. If you have too many of'.
    ' one shade it may indicate that the graded item or category is too easy or too difficult. Names in parentheses indicate'.
    ' persons, who participated in the graded items, but are not enrolled (currently) as students for this course.';

$string['discussionreport_wordcloud'] = 'Most Used Words';
$string['discussionreport_avgWordPerPost'] = 'Weekly Average Word Count per Post';
$string['discussionreport_socialStructure'] = 'Interaction Analysis (with instructor)';
$string['discussionreport_socialStructureWordCount'] = 'Interaction Analysis with Word Count (with instructor)';
$string['discussionreport_socialStructureWordContribution'] = 'Interaction Analysis with Original Contributions (with instructor)';
$string['discussionreport_socialStructureWordCTC'] = 'Interaction Analysis with  Critical Thought (with instructor)';
$string['discussionreport_endogenicPlagiarismStudentsHeatmap'] = 'Word Overlap between Posts (without instructor)';
$string['discussionreport_endogenicPlagiarismHeatmap'] = 'Word Overlap (with instructor)';
$string['discussionreport_discussionSuggestedGrades'] = 'Distribution of Recommended Grades';

// Help Graphs Discussion report.
$string['discussionreport_wordcloud_help'] = 'This word cloud shows the words used most often in discussions.'.
    ' It is based on a word count of unique words used. Bigger words indicate more use. The individual discussion report'.
    ' has a lower threshold for minimum number of word occurrences. It may include words not in the course discussion'.
    ' report word cloud.';
$string['discussionreport_avgWordPerPost_help'] = 'This graph shows the average word count in course discussion posts'.
    ' per week. The blue line represents observed values while the yellow dotted line represents the expected  average.';
$string['discussionreport_socialStructure_help'] = 'This diagram shows you who your students are replying to. Color'.
    ' shows how connected a student is to the rest of the class. Blue shows the student has an average or a above'.
    ' average connection to the rest of the class. Yellow shows a below average connection. Red shows the student'.
    ' has not connected with the rest of the class yet. "inst" in brackets indicates the instructor(s) of the course,'.
    ' and names in parentheses indicate persons, who participated in the forum(s), but are not enrolled as students'.
    ' for this course.';
$string['discussionreport_socialStructureWordCount_help'] = 'This diagram shows you who your students are talking to'.
    ' and how much they are saying. This is based on the number of words exchanged between two students in discussion'.
    ' posts. Thicker lines indicate more words being used. "inst" in brackets indicates the instructor(s) of the course,'.
    ' and names in parentheses indicate persons, who participated in the forum(s), but are not enrolled as students'.
    ' for this course.';
$string['discussionreport_socialStructureWordContribution_help'] = 'This diagram shows you who your students are'.
    ' talking to and the quality of their contribution. Original contribution is based on a word count with stop'.
    ' words filtered out to determine the ratio of unique words used. Thicker lines indicate more unique words'.
    ' being used. "inst" in brackets indicates the instructor(s) of the course,'.
    ' and names in parentheses indicate persons, who participated in the forum(s), but are not enrolled as students'.
    ' for this course.';
$string['discussionreport_socialStructureWordCTC_help'] = 'This diagram shows you which replies between students'.
    ' are showing evidence of critical thought. This is based on the number of reflective statements used. "inst" in'.
    ' brackets indicates the instructor(s) of the course, and names in parentheses indicate persons, who participated'.
    ' in the forum(s), but are not enrolled as students for this course.';
$string['discussionreport_endogenicPlagiarismStudentsHeatmap_help'] = 'The heatmaps show how similiar student posts'.
    ' are to others in the class. It shows who the knowlege source is and who is copying them. Original posts are'.
    ' determined by time stamps. Lower values indicate less similarity between posts. Review the posts of students'.
    ' with higher values, they may be quoting other students or plagiarising their work. Names in parentheses'.
    ' indicate persons, who participated in the forum(s), but are not enrolled as students for this course.';
$string['discussionreport_endogenicPlagiarismHeatmap_help'] = 'The heatmaps show how similiar student posts are to'.
    ' others in the class, including the instructor. It shows who the knowlege source is and who is copying'.
    ' them. Original posts are determined by time stamps. Lower values indicate less similarity between posts. Review'.
    ' the posts of students with higher values, they may be quoting other students and instructor or plagiarising'.
    ' their work. Names in parentheses indicate persons, who participated in the forum(s), but are not enrolled as'.
    ' students for this course.';
$string['discussionreport_discussionSuggestedGrades_help'] = 'This bar plot shows the distribution of suggested'.
    ' grades for the participation in discussion groups. The solid line shows an expected distribution of'.
    ' grades with a class average grade of C. The dotted line shows the distribution with a class average grade'.
    ' of B-. Blue bars represent the actual grades.';

// Graphs Discussion report individual.
$string['discussionreportindividual_wordcloud'] = 'Most Used Words';
$string['discussionreportindividual_socialStructure'] = 'Interaction Analysis';
$string['discussionreportindividual_wordHistogram'] = 'Frequency of Most Used Words';

// Help Graphs Discussion report individual.
$string['discussionreportindividual_wordcloud_help'] = 'This word cloud shows the words used most often in discussions.'.
     ' It is based on a word count of unique words used. Bigger words indicate more use. The individual discussion report'.
     ' has a lower threshold for minimum number of word occurrences. It may include words not in the course discussion'.
     ' report word cloud.';
$string['discussionreportindividual_socialStructure_help'] = 'This diagram shows who this student is talking to. Color'.
    ' shows how connected a student is to the rest of the class. Blue shows an average or a above average connection.'.
    ' Yellow shows a below average connection. Red shows that there has been no connection with that student yet. ';
$string['discussionreportindividual_wordHistogram_help'] = "This histogram shows how often a student's 10 most ".
    "used words are used.";

// Graphs Discussion report individual forum.
$string['discussionreportindividualforum_wordcloud'] = 'Most Used Words';
$string['discussionreportindividualforum_socialStructure'] = 'Interaction Analysis';
$string['discussionreportindividualforum_wordHistogram'] = 'Frequency of Most Used Words';

// Help Graphs Discussion report individual forum.
$string['discussionreportindividualforum_wordcloud_help'] = 'This word cloud shows the words used most often in discussions.'.
     ' It is based on a word count of unique words used. Bigger words indicate more use. The individual discussion report'.
     ' has a lower threshold for minimum number of word occurrences. It may include words not in the course discussion'.
     ' report word cloud.';
$string['discussionreportindividualforum_socialStructure_help'] = 'This diagram shows you who your students are'.
    ' replying to. Color shows how connected a student is to the rest of the class. Blue shows the student has'.
    ' an average or a above average connection to the rest of the class. Yellow shows a below average connection.'.
    ' Red shows the student has not connected with the rest of the class yet. "inst" in brackets indicates the'.
    ' instructor(s) of the course, and names in parentheses indicate persons, who participated in the forum(s), but'.
    ' are not enrolled as students for this course.';
$string['discussionreportindividualforum_wordHistogram_help'] = 'This histogram displays the frequency of the most'.
    ' used words in the forum. Words with a frequency of less than 10 have been excluded.';

// Behat test.
$string['error_behat_getjson'] = 'Error to get json file "{$a}" from folder local/xray/tests/fixtures for '.
    'simulate call to X-Ray Learning Analytics webservice when you are running behat test.';
$string['error_behat_instancefail'] = 'This is an instance configured for fail with behat tests.';

// Format for time range value.
$string['strftimehoursminutes'] = '%H:%M';

// Empties reports.
$string['xray_course_report_empty'] = 'There is not enough data for this report. Please try again when there is more'.
    ' user activity in your course.';

// Email.
$string['changesubscription'] = 'Change your subscription preferences';
$string['coursesubscribe'] = 'Subscribe to the email reports for {$a}';
$string['coursesubscribedesc'] = 'You will receive an email with the course X-Ray summary data';
$string['email_log_desc'] = 'Email sent. Course ID {$a->courseid} User ID {$a->to}.';
$string['emailsubject'] = 'X-Ray Report Summary for {$a}';
$string['erroremailheadline'] = 'Error with headline data. The email was not sent for Course ID {$a}';
$string['profilelink'] = 'X-Ray Global Subscription';
$string['subscribeall'] = 'Subscribe to the email reports for all courses';
$string['subscribetothiscourse'] = 'Subscribe to email report';
$string['subscriptiontitle'] = 'X-Ray Email Subscription';
$string['unsubscribeemail'] = 'Unsubscribe';
$string['unsubscribetothiscourse'] = 'Unsubscribe from email report';
$string['globalsubtitle'] = 'X-Ray Global Subscription';
$string['globalsubcourse'] = 'Use course level subscription settings';
$string['globalsubon'] = 'Subscribe to all courses';
$string['globalsuboff'] = 'Cancel all subscriptions';
$string['globalsubdesctitle'] = 'Select the configuration for the global subscription to the X-Ray summary report. '.
    'You can let the decision to subscribe be made at the course level or make the decision for all courses.';
$string['globalsubdescfirst'] = 'If you choose to <b>Use course level subscription settings</b>, you will receive '.
    'the X-Ray summary report only for the courses that you are subscribed. This is selected by default.';
$string['globalsubdescsecond'] = 'The <b>Subscribe to all courses</b> and <b>Cancel all subscriptions</b> options '.
    'overwrite subscription settings made at the course level. You will or will not receive the X-Ray summary '.
    'report for all courses.';
$string['subscriptiondisabled'] = 'Enable this setting in the X-Ray Global Subscription page. You can access '.
    'this page using the link X-Ray Global Subscription from your profile.';
$string['email_singleactivity'] = "Subscriptions aren't available for Single Activity Courses. No X-Ray alerts ".
    "are emailed for this course.";
$string['xrayemaildate'] = 'X-Ray data as of {$a}';
$string['pdfnotattached'] = 'The PDF was not attached.';
$string['strfemaildate'] = '%m%d%Y';

// Frequency control for emails.
$string['daily'] = 'Daily';
$string['emailfrequency'] = 'Frequency of email alerts';
$string['emailfrequency_desc'] = 'If you choose to email alerts daily, the emails start tomorrow. If you choose '.
    'to email alerts weekly, the emails start on the next Sunday. If you choose to never email alerts, users can '.
    'still subscribe but no emails are sent.';
$string['emailsdisabled'] = 'You will not receive alerts at this time. X-Ray email alerts are turned off. You can '.
    'still subscribe. Emails will be sent when the alerts are turned on. Contact your system administrator for more '.
    'information.';
$string['frequencyheading'] = 'Alert Email Frequency';
$string['frequencyheading_desc'] = 'Choose how often you want X-Ray alerts emailed to subscribers. By default, '.
    'alerts are emailed weekly. This happens every Sunday. You can choose to email alerts daily or never.';
$string['never'] = 'Never';
$string['weekly'] = 'Weekly';

// Recommended Actions.
$string['countaction'] = '{$a} recommended action';
$string['countactions'] = '{$a} recommended actions';
$string['recommendedactions'] = 'Recommended Actions';
$string['youhave'] = 'You have ';
$string['youdonthave'] = 'You do not have recommended actions';
$string['recommendedactions_button'] = 'Show/Hide recommended actions';
$string['recommendedaction_button'] = 'Show/Hide recommended action';

// Config validation errors for wsapi.
$string['error_wsapi_config_params_empty'] = 'X-Ray parameters are empty';
$string['error_wsapi_config_xrayusername'] = 'X-Ray Learning Analytics Username is empty';
$string['error_wsapi_config_xraypassword'] = 'X-Ray Learning Analytics Password is empty';
$string['error_wsapi_config_xrayurl'] = 'X-Ray Learning Analytics Url is empty';
$string['error_wsapi_config_xrayclientid'] = 'X-Ray Learning Analytics Client Identifier is empty';
$string['error_wsapi_exception'] = 'Error while communicating with X-Ray server: {$a}';
$string['error_wsapi_domaininfo_incomplete'] = 'Domain information is incomplete, missing: ${a}';

// Config validation errors for aws.
$string['error_awssync_config_enablesync'] = 'Data Sync is empty';
$string['error_awssync_config_awskey'] = 'AWS Key is empty';
$string['error_awssync_config_awssecret'] = 'AWS Secret is empty';
$string['error_awssync_config_s3bucket'] = 'S3 bucket is empty';
$string['error_awssync_config_s3bucketregion'] = 'S3 region is empty';
$string['error_awssync_config_s3protocol'] = 'Upload protocol is empty';
$string['error_awssync_config_s3uploadretry'] = 'Upload retry is empty';
$string['error_awssync_exception'] = 'Error while communicating with AWS server: {$a}';

// Config validation error reasons.
$string['error_wsapi_reason_login'] = 'Logging into X-Ray server';
$string['error_wsapi_reason_accesstoken'] = 'Accessing X-Ray server token';
$string['error_wsapi_reason_accountcheck'] = 'Checking the account information in the X-Ray server';
$string['error_wsapi_reason_domaininfo'] = 'Getting information about for the specified <strong>Client Identifier</strong>';
$string['error_wsapi_reason_courses'] = 'Getting X-Ray server courses';
$string['error_aws_reason_client_create'] = 'Creating AWS client';
$string['error_aws_reason_object_list'] = 'Listing Bucket Objects';
$string['error_aws_reason_upload_file'] = 'Uploading a file';
$string['error_aws_reason_download_file'] = 'Downloading a file';
$string['error_aws_reason_erase_file'] = 'Erasing a file';

// Config validation errors for compression.
$string['error_compress_config_enablepacker'] = 'Use native compression is empty';
$string['error_compress_config_packertar'] = 'GNU tar executable is empty';
$string['error_compress_config_exportlocation'] = 'Export location is empty';
$string['error_compress_exception'] = 'Error when compressing: {$a}';
$string['error_compress_files'] = 'Incorrect files found in archive(s). Please check all your compression parameters '.
    'against your operating system capabilities.';
$string['error_compress_packertar_invalid'] = '<strong>GNU tar executable</strong> is invalid.';

// Temporary for API check.
$string['connectionfailed'] = 'Failed - please check parameters';
$string['connectionverified'] = 'Parameters verified';
$string['connectionstatusunknown'] = 'Status unknown. There was a problem with the moodle server, please '.
    'contact your system administrator.';
$string['verifyingapi'] = '<div class="xray_validate_loader"></div> Checking parameters. Please wait.';
$string['test_api_action'] = 'Validate parameters';
$string['validate_check_fields'] = 'Please, check the following parameters';
$string['validate_service_response'] = 'Check the service response';
$string['validation_check_not_found'] = 'Validation check "{$a}" not found.';
$string['validation_check_not_filled'] = 'Validation check not specified.';

// API titles.
$string['test_api_ws_connect'] = 'X-Ray Server';
$string['test_api_s3_bucket'] = 'AWS S3 Bucket';
$string['test_api_compress'] = 'Compression';
$string['test_api_label'] = 'Validation';
$string['test_api_description'] = 'Check <strong>saved parameters</strong> for connectivity or system issues';
$string['validate_when'] = 'When';

// Validate courses.
$string['error_single_activity'] = 'Reports aren’t available for Single Activity Courses.';

// Settings page.
$string['global_settings'] = 'X-Ray Global Settings';
$string['courseselection'] = 'X-Ray Course Selection';

// Default X-Ray controller error.
$string['page_not_found'] = '404 - Page not found.';
$string['error_xray_unknown'] = 'Unknown X-Ray response, please reload the page. If error persists, contact '.
    'your system administrator.';

// Courses.
$string['xraycourses'] = 'X-Ray Courses';
$string['xraycourses_instructions'] = 'Select from this list the Categories and Courses that you want to use '.
    'with X-Ray Learning Analytics. Select a Category, if you want all of the associated courses to use X-Ray. '.
    'Or expand a Category and select individual courses.';
$string['xray_check_global_settings'] = 'Couldn\'t connect to X-Ray Learning Analytics Server. {$a}';
$string['xray_check_global_settings_link'] = 'Please validate that X-Ray Learning Analytics Server is available '.
    'before selecting the courses.';
$string['xray_save_course_filter_error'] = 'There was an error saving the courses to X-Ray Learning Analytics server:<br />{$a}';
$string['loading_please_wait'] = 'Loading. Please wait.';
$string['warn_courses_do_not_match'] = "The selected courses on the X-Ray server don't match the courses selected here.".
    " You will overwrite X-Ray server analysis filter if you save your selection.";
$string['warn_courses_not_persisted_in_xrf'] = 'The selected courses weren\'t saved in the X-Ray server due to '.
    'system configuration.';

// Course related messages.
$string['warn_course_disabled'] = 'This course doesn\'t use X-Ray Learning Analytics. If you think it should, '.
    'contact your system administrator.';
$string['course_disabled'] = '<p>This course can’t use X-Ray Learning Analytics at this time. Here’s why:'.
    '</p><ul>{$a->students}{$a->hidden}{$a->single}</ul><p>If you think your course meets the requirements and '.
    'still can’t use X-Ray, contact your system administrator.</p>';
$string['course_many_students'] = '<li>The course has too many students. There can’t be more than {$a} students '.
    'enrolled to use X-Ray.</li>';
$string['course_hidden'] = '<li>The course is hidden. Courses must be visible to use X-Ray.</li>';
$string['course_single_activity_format'] = "<li>The course is in Single Activity format. Courses can't be in ".
    "Single Activity format to use X-Ray.</li>";

// X-Ray Reports.
$string['xraydashboardurl'] = 'X-Ray Dashboard URL';
$string['xraydashboardurl_desc'] = 'URL to connect with X-Ray dashboard.';
$string['xrayreportsurl'] = 'X-Ray Reports URL';
$string['xrayreportsurl_desc'] = 'URL to connect with X-Ray reports.';
$string['xrayreports'] = 'X-Ray reports';
$string['noaccessxrayreports'] = 'X-Ray Reports are not available. If you want the reports turned on, please contact '.
    'your system administrator.';
$string['noaccessoldxrayreports'] = 'This version of the report is no longer available. There is a new version of the '.
    'report available. If you still want this version of the report, please contact your system administrator.';
$string['error_xrayreports_nourl'] = 'The Xray Reports URL is missing from the X-Ray Learning Analytics configuration page.';
$string['error_xrayclientid'] = 'X-Ray Learning Analytics Client Identifier is empty';
$string['error_xrayreports_gettoken'] = 'Error to get token for access to X-Ray reports.';
$string['dashboard_button'] = 'Show/Hide dashboard';

