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
# @copyright Copyright (c) 2017 Blackboard Inc.
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_xray @local_xray_risk_disabled
Feature: The risk status report should not be present in the headline data.

  Background:
    Given the following config values are set as admin:
      | instanceid | 1 | local_xray |
      | xrayurl | http://xrayurltest.com | local_xray |
      | xrayusername | xrayuser@test.com | local_xray |
      | xraypassword | xraypass | local_xray |
      | xrayclientid | datapushdemo | local_xray |
      | displaymenu | 1 | local_xray |
      | riskdisabled | 0 | local_xray |
    And the following config values are set as admin:
      | theme | clean |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Xray Course 01 | xraycourse1 | weeks |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | xraycourse1 | editingteacher |

  @javascript
  Scenario: Risk is displayed in the headline
    Given I log in as "teacher1"
    # Test the Risk report link in Headline.
    And I am on site homepage
    And I follow "Xray Course 01"
    And I wait until the page is ready
    And "#xray-headline-risk div.xray-headline-number" "css_element" should exist

  @javascript
  Scenario: Risk is not displayed in the headline
    Given the following config values are set as admin:
      | riskdisabled | 1 | local_xray |
    And I log in as "teacher1"
    # Test the Risk report link in Headline.
    And I am on site homepage
    And I follow "Xray Course 01"
    And I wait until the page is ready
    And "#xray-headline-risk div.xray-headline-number" "css_element" should not exist
