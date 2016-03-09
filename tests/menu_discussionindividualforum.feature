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
      | user1student | user1 | last1 | user1@example.com |
      | user2teacher | user2 | last2 | user2@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | user1student | C1 | student |
      | user2teacher | C1 | editingteacher |

  @javascript
  Scenario: Menu xray with link to Discussion Report Individual Forum is displayed in forum view-page, hsforum view-page and in discussion view-page.
    Given the following "activities" exist:
      | activity   | name      | intro      | type     | course | idnumber     |
      | forum      | Forum1    | intro test | general  | C1     | forum1       |
      | hsuforum   | HSUForum1 | intro test | general  | C1     | hsforum1     |

    # I create posts for forums.
    And I log in as "admin"
    And I am on site homepage
    And I follow "Course1"
    And I add a new discussion to "Forum1" forum with:
      | Subject | Discussion1 |
      | Message | Discussion of Forum1 |
    And I follow "C1"
    And I add a new discussion to "HSUForum1" advanced forum with:
      | Subject | Discussion1hsu |
      | Message | Discussion of HSUForum1 |
    And I log out
    # Check if link is visible like teacher.
    And I log in as "user2teacher"
    And I am on site homepage
    And I follow "Forum1"
    And I expand "X-Ray Learning Analytics" node
    Then I should see "Discussion Report Individual Forum"
    And I follow "Discussion1"
    And I expand "X-Ray Learning Analytics" node
    Then I should see "Discussion Report Individual Forum"
    And I follow "C1"
    And I follow "HSUForum1"
    And I expand "X-Ray Learning Analytics" node
    Then I should see "Discussion Report Individual Forum"
    And I follow "Discussion1hsu"
    And I expand "X-Ray Learning Analytics" node
    Then I should see "Discussion Report Individual Forum"

    # TODO: I need to add check for forum type "single".


  @javascript
  Scenario: Menu xray with link to Discussion Report Individual Forum is not displayed in forum view-page, hsforum view-page and in discussion view-page.
    Given the following "activities" exist:
      | activity   | name      | intro      | type     | course | idnumber     |
      | forum      | Forum1    | intro test | general  | C1     | forum1       |
      | hsuforum   | HSUForum1 | intro test | general  | C1     | hsforum1     |
    # I create posts for forums.
    And I log in as "admin"
    And I am on site homepage
    And I follow "Course1"
    And I add a new discussion to "Forum1" forum with:
      | Subject | Discussion1 |
      | Message | Discussion of Forum1 |
    And I follow "C1"
    And I add a new discussion to "HSUForum1" advanced forum with:
      | Subject | Discussion1hsu |
      | Message | Discussion of HSUForum1 |
    And I log out
    # Check like student.
    And I log in as "user1student"
    And I am on site homepage
    And I follow "Course1"
    And I follow "Forum1"
    Then I should not see "X-Ray Learning Analytics"
    And I follow "Discussion1"
    Then I should not see "X-Ray Learning Analytics"
    And I follow "C1"
    And I follow "HSUForum1"
    Then I should not see "X-Ray Learning Analytics"
    And I follow "Discussion1hsu"
    Then I should not see "X-Ray Learning Analytics"

    # TODO: I need to add check for forum type "single".

  @javascript
  Scenario: Menu xray with link to Discussion Report Individual Forum is not displayed in quiz pages.
    Given  the following "activities" exist:
      | activity   | name   | intro              | course | idnumber |
      | quiz       | Quiz1  | Quiz 1 description | C1     | quiz1    |
    # Check like teacher.
    And I log in as "user2teacher"
    And I am on site homepage
    And I follow "Course1"
    And I follow "Quiz1"
    Then I should not see "X-Ray Learning Analytics"
    And I log out
    # Check like student.
    And I log in as "user1student"
    And I am on site homepage
    And I follow "Course1"
    And I follow "Quiz1"
    Then I should not see "X-Ray Learning Analytics"

  @javascript
  Scenario: Check if breadcrum is working correctly when user go to Accessible Version of some graph in Discussion Report Individual Forum.
    Given the following "activities" exist:
      | activity   | name      | intro      | type     | course | idnumber     |
      | forum      | Forum1    | intro test | general  | C1     | forum1       |
    And I log in as "user2teacher"
    And I am on site homepage
    And I follow "Course1"
    And I follow "Forum1"
    And I expand "X-Ray Learning Analytics" node
    And I follow "Discussion Report Individual Forum"
    And I follow "View Data"
    Then I should see "Discussion Report Individual Forum"
    # TODO: I need to add fixture for Discussion Report Individual Forum.