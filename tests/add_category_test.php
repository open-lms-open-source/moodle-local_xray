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
 * add_category tests
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\tests;

defined('MOODLE_INTERNAL') || die();

/**
 * Test add_category method
 *
 * @author    German Vitale
 * @package   local_xray
 * @group     local_xray
 * @group     local_xray_add_category
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_add_category_testcase extends \advanced_testcase {

    /**
     * Setup.
     */
    public function setUp() {
        $this->resetAfterTest(true);
        global $CFG;
        /* @noinspection PhpIncludeInspection */
        require_once($CFG->dirroot.'/local/xray/controller/reports.php');
    }

    /**
     * Test red color with low category
     */
    public function test_red_color () {
        $result = \local_xray_controller_reports::add_category(10, 'red');
        $this->assertEquals('<span class="label label-danger">'.get_string('low', 'local_xray').'</span> 10', $result);
    }

    /**
     * Test yellow color with medium category
     */
    public function test_yellow_color () {
        $result = \local_xray_controller_reports::add_category(10, 'yellow');
        $this->assertEquals('<span class="label label-warning">'.get_string('medium', 'local_xray').'</span> 10', $result);
    }

    /**
     * Test green color with high category
     */
    public function test_green_color () {
        $result = \local_xray_controller_reports::add_category(10, 'green');
        $this->assertEquals('<span class="label label-success">'.get_string('high', 'local_xray').'</span> 10', $result);
    }

    /**
     * Test red color with low category with the percentage sign
     */
    public function test_red_color_percentage () {
        $result = \local_xray_controller_reports::add_category(10, 'red', true);
        $this->assertEquals('<span class="label label-danger">'.get_string('low', 'local_xray').'</span> 10%', $result);
    }

    /**
     * Test yellow color with medium category with the percentage sign
     */
    public function test_yellow_color_percentage () {
        $result = \local_xray_controller_reports::add_category(10, 'yellow', true);
        $this->assertEquals('<span class="label label-warning">'.get_string('medium', 'local_xray').'</span> 10%', $result);
    }

    /**
     * Test green color with high category with the percentage sign
     */
    public function test_green_color_percentage () {
        $result = \local_xray_controller_reports::add_category(10, 'green', true);
        $this->assertEquals('<span class="label label-success">'.get_string('high', 'local_xray').'</span> 10%', $result);
    }

    /**
     * Test red color with low category with a regularity value
     */
    public function test_red_color_regularity () {
        $result = \local_xray_controller_reports::add_category(10, 'red', false, true);
        $this->assertEquals('<span class="label label-danger">'.get_string('irregular', 'local_xray').'</span> 10', $result);
    }

    /**
     * Test yellow color with medium category with a regularity value
     */
    public function test_yellow_color_regularity () {
        $result = \local_xray_controller_reports::add_category(10, 'yellow', false, true);
        $this->assertEquals('<span class="label label-warning">'.get_string('regular', 'local_xray').'</span> 10', $result);
    }

    /**
     * Test green color with high category with a regularity value
     */
    public function test_green_color_regularity () {
        $result = \local_xray_controller_reports::add_category(10, 'green', false, true);
        $this->assertEquals('<span class="label label-success">'.get_string('highlyregular', 'local_xray').'</span> 10', $result);
    }

    /**
     * Test red color with low category with a regularity value and the percentage sign
     */
    public function test_red_color_regularity_percentage () {
        $result = \local_xray_controller_reports::add_category(10, 'red', true, true);
        $this->assertEquals('<span class="label label-danger">'.get_string('irregular', 'local_xray').'</span> 10%', $result);
    }

    /**
     * Test yellow color with medium category with a regularity value and the percentage sign
     */
    public function test_yellow_color_regularity_percentage () {
        $result = \local_xray_controller_reports::add_category(10, 'yellow', true, true);
        $this->assertEquals('<span class="label label-warning">'.get_string('regular', 'local_xray').'</span> 10%', $result);
    }

    /**
     * Test green color with high category with a regularity value and the percentage sign
     */
    public function test_green_color_regularity_percentage () {
        $result = \local_xray_controller_reports::add_category(10, 'green', true, true);
        $this->assertEquals('<span class="label label-success">'.get_string('highlyregular', 'local_xray').'</span> 10%', $result);
    }

    /**
     * Test red color with inverted categories
     */
    public function test_red_color_inverted () {
        $result = \local_xray_controller_reports::add_category(10, 'red', false, false, true);
        $this->assertEquals('<span class="label label-danger">'.get_string('high', 'local_xray').'</span> 10', $result);
    }

    /**
     * Test yellow color with inverted categories
     */
    public function test_yellow_color_inverted () {
        $result = \local_xray_controller_reports::add_category(10, 'yellow', false, false, true);
        $this->assertEquals('<span class="label label-warning">'.get_string('medium', 'local_xray').'</span> 10', $result);
    }

    /**
     * Test green color with inverted categories
     */
    public function test_green_color_inverted () {
        $result = \local_xray_controller_reports::add_category(10, 'green', false, false, true);
        $this->assertEquals('<span class="label label-success">'.get_string('low', 'local_xray').'</span> 10', $result);
    }

    /**
     * Test red color with inverted categories and the percentage sign
     */
    public function test_red_color_inverted_percentage () {
        $result = \local_xray_controller_reports::add_category(10, 'red', true, false, true);
        $this->assertEquals('<span class="label label-danger">'.get_string('high', 'local_xray').'</span> 10%', $result);
    }

    /**
     * Test yellow color with inverted categories and the percentage sign
     */
    public function test_yellow_color_inverted_percentage () {
        $result = \local_xray_controller_reports::add_category(10, 'yellow', true, false, true);
        $this->assertEquals('<span class="label label-warning">'.get_string('medium', 'local_xray').'</span> 10%', $result);
    }

    /**
     * Test green color with inverted categories and the percentage sign
     */
    public function test_green_color_inverted_percentage () {
        $result = \local_xray_controller_reports::add_category(10, 'green', true, false, true);
        $this->assertEquals('<span class="label label-success">'.get_string('low', 'local_xray').'</span> 10%', $result);
    }

    /**
     * Test red color with inverted categories with a regularity value
     */
    public function test_red_color_regularity_inverted () {
        $result = \local_xray_controller_reports::add_category(10, 'red', false, true, true);
        // The regularity categories cannot be inverted. In this case, it should return the default regularity categories.
        $this->assertEquals('<span class="label label-danger">'.get_string('irregular', 'local_xray').'</span> 10', $result);
    }

    /**
     * Test yellow color with inverted categories with a regularity value
     */
    public function test_yellow_color_regularity_inverted () {
        $result = \local_xray_controller_reports::add_category(10, 'yellow', false, true, true);
        // The regularity categories cannot be inverted. In this case, it should return the default regularity categories.
        $this->assertEquals('<span class="label label-warning">'.get_string('regular', 'local_xray').'</span> 10', $result);
    }

    /**
     * Test green color with inverted categories with a regularity value
     */
    public function test_green_color_regularity_inverted () {
        $result = \local_xray_controller_reports::add_category(10, 'green', false, true, true);
        // The regularity categories cannot be inverted. In this case, it should return the default regularity categories.
        $this->assertEquals('<span class="label label-success">'.get_string('highlyregular', 'local_xray').'</span> 10', $result);
    }

    /**
     * Test a string value.
     */
    public function test_string_value() {
        $result = \local_xray_controller_reports::add_category('break', 'red');
        $this->assertSame('-', $result);
    }

    /**
     * Test an empty value.
     */
    public function test_empty_value() {
        $result = \local_xray_controller_reports::add_category('', 'red');
        $this->assertSame('-', $result);
    }

    /**
     * Test a null value.
     */
    public function test_null_value() {
        $result = \local_xray_controller_reports::add_category(null, 'red');
        $this->assertSame('-', $result);
    }
}
