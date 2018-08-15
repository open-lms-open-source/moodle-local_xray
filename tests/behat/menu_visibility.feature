# This file is part of Moodle - http://moodle.org/
#
# Moodle is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Moodle is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
#
# Check if menu xray is available in courses.
#
# @package    local_xray
# @author     Pablo Pagnone
# @copyright  Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_xray @local_xray_menu_visibility
Feature: The tree menu xray will visible or not in each place.

  Background:
    Given the following config values are set as admin:
      | xrayurl      | http://xrayurltest.com | local_xray |
      | xrayusername | xrayuser@test.com      | local_xray |
      | xraypassword | xraypass               | local_xray |
      | xrayclientid | testclient             | local_xray |
      | displaymenu  | 1                      | local_xray |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course1  | C1        | weeks  |
    And the following "activities" exist:
      | activity   | name            | intro      | type   | course | idnumber     |
      | forum      | Forum 1 test    | intro test | general| C1     | forum1       |
    And the following "users" exist:
      | username     | firstname | lastname | email |
      | user1student | user1 | student  | user1@example.com |
      | user2teacher | user2 | teacher  | user2@example.com |
    And the following "course enrolments" exist:
      | user         | course | role           |
      | user1student | C1     | student        |
      | user2teacher | C1     | editingteacher |

  @javascript @local_xray_menu_visibility_courses_format_teacher
  Scenario Outline: Menu xray will visible for teachers in all course with all formats, except format "singleactivity".
    Given I set course format "<format>" in course "C1" for xray
    And I log in as "user2teacher"
    And I am on site homepage
    And I am on "Course1" course homepage
    And I navigate to "More..." in current page administration
    Then I <vis> see "X-Ray Learning Analytic" in the "region-main" "region"
  Examples:
  | format         | vis        |
  | singleactivity | should not |
  | weeks          | should     |

  @javascript @local_xray_menu_visibility_courses_format_students
  Scenario: Menu xray will not visible for students in courses.
    Given I log in as "user1student"
    And I am on site homepage
    And I am on "Course1" course homepage
    Then "X-Ray Learning Analytics" "text" should not exist in current page administration
