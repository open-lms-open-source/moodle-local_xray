# along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
#
# The headline data should be present in the course page.
#
# @package    local_xray
# @author     David Castro
# @copyright  Copyright (c) 2017 Open LMS (https://www.openlms.net)
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_xray @local_xray_courseselection
Feature: X-Ray course selection presents the list of categories and courses and allows checking categories and courses

  Background:
    Given the following config values are set as admin:
      | instanceid | 1 | local_xray |
      | xrayurl | https://xrf-use1-dev.xrayanalytics.net | local_xray |
      | xrayusername | xray2sb | local_xray |
      | xraypassword | rewq1234 | local_xray |
      | xrayclientid | qa2nxtclonesb | local_xray |
      | displaymenu | 1 | local_xray |
    And the following config values are set as admin:
      | theme | classic |
    And the following "categories" exist:
      | name | description | parent | idnumber |
      | Category 1 | This is the category 1 | 0 | CAT01 |
      | Category 2 | This is the category 2 | 0 | CAT02 |
    And the following "courses" exist:
      | fullname | shortname | format | category | idnumber |
      | Xray Course 01 | COURSE01 | weeks | CAT01 | COURSE01 |
      | Xray Course 02 | COURSE02 | weeks | CAT01 | COURSE02 |
      | Xray Course 03 | COURSE03 | singleactivity | CAT02 | COURSE03 |

  @javascript
  Scenario: X-Ray course selection loads successfully
    Given I log in as "admin"
    And I am on site homepage
    And I expand "Site administration" node
    And I expand "Plugins" node
    And I expand "Local plugins" node
    And I expand "X-Ray Learning Analytics" node
    And I follow "X-Ray Course Selection"
    And I wait "10" seconds
    Then I should see "Category 1"
    Then I should see "Category 2"

  @javascript
  Scenario: X-Ray course selection saves changes successfully
    Given I log in as "admin"
    And I am on site homepage
    And I expand "Site administration" node
    And I expand "Plugins" node
    And I expand "Local plugins" node
    And I expand "X-Ray Learning Analytics" node
    And I follow "X-Ray Course Selection"
    And I wait "10" seconds
    And I expand the category "Category 1"
    And I wait "10" seconds
    Then I should see "COURSE01"
    And I check the course "COURSE01"
    And I click on "#id_submitbutton" "css_element"
    And I wait "10" seconds
    Then I should see category "Category 1" checked
    And I expand the category "Category 1"
    And I wait "10" seconds
    Then I should see course "COURSE01" checked
