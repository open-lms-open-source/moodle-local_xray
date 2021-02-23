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
 * PDF methods tests.
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\tests;

defined('MOODLE_INTERNAL') || die();

/**
 * Test PDF methods.
 *
 * @author    German Vitale
 * @package   local_xray
 * @group     local_xray
 * @group     local_xray_pdf
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xray_pdf_testcase extends \advanced_testcase {

    /**
     * Setup.
     */
    public function setUp() {
        global $CFG;
        require_once($CFG->dirroot.'/local/xray/locallib.php');
        require_once($CFG->libdir.'/pdflib.php');
    }

    /**
     * Test if the result is instance of PDF class.
     */
    public function test_create_pdf () {
        // Create headline data.
        $data = new \stdClass();
        $data->riskiconpdf = 'Some text';
        $data->riskicon = 'Some text';
        $data->risklink = 'Some text';
        $data->riskarrowpdf = 'Some text';
        $data->riskarrow = 'Some text';
        $data->riskdatapdf = 'Some text';
        $data->riskdata = 'Some text';
        $data->studentsrisk = 'Some text';
        $data->riskaverageweek = 'Some text';
        $data->activityiconpdf = 'Some text';
        $data->activityicon = 'Some text';
        $data->activitylink = 'Some text';
        $data->activityarrowpdf = 'Some text';
        $data->activityarrow = 'Some text';
        $data->activitydatapdf = 'Some text';
        $data->activitydata = 'Some text';
        $data->activityloggedstudents = 'Some text';
        $data->activitylastweekwasof = 'Some text';
        $data->gradebookiconpdf = 'Some text';
        $data->gradebookicon = 'Some text';
        $data->gradebooklink = 'Some text';
        $data->gradebookarrowpdf = 'Some text';
        $data->gradebookarrow = 'Some text';
        $data->gradebooknumberpdf = 'Some text';
        $data->gradebooknumber = 'Some text';
        $data->gradebookheadline = 'Some text';
        $data->gradebookaverageofweek = 'Some text';
        $data->discussioniconpdf = 'Some text';
        $data->discussionicon = 'Some text';
        $data->discussionlink = 'Some text';
        $data->discussionarrowpdf = 'Some text';
        $data->discussionarrow = 'Some text';
        $data->discussiondatapdf = 'Some text';
        $data->discussiondata = 'Some text';
        $data->discussionposts = 'Some text';
        $data->discussionlastweekwas = 'Some text';
        $data->recommendations = true;
        $data->recommendationslist = 'Some text';
        $data->recommendationspdf = array('Recommendation 1', 'Recommendation 2');
        $data->recommendationstitle = 'Some text';
        $data->reportdate = '2016-12-26T00:00:00.000Z';
        $result = local_xray_create_pdf($data, 'Subject text');
        $this->assertInstanceOf('\pdf', $result);
    }

    /**
     * Test large recommendation.
     */
    public function test_add_recommendation_pdf_large() {

        $result = local_xray_add_recommendation_pdf(3, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. '.
            'Aenean tincidunt pretium est, in bibendum tellus varius eget. Suspendisse et rutrum dui, ut '.
            'pellentesque felis. Quisque hendrerit mi neque, vel pharetra nunc ultricies vitae. Pellentesque leo '.
            'dui, pretium at est non, faucibus tempor lorem. Proin rhoncus dui non condimentum venenatis. Duis '.
            'nec malesuada odio. Suspendisse molestie augue vel dictum finibus. Sed eleifend lacus lobortis, '.
            'accumsan arcu a, bibendum turpis. Aliquam erat volutpat. Donec consectetur hendrerit velit placerat '.
            'fringilla. Sed tincidunt volutpat enim, ut tempus lacus semper vitae. Duis nisl nunc, accumsan vel '.
            'lacus vitae, condimentum fermentum sapien. Nunc rutrum tempor metus. Praesent faucibus nulla ut '.
            'risus placerat consectetur. Integer et turpis et dolor vestibulum tempus sit amet at diam. Ut '.
            'luctus cursus rhoncus.');

        $this->assertInternalType('array', $result);
        $this->assertInstanceOf('\html_table_cell', $result[0]);
        $this->assertInstanceOf('\html_table_cell', $result[1]);
    }

    /**
     * Test int and string types.
     */
    public function test_add_recommendation_pdf_types() {

        $result = local_xray_add_recommendation_pdf('3', 8);

        $this->assertInternalType('array', $result);
        $this->assertInstanceOf('\html_table_cell', $result[0]);
        $this->assertInstanceOf('\html_table_cell', $result[1]);
    }

    /**
     * Test Rows for Report icon and title.
     */
    public function test_report_head_row() {

        $result = local_xray_report_head_row('Title', 'Icon');

        $this->assertInternalType('array', $result);
        $this->assertInstanceOf('\html_table_row', $result[0]);
        $this->assertInstanceOf('\html_table_row', $result[1]);
        $this->assertInstanceOf('\html_table_row', $result[2]);
    }
}
