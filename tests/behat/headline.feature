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
# The headline data should be present in the course page.
#
# @package    local_xray
# @author     German Vitale
# @copyright  Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_xray @local_xray_headline
Feature: The headline data should be present in the course page for manager, editingteacher and teacher.

  Background:
    Given the following config values are set as admin:
      | instanceid | 1 | local_xray |
      | xrayurl | http://xrayurltest.com | local_xray |
      | xrayusername | xrayuser@test.com | local_xray |
      | xraypassword | xraypass | local_xray |
      | xrayclientid | datapushdemo | local_xray |
      | displaymenu | 1 | local_xray |
    And the following config values are set as admin:
      | theme | clean |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | teacher2 | Teacher | 2 | teacher2@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Xray Course 01 | xray1 | weeks |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | xray1 | editingteacher |
      | teacher2 | xray1 | teacher |
      | student1 | xray1 | student |



  @javascript
  Scenario: Headline is displayed.
    Given I log in as "teacher1"
    And I am on site homepage
    And I follow "Xray Course 01"
    And I wait until the page is ready
    And I test Headline view "xray1"
    Then the following config values are set as admin:
      | theme | clean |
    And I set course format "weeks" in course "xray1" for xray
    And I click on "#xray-headline-risk p.xray-headline-number" "css_element"
    And I wait until the page is ready
    Then "#xray-nav-headline" "css_element" should not exist
    And "h4 .x-ray-icon-title" "css_element" should not exist
    And "h2.xray-report-page-title" "css_element" should exist
    And "#table_riskMeasures" "css_element" should exist
    And ".sorting:nth-child(6).sorting_desc" "css_element" should exist
    And I am on site homepage
    And I follow "Xray Course 01"
    And I wait until the page is ready
    And I click on "#xray-headline-activity p.xray-headline-number" "css_element"
    And I wait until the page is ready
    Then "#xray-nav-headline" "css_element" should not exist
    And "h4 .x-ray-icon-title" "css_element" should not exist
    And "h2.xray-report-page-title" "css_element" should exist
    And "#table_studentList" "css_element" should exist
    And ".sorting:nth-child(4).sorting_desc" "css_element" should exist
    And I am on site homepage
    And I follow "Xray Course 01"
    And I wait until the page is ready
    And I click on "#xray-headline-discussion p.xray-headline-number" "css_element"
    And I wait until the page is ready
    Then "#xray-nav-headline" "css_element" should not exist
    And "h4 .x-ray-icon-title" "css_element" should not exist
    And "h2.xray-report-page-title" "css_element" should exist
    And "#table_discussionMetrics" "css_element" should exist
    And ".sorting:nth-child(5).sorting_desc" "css_element" should exist
    And I am on site homepage
    And I follow "Xray Course 01"
    And I wait until the page is ready
    And I click on "#xray-headline-gradebook p.xray-headline-number" "css_element"
    And I wait until the page is ready
    Then "#xray-nav-headline" "css_element" should not exist
    And "h4 .x-ray-icon-title" "css_element" should not exist
    And "#table_element2" "css_element" should exist
    # This not will be implemented in headline
    # And ".sorting:nth-child(4).sorting_desc" "css_element" should exist

  @javascript
  Scenario: Headline is not displayed for students.
    Given I log in as "student1"
    And I am on site homepage
    And I follow "Xray Course 01"
    And I wait until the page is ready
    Then "#xray-nav-headline" "css_element" should not exist
    And "h4 .x-ray-icon-title" "css_element" should not exist
    And "#xray-headline-risk p.xray-headline-number" "css_element" should not exist
    And "#xray-headline-activity p.xray-headline-number" "css_element" should not exist
    And "#xray-headline-gradebook p.xray-headline-number" "css_element" should not exist
    And "#xray-headline-discussion p.xray-headline-number" "css_element" should not exist

  @javascript
  Scenario: Headline conection error and disabled menu.
    Given the following config values are set as admin:
      | xrayclientid | error | local_xray |
    And I log in as "admin"
    And I am on site homepage
    And I follow "Xray Course 01"
    And I wait until the page is ready
    Then ".xray-headline-errortoconnect" "css_element" should exist
    And "h4 .x-ray-icon-title" "css_element" should exist
    And "#xray-nav-headline" "css_element" should not exist
    Then the following config values are set as admin:
      | xrayclientid | datapushdemo | local_xray |
    And I reload the page
    Then ".xray-headline-errortoconnect" "css_element" should not exist
    And "h4 .x-ray-icon-title" "css_element" should exist
    And "#xray-nav-headline" "css_element" should exist
    Then the following config values are set as admin:
      | displaymenu | 0 | local_xray |
    And I reload the page
    Then "#xray-nav-headline" "css_element" should not exist
    And "h4 .x-ray-icon-title" "css_element" should not exist