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
# Global and course level subscription pages.
#
# @package    local_xray
# @author     German Vitale
# @copyright  Copyright (c) 2017 Moodlerooms Inc. (http://www.moodlerooms.com)
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_xray @local_xray_subscription
Feature: Global and course level subscription pages.

  Background:
    Given the following config values are set as admin:
      | instanceid | 1 | local_xray |
      | xrayurl | http://xrayurltest.com | local_xray |
      | xrayusername | xrayuser@test.com | local_xray |
      | xraypassword | xraypass | local_xray |
      | xrayclientid | datapushdemo | local_xray |
      | displaymenu | 1 | local_xray |
      | emailfrequency | weekly | local_xray |
      | emailreport | 1 | local_xray |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Xray Course 01 | xraycourse1 | weeks |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | xraycourse1 | editingteacher |
      | student1 | xraycourse1 | student |

  @javascript
  Scenario: Teacher sees global and course level subscription.
    Given I log in as "teacher1"
    And I am on site homepage
    And I follow "Xray Course 01"
    And ".xray_subscription_link" "css_element" should exist
    And I follow "Subscribe to email report"
    And I switch to "_xray_course_subscription" window
    And Xray email alerts are turned off
    And "#id_subscribe" "css_element" should exist
    And I click on "id_subscribe" "checkbox"
    And I press "Save changes"
    And ".alert.alert-success" "css_element" should exist
    And I should see "Changes saved"
    And I switch to the main window
    And I reload the page
    And ".xray_subscription_link" "css_element" should exist
    And I should not see "Subscribe to email report"
    And I should see "Unsubscribe from email report"
    And Global subscription type is changed to "all" by "teacher1"
    And I switch to "_xray_course_subscription" window
    And I reload the page
    And the "id_subscribe" "checkbox" should be disabled
    And ".alert.alert-info" "css_element" should exist
    And I should see "Enable this setting in the X-Ray Global Subscription page. You can access this page using the link X-Ray Global Subscription from your profile."
    And Global subscription type is changed to "courselevel" by "teacher1"
    And I reload the page
    And the "id_subscribe" "checkbox" should be enabled
    And ".alert.alert-info" "css_element" should not exist
    And I should not see "Enable this setting in the X-Ray Global Subscription page. You can access this page using the link X-Ray Global Subscription from your profile."
    And Global subscription type is changed to "none" by "teacher1"
    And I reload the page
    And the "id_subscribe" "checkbox" should be disabled
    And ".alert.alert-info" "css_element" should exist
    And I should see "Enable this setting in the X-Ray Global Subscription page. You can access this page using the link X-Ray Global Subscription from your profile."

  @javascript
  Scenario: Student doesn't see global and course level subscription.
    Given I log in as "student1"
    # Course level subscription.
    And I am on site homepage
    And I follow "Xray Course 01"
    And ".xray_subscription_link" "css_element" should not exist
    And I should not see "Subscribe to email report"
    And I should not see "Unsubscribe from email report"
