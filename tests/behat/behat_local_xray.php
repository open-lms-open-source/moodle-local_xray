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

    /**
     * Logs in the user. There should exist a user with the same value as username and password.
     *
     * @Given /^I log in with express for xray as "(?P<username_string>(?:[^"]|\\")*)"$/
     * @param string $username
     * @return void
     */
    public function i_log_in_with_express_for_xray_as($username) {

        // Running this step using the API rather than a chained step because
        // we need to see if the 'Log in' link is available or we need to click
        // the dropdown to expand the navigation bar before.
        $this->getSession()->visit($this->locate_path('/'));

        // Generic steps (we will prefix them later expanding the navigation dropdown if necessary).
        $steps = array(
            new Given('I click on "#thehandle" "css_element"'),
            new Given('I wait until "#newregions" "css_element" is visible'),
            new Given('I set the field "' . get_string('username') . '" to "' . $this->escape($username) . '"'),
            new Given('I set the field "' . get_string('password') . '" to "'. $this->escape($username) . '"'),
            new Given('I press "' . get_string('login', 'theme_express') . '"')
        );

        // If Javascript is disabled we have enough with these steps.
        if (!$this->running_javascript()) {
            return $steps;
        }

        // Wait for the homepage to be ready.
        $this->getSession()->wait(self::TIMEOUT * 1000, self::PAGE_READY_JS);

        return $steps;
    }

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
        mkdir('/var/jouledata/gabo/behat_moodledata/express/');
        mkdir('/var/jouledata/gabo/behat_moodledata/express/tmp/');

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
        $design->save($data); // TODO It is necesary?
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
        // Get course id.
        $courseid = $DB->get_field('course', 'id', array('shortname' => $shortname));
        // Get enrol id for guest user.
        $enrolid = $DB->get_field('enrol', 'id', array('enrol' => 'guest', 'courseid' => $courseid));
        // Add status 0 for guest user.
        $record = new stdClass();
        $record->id = $enrolid;
        $record->status = 0;
        $DB->update_record('enrol', $record);
    }
}
