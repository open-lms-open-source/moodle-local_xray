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
# Check if link of report Discussion Individual forum is available in menu xray.
#
# @package    local_xray
# @author     Pablo Pagnone
# @copyright  Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_xray @local_xray_menu_discussionindividualforum
Feature: The menu xray with link to Discussion Report Individual Forum should be present in forum view-page /hsforum
  view-page and in discussions view-page.

  Background:
    Given the following config values are set as admin:
      | xrayurl | http://xrayurltest.com | local_xray |
      | xrayusername | xrayuser@test.com | local_xray |
      | xraypassword | xraypass | local_xray |
      | xrayclientid | testclient | local_xray |
      | displaymenu | 1 | local_xray |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course1 | C1 | topics |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | user1student | user1 | student  | user1@example.com |
      | user2teacher | user2 | teacher  | user2@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | user1student | C1 | student |
      | user2teacher | C1 | editingteacher |

  @javascript @local_xray_menu_discussionindividualforum_forumgeneral
  Scenario Outline: Menu xray with link to Discussion Report Individual Forum is displayed in forum view-page (type "general"),
  hsforum view-page (type "general") and in discussions view-page.
    Given the following "activities" exist:
      | activity   | name            | intro      | type    | course | idnumber     |
      | forum      | Forum 1 test    | intro test | general | C1     | forum1       |
      | hsuforum   | HSUForum 1 test | intro test | general | C1     | hsforum1     |
    # I create posts for forums.
    And I log in as "admin"
    And I am on site homepage
    And I follow "Course1"
    And I add a new discussion to "Forum 1 test" forum with:
      | Subject | Discussion of Forum 1 |
      | Message | Generic Message       |
    And I follow "Course1"
    And I add a new discussion to "HSUForum 1 test" advanced forum with:
      | Subject | Discussion of HSUForum 1  |
      | Message | Generic Messag            |
    And I log out
    # Check if link is visible/not visible for x user.
    And I log in as "<user>"
    And I am on site homepage
    And I follow "Course1"
    And I follow "Forum 1 test"
    Then I should <vis> "X-Ray Learning Analytics" in the "Administration" "block"
    And I follow "Discussion of Forum 1"
    Then I should <vis> "X-Ray Learning Analytics" in the "Administration" "block"
    And I follow "Course1"
    And I follow "HSUForum 1 test"
    Then I should <vis> "X-Ray Learning Analytics" in the "Administration" "block"
    And I follow "Discussion of HSUForum 1"
    Then I should <vis> "X-Ray Learning Analytics" in the "Administration" "block"
    Examples:
    | user         | vis     |
    | user2teacher | see     |
    | user1student | not see |

  @javascript @local_xray_menu_discussionindividualforum_forumsingle
  Scenario Outline: Menu xray with link to Discussion Report Individual Forum is displayed in forum view-page and in
  discussion view-page when forum type is "single".
    Given the following "activities" exist:
      | activity   | name            | intro      | type   | course | idnumber     |
      | forum      | Forum 1 test    | intro test | single | C1     | forum1       |
      | hsuforum   | HSUForum 1 test | intro test | single | C1     | hsforum1     |
    # Check if link is visible/not visible for x user.
    And I log in as "<user>"
    And I am on site homepage
    And I follow "Course1"
    And I follow "Forum 1 test"
    Then I should <vis> "X-Ray Learning Analytics" in the "Administration" "block"
    And I follow "Course1"
    And I follow "HSUForum 1 test"
    Then I should <vis> "X-Ray Learning Analytics" in the "Administration" "block"
    Examples:
      | user         | vis     |
      | user2teacher | see     |
      | user1student | not see |

  @javascript @local_xray_menu_discussionindividualforum_quiz
  Scenario Outline: Menu xray with link to Discussion Report Individual Forum is not displayed in quiz pages.
    Given  the following "activities" exist:
      | activity   | name   | intro              | course | idnumber |
      | quiz       | Quiz1  | Quiz 1 description | C1     | quiz1    |
    And I log in as "<user>"
    And I am on site homepage
    And I follow "Course1"
    And I follow "Quiz1"
    Then I should not see "X-Ray Learning Analytics" in the "Administration" "block"
  Examples:
  | user         |
  | user1student |
  | user2teacher |

  @javascript @local_xray_menu_discussionindividualforum_posts_linkaccessible
  Scenario: Check the message when the forum has no posts and check if breadcrum is working correctly when user go to Accessible Version of some graph in Discussion Report Individual Forum.
    Given the following "activities" exist:
      | activity   | name      | intro      | type     | course | idnumber     |
      | forum      | Forum1    | intro test | general  | C1     | forum1       |
    # I create posts for the forum.
    And I log in as "admin"
    And I am on site homepage
    And I follow "Course1"
    And I follow "Forum1"
    And I navigate to "Discussion Report Individual Forum" node in "Forum administration > X-Ray Learning Analytics"
    Then I should see "There is not enough data for this report. Please try again when there is more user activity in your course."
    And "a.xray-graph-box-link" "css_element" should not exist
    And I follow "Course1"
    And I add a new discussion to "Forum1" forum with:
      | Subject | Discussion of Forum1 |
      | Message | Generic Message       |
    And I follow "Course1"
    And I follow "Forum1"
    And I navigate to "Discussion Report Individual Forum" node in "Forum administration > X-Ray Learning Analytics"
    And "a.xray-graph-box-link" "css_element" should exist
    # Open graph.
    And I click on "a.xray-graph-box-link" "css_element"
    # Close graph.
    And I click on "a.xray-close-link" "css_element"
    # Go to Accessible data.
    And I click on "a.xray-accessible-view-data" "css_element"
    And I switch to "_accessibledata" window
    And I wait until the page is ready
    And I follow visible link "Discussion Report Individual Forum"
    And I wait "3" seconds
    Then I should see "Discussion Report Individual Forum" in the "#region-main" "css_element"