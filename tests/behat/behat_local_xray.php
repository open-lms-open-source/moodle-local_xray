<?php
// @codingStandardsIgnoreFile
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
 * Steps definitions for behat theme.
 *
 * @package   local_xray
 * @category  test
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;
use Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Behat Local Xray
 *
 * @package   local_xray
 * @category  test
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_xray extends behat_base {


    public $courseshortname = '';

    /**
     * Create an express design based on an express template.
     *
     * @Given /^I use express template "(?P<username_string>(?:[^"]|\\")*)" for xray$/
     * @param string $template
     * @return void
     */
    public function i_use_express_template_for_xray($template) {
        global $CFG;

        $plugins = \core_plugin_manager::instance()->get_installed_plugins('block');
        if (!in_array('express', $plugins)) {
            return;
        }

        require_once("$CFG->dirroot/blocks/express/model/design.php");

        // Create express paths.
        make_upload_directory('express');
        make_upload_directory('express/tmp');

        // Add design at site context.
        $contextcourse = context_course::instance(SITEID);
        $parentcontextid = $contextcourse->id;
        $design = new block_express_model_design($parentcontextid);
        $data = new stdClass();
        $data->name = 'xrayheadlintest';
        $data->template = $template;
        $data->variant  = 'green';
        $data->iconpack  = 'serene';
        $data->resetimages = 0;
        $data->roundedcorners = 0;
        $data->hideui = 0;
        $data->analyticcode = '';
        $data->customcss = '';
        $design->create($data);
        $design->save($data);
    }

    /**
     * Allow guest access in course
     *
     * @Given /^I allow guest access for xray in course "(?P<shortname_string>(?:[^"]|\\")*)"$/
     * @param string $shortname
     * @return void
     * @throws ExpectationException
     */
    public function i_allow_guest_access_for_xray_in_course($shortname) {
        global $DB;
        $session = $this->getSession();
        // Get course id.
        $courseid = $DB->get_field('course', 'id', array('shortname' => $shortname));
        if (!$courseid) {
            throw new ExpectationException('The course with shortname '.$shortname.' does not exist', $session);
        }
        // Get enrol id for guest user.
        $enrolid = $DB->get_field('enrol', 'id', array('enrol' => 'guest', 'courseid' => $courseid));
        if (!$enrolid) {
            throw new ExpectationException('The course with courseid '.$courseid.' has not guest enrollment', $session);
        }
        // Add status 0 for guest user.
        $record = new stdClass();
        $record->id = $enrolid;
        $record->status = 0;
        $DB->update_record('enrol', $record);
    }

    /**
     * Test Headline.
     *
     * @Given /^I test Headline view "(?P<shortname_string>(?:[^"]|\\")*)"$/
     * @param string $shortname
     * @param TableNode $pages
     * @return void
     */
    public function i_test_headline_view($shortname, TableNode $pages) {
        /** @var behat_admin $admincontext */
        $admincontext = behat_context_helper::get('behat_admin');
        $this->courseshortname = $shortname;
        // Get themes and the course format for each one.
        $themes = array();
        $templates = array();
        foreach ($pages->getHash() as $elementdata) {
            if ($elementdata['type'] == 'template') {
                $templates[$elementdata['theme']] = explode(',', $elementdata['formats']);
            } else {
                $themes[$elementdata['theme']] = explode(',', $elementdata['formats']);
            }
        }
        // Test themes.
        foreach ($themes as $theme => $formats) {
            $this->local_xray_test_headline_themes($theme, $formats, $shortname);
        }

        // Test express theme only when present.
        $plugins = \core_plugin_manager::instance()->get_installed_plugins('theme');
        if (in_array('express', $plugins)) {
            // Test express templates.
            // Add express template.
            if (get_config('core', 'theme') != 'express') {
                $table = new \Behat\Gherkin\Node\TableNode([['theme', 'express']]);
                $admincontext->the_following_config_values_are_set_as_admin($table);
            }
            foreach ($templates as $template => $formats) {
                $this->local_xray_test_headline_themes($template, $formats, $shortname, true);
            }
        }
    }

    /**
     * @param $theme
     * @param $formats
     * @param $shortname
     * @param bool|false $template
     * @return void
     */
    private function local_xray_test_headline_themes($theme, $formats, $shortname, $template = false) {
        /** @var behat_general $generalcontext */
        $generalcontext = behat_context_helper::get('behat_general');
        /** @var behat_admin $admincontext */
        $admincontext = behat_context_helper::get('behat_admin');

        if ($template) {
            // Express theme should be activated for this option.
            $this->i_use_express_template_for_xray($theme);
        } else {
            // Add theme.
            $table = new \Behat\Gherkin\Node\TableNode([['theme', $theme]]);
            $admincontext->the_following_config_values_are_set_as_admin($table);
        }

        // Tests formats.
        foreach ($formats as $format) {
            $this->i_set_course_format_in_course_for_xray($format, $shortname);
            $generalcontext->reload();
            $generalcontext->wait_until_the_page_is_ready();
            $this->headline_elements(true);
        }
    }

    /**
     * See all Headline Elements
     *
     * @param bool $positive
     */
    private function headline_elements ($positive) {
        /** @var behat_general $generalcontext */
        $generalcontext = behat_context_helper::get('behat_general');
        if ($positive) {// Test headline is present.
            $generalcontext->should_exist("#xray-nav-headline", "css_element");
            $generalcontext->should_exist("img.x-ray-icon-title", "css_element");
            $generalcontext->should_exist("#xray-headline-risk div.xray-headline-number", "css_element");
            $generalcontext->should_exist("#xray-headline-activity div.xray-headline-number", "css_element");
            $generalcontext->should_exist("#xray-headline-gradebook div.xray-headline-number", "css_element");
            $generalcontext->should_exist("#xray-headline-discussion div.xray-headline-number", "css_element");
        } else { // Test headline is not present.
            $generalcontext->should_not_exist("#xray-nav-headline", "css_element");
            $generalcontext->should_not_exist("img.x-ray-icon-title", "css_element");
            $generalcontext->should_not_exist("#xray-headline-risk div.xray-headline-number", "css_element");
            $generalcontext->should_not_exist("#xray-headline-activity div.xray-headline-number", "css_element");
            $generalcontext->should_not_exist("#xray-headline-gradebook div.xray-headline-number", "css_element");
            $generalcontext->should_not_exist("#xray-headline-discussion div.xray-headline-number", "css_element");
        }
    }

    /**
     * Change course format.
     *
     * @Given /^I set course format "(?P<format_string>(?:[^"]|\\")*)" in course "(?P<shortname_string>(?:[^"]|\\")*)" for xray$/
     * @param  string $format
     * @param  string $shortname
     * @return void
     * @throws ExpectationException
     */
    public function i_set_course_format_in_course_for_xray($format, $shortname) {
        global $DB;
        $session = $this->getSession();
        // Get course id.
        $courseid = $DB->get_field('course', 'id', array('shortname' => $shortname));
        if (!$courseid) {
            throw new ExpectationException('The course with shortname '.$shortname.' does not exist', $session);
        }
        // Add format.
        $record = new stdClass();
        $record->id = $courseid;
        $record->format = $format;
        $DB->update_record('course', $record);
    }

    /**
     * Test the info message when the email feature are turned off.
     *
     * @Given /^Xray email alerts are turned off$/
     * @return void
     */
    public function xray_email_alerts_are_turned_off() {
        /** @var behat_admin $admincontext */
        $admincontext = behat_context_helper::get('behat_admin');
        /** @var behat_general $generalcontext */
        $generalcontext = behat_context_helper::get('behat_general');

        $table = new \Behat\Gherkin\Node\TableNode([['emailfrequency', 'weekly', 'local_xray']]);
        $admincontext->the_following_config_values_are_set_as_admin($table);
        $generalcontext->reload();
        $generalcontext->should_not_exist('.alert.alert-info', 'css_element');
        $generalcontext->assert_page_not_contains_text(get_string('emailsdisabled', 'local_xray'));
        $table = new \Behat\Gherkin\Node\TableNode([['emailfrequency', 'never', 'local_xray']]);
        $admincontext->the_following_config_values_are_set_as_admin($table);
        $generalcontext->reload();
        $generalcontext->should_exist('.alert.alert-info', 'css_element');
        $generalcontext->assert_page_contains_text(get_string('emailsdisabled', 'local_xray'));
        $table = new \Behat\Gherkin\Node\TableNode([['emailfrequency', 'daily', 'local_xray']]);
        $admincontext->the_following_config_values_are_set_as_admin($table);
        $generalcontext->reload();
        $generalcontext->should_not_exist('.alert.alert-info', 'css_element');
        $generalcontext->assert_page_not_contains_text(get_string('emailsdisabled', 'local_xray'));
    }

    /**
     * Change Global Subscription
     *
     * @Given /^Global subscription type is changed to "(?P<type_string>(?:[^"]|\\")*)" by "(?P<username_string>(?:[^"]|\\")*)"$/
     * @param string $type courselevel/all/none
     * @param string $username
     * @return void
     * @throws ExpectationException
     */
    public function xray_global_subscription($type, $username) {
        global $DB;
        $session = $this->getSession();

        if ($userid = $DB->get_field('user', 'id', array('username' => $username), IGNORE_MULTIPLE)) {
            switch ($type) {
                case "courselevel":
                    $type = 0;
                    break;
                case "all":
                    $type = 1;
                    break;
                case "none":
                    $type = 2;
                    break;
                default:
                    throw new ExpectationException('Invalid type ' . $type, $session);
            }

            $data = new stdClass();
            $data->userid = $userid;
            $data->type = $type;
            if ($currentvalue = $DB->get_record('local_xray_globalsub', array('userid' => $userid), 'id, type', IGNORE_MULTIPLE)) {
                $data->id = $currentvalue->id;
                $DB->update_record('local_xray_globalsub', $data);
            } else {
                $DB->insert_record('local_xray_globalsub', $data);
            }
        } else {
            throw new ExpectationException("The username " . $username . " doesn't exist", $session);
        }
    }

    /**
     * Expands a category in course selection
     *
     * @Given /^I expand the category "(?P<categoryname_string>(?:[^"]|\\")*)"$/
     * @param string $categoryname Category name
     * @throws ExpectationException
     * return void
     */
    public function i_expand_the_category($categoryname) {
        $session = $this->getSession();
        $page = $session->getPage();
        $findname = $page->findButton($categoryname);
        if (!$findname) {
            throw new ExpectationException($categoryname . " could not be found", $session);
        } else {
            $findname->click();
        }
    }

    /**
     * Checks the courses checkbox
     *
     * @Given /^I check the course "(?P<courseshortname_string>(?:[^"]|\\")*)"$/
     * @param string $courseshortname Course short name
     * @throws ExpectationException
     * return void
     */
    public function i_check_the_course($courseshortname) {
        global $DB;
        $session = $this->getSession();
        $page = $session->getPage();
        $courseid = $DB->get_field('course', 'id', array('shortname' => $courseshortname));
        $coursecheckbox = $page->findField('id_courses_'.$courseid);
        if (!$coursecheckbox) {
            throw new ExpectationException($courseshortname . " could not be found", $session);
        } else {
            $coursecheckbox->check();
        }
    }

    /**
     * Reviews is category is checked
     *
     * @Given /^I should see category "(?P<categoryname_string>(?:[^"]|\\")*)" checked$/
     * @param string $categoryname Category name
     * @throws ExpectationException
     * return void
     */
    public function i_should_see_category_checked($categoryname) {
        global $DB;
        $session = $this->getSession();
        $page = $session->getPage();
        $categoryid = $DB->get_field('course_categories', 'id', array('name' => $categoryname));
        $checkbox = $page->findField('cat_'.$categoryid);
        if (!$checkbox) {
            throw new ExpectationException($categoryname . " checkbox could not be found", $session);
        } else if (!$checkbox->isChecked()) {
            throw new ExpectationException($categoryname . " checkbox is not checked", $session);
        }
    }

    /**
     * Reviews if course is checked
     *
     * @Given /^I should see course "(?P<courseshortname_string>(?:[^"]|\\")*)" checked$/
     * @param string $courseshortname Course short name
     * @throws ExpectationException
     * return void
     */
    public function i_should_see_course_checked($courseshortname) {
        global $DB;
        $session = $this->getSession();
        $page = $session->getPage();
        $courseid = $DB->get_field('course', 'id', array('shortname' => $courseshortname));
        $coursecheckbox = $page->findField('id_courses_'.$courseid);
        if (!$coursecheckbox) {
            throw new ExpectationException($courseshortname . " checkbox could not be found", $session);
        } else if (!$coursecheckbox->isChecked()) {
            throw new ExpectationException($courseshortname . " checkbox is not checked", $session);
        }
    }
}
