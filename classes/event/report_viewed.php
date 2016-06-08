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
 * Class report_viewed.
 *
 * This event is fired when a user views a report.
 *
 * @package   local_xray
 * @author    German Vitale
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_xray\event;
use core\event\base;

defined('MOODLE_INTERNAL') || die();

class report_viewed extends \core\event\base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('reportviewed', 'local_xray');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $description = "The user with id '$this->userid' viewed the X-Ray ".get_string($this->other['reportname'], 'local_xray')." report for the course with id '$this->courseid'";
        // Add description for special cases.
        if ($this->other['reportname'] == 'discussionreportindividualforum') {
            $description .= " for the forum with id '".$this->other['forumid']."'.";
        } else if (isset($this->other['accessibledata']) && $this->other['accessibledata']) {
            $description = "The user with id '$this->userid' viewed the Accessible Data of the graph called ".$this->other['graphname']." in the X-Ray ".get_string($this->other['reportname'], 'local_xray')." report for the course with id '$this->courseid'.";
        }
        return $description;
    }

    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        // Basic Reports.
        $params = array(
            'controller' => $this->other['reportname'],
            'courseid' => $this->courseid
        );
        // Individual Reports.
        if ($this->other['reportname'] == 'activityreportindividual' || $this->other['reportname'] == 'discussionreportindividual') {
            $params['userid'] = $this->relateduserid;
        }
        // Discussion individual Forum Report.
        if ($this->other['reportname'] == 'discussionreportindividualforum') {
            $params['cmid'] = $this->other['cmid'];
            $params['forumid'] = $this->other['forumid'];
        }
        // Accessible Data.
        if (isset($this->other['accessibledata']) && $this->other['accessibledata']) {
            $params = array(
                'controller'    => 'accessibledata',
                'origincontroller' => $this->other['reportname'],
                'graphname' => $this->other['graphname'],
                'reportid' => $this->other['reportid'],
                'elementname' => $this->other['elementname'],
                'courseid' => $this->courseid
            );
        }
        return new \moodle_url('/local/xray/view.php', $params);
    }
}
