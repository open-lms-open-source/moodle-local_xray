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
 * Steps definitions for behat theme.
 *
 * @package   local_xray
 * @category  test
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given,
    Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Behat Local Xray
 *
 * @package   local_xray
 * @category  test
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
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
        require_once("$CFG->dirroot/blocks/express/model/design.php");

        // Create express paths.
        mkdir("$CFG->behat_dataroot/express/");
        mkdir("$CFG->behat_dataroot/express/tmp/");

        // Add design at site context.
        $context_course = context_course::instance(SITEID);
        $parentcontextid = $context_course->id;
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
     * @Given /^I test Headline view "(?P<shortname_string>(?:[^"]|\\")*)" "(?P<view_string>[^"]*)"$/
     * @param string $shortname
     * @return void
     */
    public function i_test_headline_view($shortname, $view) {
        global $DB;
        $this->courseshortname = $shortname;
        // Check if the headline shoould be displayed.
        $positive = true;
        if ($view == 'notdisplayed') {
            $positive = false;
        }
        // Add themes and the course format for each one.
        $themes = array();
        $themes['clean'] = array('topics', 'folderview', 'onetopic', 'social', 'topcoll');
        $themes['more'] = array('topics', 'folderview', 'onetopic', 'social', 'topcoll');
        $themes['snap'] = array('topics');
        $themes['express'] = array('topics', 'flexpage', 'folderview', 'onetopic', 'social', 'topcoll');
        // Add express templates and the course format for each one.
        $templates = array();
        $templates['minimal'] = array('topics', 'folderview', 'onetopic', 'social', 'topcoll');
        $templates['cherub'] = array('topics', 'flexpage', 'folderview', 'onetopic', 'social', 'topcoll');
        $templates['dropshadow'] = array('topics', 'flexpage', 'folderview', 'onetopic', 'social', 'topcoll');
        $templates['future'] = array('topics', 'flexpage', 'folderview', 'onetopic', 'social', 'topcoll');
        $templates['joule'] = array('topics', 'flexpage', 'folderview', 'onetopic', 'social', 'topcoll');
        $templates['simple'] = array('topics', 'flexpage', 'folderview', 'onetopic', 'social', 'topcoll');
        $templates['sleek'] = array('topics', 'flexpage', 'folderview', 'onetopic', 'social', 'topcoll');
        $templates['topslide'] = array('topics', 'flexpage', 'folderview', 'onetopic', 'social', 'topcoll');

        $steps = array();
        // Test default theme clean and default week format.
        $steps1 = $this->local_xray_test_headline_themes('clean', $themes['clean'], $shortname, $positive);
        if ($positive) {
            // Test the other themes.
            foreach ($themes as $theme => $formats) {
                $steps2 = $this->local_xray_test_headline_themes($theme, $formats, $shortname, $positive);
            }
            // Test express templates.
            foreach ($templates as $template => $formats) {
                $steps3 = $this->local_xray_test_headline_themes($template, $formats, $shortname, $positive, false, true);
            }
            $steps = array_merge($steps1, $steps2, $steps3);
        } else {
            $steps = $steps1;
        }
        return $steps;
    }

    /**
     * @param $theme
     * @param $formats
     * @param $shortname
     * @param bool|true $positive
     * @param bool|false $default
     * @param bool|false $template
     * @return array
     */
    private function local_xray_test_headline_themes($theme, $formats, $shortname, $positive = true, $default = false, $template = false) {
        if (!$default){
            if ($template) {
                // Express theme should be activated for this option.
                $steps[] = new Given('I use express template "'.$theme.'" for xray');
            } else {
                // Add theme.
                $table = new \Behat\Gherkin\Node\TableNode("| theme | $theme |");
                $steps[] = new Given('the following config values are set as admin:', $table);
            }
            // Add format weeks.
            $steps[] = new Given('I set course format "weeks" in course "'.$shortname.'" for xray');
        }
        if ($positive) {
            // Test headline is present.
            $steps[] = new Given('"#xray-nav-headline" "css_element" should exist');
            $steps[] = new Given('"h4 .x-ray-icon-title" "css_element" should exist');
        } else {
            // Test headline is not present.
            $steps[] = new Given('"#xray-nav-headline" "css_element" should not exist');
            $steps[] = new Given('"h4 .x-ray-icon-title" "css_element" should not exist');
        }
        // Tests theme clean with the other formats.
        foreach ($formats as $format) {
            $steps[] = new Given('I set course format "'.$format.'" in course "'.$shortname.'" for xray');
            $steps[] = new Given('I reload the page');
            $steps[] = new Given('I wait until the page is ready');
            if ($positive) {
                $steps[] = new Given('"#xray-nav-headline" "css_element" should exist');
                $steps[] = new Given('"h4 .x-ray-icon-title" "css_element" should exist');
                $steps[] = new Given('I click on "#xray-headline-risk p.xray-headline-number" "css_element"');
                $steps[] = new Given('I wait until the page is ready');
                $steps[] = new Given('"#xray-nav-headline" "css_element" should not exist');
                $steps[] = new Given('"h4 .x-ray-icon-title" "css_element" should not exist');
                $steps[] = new Given('"h2.xray-report-page-title" "css_element" should exist');
                $steps[] = new Given('"#table_riskMeasures" "css_element" should exist');
                $steps[] = new Given('".sorting:nth-child(6).sorting_desc" "css_element" should exist');
                $steps[] = new Given('I scroll until "'.$this->courseshortname.'" "text" is visible');
                $steps[] = new Given('I follow "'.$this->courseshortname.'"');
                $steps[] = new Given('I wait until the page is ready');
            } else {
                $steps[] = new Given('"#xray-nav-headline" "css_element" should not exist');
                $steps[] = new Given('"h4 .x-ray-icon-title" "css_element" should not exist');
            }
        }
        return $steps;
    }

    /**
     * Change course format.
     *
     * @Given /^I set course format "(?P<format_string>(?:[^"]|\\")*)" in course "(?P<shortname_string>(?:[^"]|\\")*)" for xray$/
     * @param string $shortname
     * @return void
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
}
