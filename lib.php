<?php
defined('MOODLE_INTERNAL') || die();
/**
 * Local xray lang file
 *
 * @author Pablo Pagnone
 * @author German Vitale
 * @package local_xray
 */
 
/**
 * Extend navigations block.
 */
function local_xray_extends_settings_navigation($settings, $context) {

    global $PAGE, $COURSE;

    if(is_callable('mr_on') && mr_on("xray", "_MR_LOCAL")) {

        if($COURSE->id != SITEID && has_capability('local/xray:view', $context)) {

            $plugin = "local_xray";

            // Reports to show in course-view.	
            if($PAGE->pagetype == "course-view-".$COURSE->format) {

                //Show nav x-ray in courseadmin node.
                $coursenode = $settings->get('courseadmin');
                $extranavigation = $coursenode->add(get_string('navigation_xray', $plugin));

                // Activity report.
                if(has_capability('local/xray:activityreport_view', $context)){
                    $url = new moodle_url('/local/xray/view.php', array("controller" => "activityreport",
                                                                        "courseid"   => $COURSE->id));

                    $extranavigation->add(get_string('activityreport', $plugin),$url);					
                }

                // Discussion report.
                if(has_capability('local/xray:discussionreport_view', $context)){
                    $url = new moodle_url('/local/xray/view.php', array("controller" => "discussionreport",
                                                                        "courseid"   => $COURSE->id));
                    $extranavigation->add(get_string('discussionreport', $plugin),$url);
                }

                // Discussion grading.
                if(has_capability('local/xray:discussiongrading_view', $context)){
                    $url = new moodle_url('/local/xray/view.php', array("controller" => "discussiongrading",
                                                                    "courseid"   => $COURSE->id));

                    $extranavigation->add(get_string('discussiongrading', $plugin),$url);
                }

                // Endogenic Plagiarism.
                if(has_capability('local/xray:discussionendogenicplagiarism_view', $context)){
                    $url = new moodle_url('/local/xray/view.php', array("controller" => "discussionendogenicplagiarism",
                                                                "courseid"   => $COURSE->id));				
                    $extranavigation->add(get_string('discussionendogenicplagiarism', $plugin),$url);	
                }

                // Risk.
                if(has_capability('local/xray:risk_view', $context)){
                    $url = new moodle_url('/local/xray/view.php', array("controller" => "risk",
                                                               "courseid"   => $COURSE->id));

                    $extranavigation->add(get_string('risk', $plugin),$url);	
                }
                // Gradebook report.
                if(has_capability('local/xray:gradebookreport_view', $context)){
                    $url = new moodle_url('/local/xray/view.php', array("controller" => "gradebookreport",
                            "courseid"   => $COURSE->id));
                
                    $extranavigation->add(get_string('gradebookreport', $plugin),$url);
                }
            }

            // Report to show in forum-view.
            if($PAGE->pagetype == "mod-forum-view") {

                //Show nav x-ray in module setting node.
                $coursenode = $settings->get('modulesettings');
                $extranavigation = $coursenode->add(get_string('navigation_xray', $plugin));

                // Discussion report individual forum.
                if(has_capability('local/xray:discussionreportindividualforum_view', $context)){
                    $url = new moodle_url('/local/xray/view.php', array("controller" => "discussionreportindividualforum",
                                                                        "courseid"   => $COURSE->id,
                                                                        "cmid"   => $context->instanceid,
                                                                        "forum"      => $PAGE->cm->instance));

                    $extranavigation->add(get_string('discussionreportindividualforum', $plugin),$url);		
                }
            }

            // Report to show in forum-view.
            if($PAGE->pagetype == "mod-quiz-view") {

                //Show nav x-ray in module setting node.
                $coursenode = $settings->get('modulesettings');
                $extranavigation = $coursenode->add(get_string('navigation_xray', $plugin));

                // Discussion report individual forum.
                if(has_capability('local/xray:discussionreportindividualforum_view', $context)){
                    $url = new moodle_url('/local/xray/view.php', array("controller" => "discussionreportindividualforum",
                            "courseid"   => $COURSE->id,
                            "cmid"   => $context->instanceid,
                            "forum"      => $PAGE->cm->instance));
                    	
                    $extranavigation->add(get_string('discussionreportindividualforum', $plugin),$url);
                }
            }
        }
    }
}
