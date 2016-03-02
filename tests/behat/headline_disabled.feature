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
# Headline data is not displayed when displaymenu is turned off.
#
# @package    local_xray
# @author     German Vitale
# @copyright  Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_xray @local_xray_headline_disabled
Feature: Headline data is not displayed when displaymenu is turned off.

  Background:
    Given the following config values are set as admin:
      | instanceid | 1 | local_xray |
      | xrayurl | http://xrayurltest.com | local_xray |
      | xrayusername | xrayuser@test.com | local_xray |
      | xraypassword | xraypass | local_xray |
      | xrayclientid | datapushdemo | local_xray |
      | displaymenu | 0 | local_xray |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | teacher2 | Teacher | 2 | teacher2@example.com |
      | student1 | Student | 1 | student1@example.com |

  @javascript
  Scenario Outline: Headline data is disabled.
  Roles: manager, teacher and editingteacher.
  Themes: more, clean, flexpage.
  Course formats: weeks, topics, social, folderview, onetopic, topcoll, flexpage.

    Given the following config values are set as admin:
      | theme | <theme> |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Xray Course 01 | xray1 | <course_format> |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | xray1 | editingteacher |
      | teacher2 | xray1 | teacher |
    And I log in as <username>
    And I am on site homepage
    And I follow "Xray Course 01"
    And I wait until the page is ready
    Then "#xray-nav-headline" "css_element" should not exist
    And "h4 .x-ray-icon-title" "css_element" should not exist

    Examples:

      | username | theme | course_format |
      | "admin" | more | weeks |
      | "admin" | more | topics |
      | "admin" | more | social |
      | "admin" | more | folderview |
      | "admin" | more | onetopic |
      | "admin" | more | topcoll |
      | "admin" | more | tabbedweek |
      | "admin" | clean | weeks |
      | "admin" | clean | topics |
      | "admin" | clean | social |
      | "admin" | clean | folderview |
      | "admin" | clean | onetopic|
      | "admin" | clean | topcoll|
      | "admin" | clean | tabbedweek|
      | "admin" | flexpage | weeks |
      | "admin" | flexpage | topics |
      | "admin" | flexpage | social |
      | "admin" | flexpage | folderview |
      | "admin" | flexpage | onetopic|
      | "admin" | flexpage | topcoll|
      | "admin" | flexpage | tabbedweek|
      | "admin" | flexpage | flexpage|
      | "teacher1" | more | weeks |
      | "teacher1" | more | topics |
      | "teacher1" | more | social |
      | "teacher1" | more | folderview |
      | "teacher1" | more | onetopic|
      | "teacher1" | more | topcoll|
      | "teacher1" | more | tabbedweek|
      | "teacher1" | clean | weeks |
      | "teacher1" | clean | topics |
      | "teacher1" | clean | social |
      | "teacher1" | clean | folderview |
      | "teacher1" | clean | onetopic|
      | "teacher1" | clean | topcoll|
      | "teacher1" | clean | tabbedweek|
      | "teacher1" | flexpage | weeks |
      | "teacher1" | flexpage | topics |
      | "teacher1" | flexpage | social |
      | "teacher1" | flexpage | folderview |
      | "teacher1" | flexpage | onetopic|
      | "teacher1" | flexpage | topcoll|
      | "teacher1" | flexpage | tabbedweek|
      | "teacher1" | flexpage | flexpage|
      | "teacher2" | more | weeks |
      | "teacher2" | more | topics |
      | "teacher2" | more | social |
      | "teacher2" | more | folderview |
      | "teacher2" | more | onetopic|
      | "teacher2" | more | topcoll|
      | "teacher2" | more | tabbedweek|
      | "teacher2" | clean | weeks |
      | "teacher2" | clean | topics |
      | "teacher2" | clean | social |
      | "teacher2" | clean | folderview |
      | "teacher2" | clean | onetopic|
      | "teacher2" | clean | topcoll|
      | "teacher2" | clean | tabbedweek|
      | "teacher2" | flexpage | weeks |
      | "teacher2" | flexpage | topics |
      | "teacher2" | flexpage | social |
      | "teacher2" | flexpage | folderview |
      | "teacher2" | flexpage | onetopic|
      | "teacher2" | flexpage | topcoll|
      | "teacher2" | flexpage | tabbedweek|
      | "teacher2" | flexpage | flexpage|

  @javascript
  Scenario Outline: Headline data is disabled with snap theme.
  Roles: manager, teacher, editingteacher.
  Course formats: weeks, topics, social, folderview, onetopic, topcoll, flexpage.

    Given the following config values are set as admin:
      | theme | snap |
    And the following "courses" exist:
      | fullname | shortname | category | format |
      | Xray Course 01 | xray1 | 0 | <course_format> |
    And the following "course enrolments" exist:
      | user | course | role |
      | admin | xray1 | editingteacher |
      | teacher1 | xray1 | editingteacher |
      | teacher2 | xray1 | teacher |
    And I log in with snap as <username>
    And I am on site homepage
    And I follow "Menu"
    And I follow "Xray Course 01"
    And I wait until the page is ready
    Then "#xray-nav-headline" "css_element" should not exist
    And "h4 .x-ray-icon-title" "css_element" should not exist

    Examples:

      | username | course_format |
      | "admin" | weeks |
      | "admin" | topics |
      | "admin" | social |
      | "admin" | folderview |
      | "admin" | onetopic|
      | "admin" | topcoll|
      | "admin" | tabbedweek|
      | "admin" | flexpage|
      | "teacher1" | weeks |
      | "teacher1" | topics |
      | "teacher1" | social |
      | "teacher1" | folderview |
      | "teacher1" | onetopic|
      | "teacher1" | topcoll|
      | "teacher1" | tabbedweek|
      | "teacher1" | flexpage|
      | "teacher2" | weeks |
      | "teacher2" | topics |
      | "teacher2" | social |
      | "teacher2" | folderview |
      | "teacher2" | onetopic|
      | "teacher2" | topcoll|
      | "teacher2" | tabbedweek|
      | "teacher2" | flexpage|

  @javascript
  Scenario Outline: Headline data is disabled with express theme.
  Roles: manager, teacher, editingteacher.
  Course formats: weeks, topics, social, folderview, onetopic, topcoll, flexpage.

    Given the following config values are set as admin:
      | theme | express |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Xray Course 01 | xray1 | <course_format> |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | xray1 | editingteacher |
      | teacher2 | xray1 | teacher |

    And I log in with express for xray as <username>
    And I am on site homepage
    And I follow "Xray Course 01"
    And I wait until the page is ready
    Then "#xray-nav-headline" "css_element" should not exist
    And "h4 .x-ray-icon-title" "css_element" should not exist

    Examples:
      | username | course_format |
      | "admin" | weeks |
      | "admin" | topics |
      | "admin" | social |
      | "admin" | folderview |
      | "admin" | onetopic|
      | "admin" | topcoll|
      | "admin" | tabbedweek|
      | "admin" | flexpage|
      | "teacher1" | weeks |
      | "teacher1" | topics |
      | "teacher1" | social |
      | "teacher1" | folderview |
      | "teacher1" | onetopic|
      | "teacher1" | topcoll|
      | "teacher1" | tabbedweek|
      | "teacher1" | flexpage|
      | "teacher2" | weeks |
      | "teacher2" | topics |
      | "teacher2" | social |
      | "teacher2" | folderview |
      | "teacher2" | onetopic|
      | "teacher2" | topcoll|
      | "teacher2" | tabbedweek|
      | "teacher2" | flexpage|

  @javascript
  Scenario Outline: Headline data is disabled with express templates.
  Templates: minimal, cherub, dropshadow, future, joule, simple, sleek, topslide.
  Roles: manager, teacher, editingteacher.
  Course formats: weeks, topics, social, folderview, onetopic, topcoll, flexpage.

    Given the following config values are set as admin:
      | theme | express |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Xray Course 01 | xray1 | <course_format> |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | xray1 | editingteacher |
      | teacher2 | xray1 | teacher |

    And I log in with express for xray as <username>
    And I am on site homepage
    And I use express template <template> for xray
    And I wait "2" seconds
    And I follow "Xray Course 01"
    And I wait until the page is ready
    Then "#xray-nav-headline" "css_element" should not exist
    And "h4 .x-ray-icon-title" "css_element" should not exist

    Examples:
      | username | template | course_format |
      | "admin" | "minimal" | weeks |
      | "admin" | "minimal" | topics |
      | "admin" | "minimal" | social |
      | "admin" | "minimal" | folderview |
      | "admin" | "minimal" | onetopic|
      | "admin" | "minimal" | topcoll|
      | "admin" | "minimal" | tabbedweek|
      | "admin" | "cherub" | weeks |
      | "admin" | "cherub" | topics |
      | "admin" | "cherub" | social |
      | "admin" | "cherub" | folderview |
      | "admin" | "cherub" | onetopic|
      | "admin" | "cherub" | topcoll|
      | "admin" | "cherub" | tabbedweek|
      | "admin" | "cherub" | flexpage|
      | "admin" | "dropshadow" | weeks |
      | "admin" | "dropshadow" | topics |
      | "admin" | "dropshadow" | social |
      | "admin" | "dropshadow" | folderview |
      | "admin" | "dropshadow" | onetopic|
      | "admin" | "dropshadow" | topcoll|
      | "admin" | "dropshadow" | tabbedweek|
      | "admin" | "dropshadow" | flexpage|
      | "admin" | "future" | weeks |
      | "admin" | "future" | topics |
      | "admin" | "future" | social |
      | "admin" | "future" | folderview |
      | "admin" | "future" | onetopic|
      | "admin" | "future" | topcoll|
      | "admin" | "future" | tabbedweek|
      | "admin" | "future" | flexpage|
      | "admin" | "joule" | weeks |
      | "admin" | "joule" | topics |
      | "admin" | "joule" | social |
      | "admin" | "joule" | folderview |
      | "admin" | "joule" | onetopic|
      | "admin" | "joule" | topcoll|
      | "admin" | "joule" | tabbedweek|
      | "admin" | "joule" | flexpage|
      | "admin" | "simple" | weeks |
      | "admin" | "simple" | topics |
      | "admin" | "simple" | social |
      | "admin" | "simple" | folderview |
      | "admin" | "simple" | onetopic|
      | "admin" | "simple" | topcoll|
      | "admin" | "simple" | tabbedweek|
      | "admin" | "simple" | flexpage|
      | "admin" | "sleek" | weeks |
      | "admin" | "sleek" | topics |
      | "admin" | "sleek" | social |
      | "admin" | "sleek" | folderview |
      | "admin" | "sleek" | onetopic|
      | "admin" | "sleek" | topcoll|
      | "admin" | "sleek" | tabbedweek|
      | "admin" | "sleek" | flexpage|
      | "admin" | "topslide" | weeks |
      | "admin" | "topslide" | topics |
      | "admin" | "topslide" | social |
      | "admin" | "topslide" | folderview |
      | "admin" | "topslide" | onetopic|
      | "admin" | "topslide" | topcoll|
      | "admin" | "topslide" | tabbedweek|
      | "admin" | "topslide" | flexpage|
      | "teacher1" | "minimal" | weeks |
      | "teacher1" | "minimal" | topics |
      | "teacher1" | "minimal" | social |
      | "teacher1" | "minimal" | folderview |
      | "teacher1" | "minimal" | onetopic|
      | "teacher1" | "minimal" | topcoll|
      | "teacher1" | "minimal" | tabbedweek|
      | "teacher1" | "cherub" | weeks |
      | "teacher1" | "cherub" | topics |
      | "teacher1" | "cherub" | social |
      | "teacher1" | "cherub" | folderview |
      | "teacher1" | "cherub" | onetopic|
      | "teacher1" | "cherub" | topcoll|
      | "teacher1" | "cherub" | tabbedweek|
      | "teacher1" | "cherub" | flexpage|
      | "teacher1" | "dropshadow" | weeks |
      | "teacher1" | "dropshadow" | topics |
      | "teacher1" | "dropshadow" | social |
      | "teacher1" | "dropshadow" | folderview |
      | "teacher1" | "dropshadow" | onetopic|
      | "teacher1" | "dropshadow" | topcoll|
      | "teacher1" | "dropshadow" | tabbedweek|
      | "teacher1" | "dropshadow" | flexpage|
      | "teacher1" | "future" | weeks |
      | "teacher1" | "future" | topics |
      | "teacher1" | "future" | social |
      | "teacher1" | "future" | folderview |
      | "teacher1" | "future" | onetopic|
      | "teacher1" | "future" | topcoll|
      | "teacher1" | "future" | tabbedweek|
      | "teacher1" | "future" | flexpage|
      | "teacher1" | "joule" | weeks |
      | "teacher1" | "joule" | topics |
      | "teacher1" | "joule" | social |
      | "teacher1" | "joule" | folderview |
      | "teacher1" | "joule" | onetopic|
      | "teacher1" | "joule" | topcoll|
      | "teacher1" | "joule" | tabbedweek|
      | "teacher1" | "joule" | flexpage|
      | "teacher1" | "simple" | weeks |
      | "teacher1" | "simple" | topics |
      | "teacher1" | "simple" | social |
      | "teacher1" | "simple" | folderview |
      | "teacher1" | "simple" | onetopic|
      | "teacher1" | "simple" | topcoll|
      | "teacher1" | "simple" | tabbedweek|
      | "teacher1" | "simple" | flexpage|
      | "teacher1" | "sleek" | weeks |
      | "teacher1" | "sleek" | topics |
      | "teacher1" | "sleek" | social |
      | "teacher1" | "sleek" | folderview |
      | "teacher1" | "sleek" | onetopic|
      | "teacher1" | "sleek" | topcoll|
      | "teacher1" | "sleek" | tabbedweek|
      | "teacher1" | "sleek" | flexpage|
      | "teacher1" | "topslide" | weeks |
      | "teacher1" | "topslide" | topics |
      | "teacher1" | "topslide" | social |
      | "teacher1" | "topslide" | folderview |
      | "teacher1" | "topslide" | onetopic|
      | "teacher1" | "topslide" | topcoll|
      | "teacher1" | "topslide" | tabbedweek|
      | "teacher1" | "topslide" | flexpage|
      | "teacher2" | "minimal" | weeks |
      | "teacher2" | "minimal" | topics |
      | "teacher2" | "minimal" | social |
      | "teacher2" | "minimal" | folderview |
      | "teacher2" | "minimal" | onetopic|
      | "teacher2" | "minimal" | topcoll|
      | "teacher2" | "minimal" | tabbedweek|
      | "teacher2" | "cherub" | weeks |
      | "teacher2" | "cherub" | topics |
      | "teacher2" | "cherub" | social |
      | "teacher2" | "cherub" | folderview |
      | "teacher2" | "cherub" | onetopic|
      | "teacher2" | "cherub" | topcoll|
      | "teacher2" | "cherub" | tabbedweek|
      | "teacher2" | "cherub" | flexpage|
      | "teacher2" | "dropshadow" | weeks |
      | "teacher2" | "dropshadow" | topics |
      | "teacher2" | "dropshadow" | social |
      | "teacher2" | "dropshadow" | folderview |
      | "teacher2" | "dropshadow" | onetopic|
      | "teacher2" | "dropshadow" | topcoll|
      | "teacher2" | "dropshadow" | tabbedweek|
      | "teacher2" | "dropshadow" | flexpage|
      | "teacher2" | "future" | weeks |
      | "teacher2" | "future" | topics |
      | "teacher2" | "future" | social |
      | "teacher2" | "future" | folderview |
      | "teacher2" | "future" | onetopic|
      | "teacher2" | "future" | topcoll|
      | "teacher2" | "future" | tabbedweek|
      | "teacher2" | "future" | flexpage|
      | "teacher2" | "joule" | weeks |
      | "teacher2" | "joule" | topics |
      | "teacher2" | "joule" | social |
      | "teacher2" | "joule" | folderview |
      | "teacher2" | "joule" | onetopic|
      | "teacher2" | "joule" | topcoll|
      | "teacher2" | "joule" | tabbedweek|
      | "teacher2" | "joule" | flexpage|
      | "teacher2" | "simple" | weeks |
      | "teacher2" | "simple" | topics |
      | "teacher2" | "simple" | social |
      | "teacher2" | "simple" | folderview |
      | "teacher2" | "simple" | onetopic|
      | "teacher2" | "simple" | topcoll|
      | "teacher2" | "simple" | tabbedweek|
      | "teacher2" | "simple" | flexpage|
      | "teacher2" | "sleek" | weeks |
      | "teacher2" | "sleek" | topics |
      | "teacher2" | "sleek" | social |
      | "teacher2" | "sleek" | folderview |
      | "teacher2" | "sleek" | onetopic|
      | "teacher2" | "sleek" | topcoll|
      | "teacher2" | "sleek" | tabbedweek|
      | "teacher2" | "sleek" | flexpage|
      | "teacher2" | "topslide" | weeks |
      | "teacher2" | "topslide" | topics |
      | "teacher2" | "topslide" | social |
      | "teacher2" | "topslide" | folderview |
      | "teacher2" | "topslide" | onetopic|
      | "teacher2" | "topslide" | topcoll|
      | "teacher2" | "topslide" | tabbedweek|
      | "teacher2" | "topslide" | flexpage|
