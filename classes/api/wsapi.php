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
 * Convenient wrappers and helper for using the X-Ray web service API.
 *
 * @package local_xray
 * @author Darko Miletic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Moodlerooms
 */

namespace local_xray\api;

abstract class wsapi {
    const PLUGIN = 'local_xray';

    const XRAYLIST          = 'xrayList';
    const XRAYTABLE         = 'xrayTable';
    const XRAYPLOT          = 'xrayPlot';
    const XRAYSECTIONHEADER = 'xraySectionHeader';
    const XRAYHEATMAP       = 'xrayHeatmap';

    /**
     * @return bool
     * @throws \Exception
     * @throws \dml_exception
     * @throws jsonerror_exception
     */
    public static function login() {
        $username = get_config(self::PLUGIN, 'xrayusername');
        $pass     = get_config(self::PLUGIN, 'xraypassword');
        $baseurl  = get_config(self::PLUGIN, 'xrayurl');
        if (($username === false) || ($pass === false) || ($baseurl === false)) {
            return false;
        }
        $data = array('email' => $username, 'pass' => $pass);
        $url = sprintf('%s/user/login', $baseurl);
        $result = xrayws::instance()->post_hook($url, $data);
        if ($result) {
            $data = json_decode(xrayws::instance()->lastresponse());
            $result = (!is_null($data) && isset($data['ok']) && $data['ok'] && xrayws::instance()->hascookie());
            if (!$result) {
                // Should check this some more?
                xrayws::instance()->resetcookie();
            }
        }

        /** @var bool $result */
        return $result;
    }

    /**
     * @param string $url
     * @param null|int $start
     * @param null|int $count
     * @return bool|mixed
     */
    protected static function generic_getcall($url, $start = null, $count = null) {
        if (!empty($url)) {
            return false;
        }

        if ($start !== null) {
            if ($count === null) {
                $count = 20;
            }
            $query = http_build_query(array('start' => $start, 'count' => $count));
            $url .= '?' . $query;
        }
        if (!xrayws::instance()->hascookie()) {
            if (!self::login()) {
                return false;
            }
        }
        $result = xrayws::instance()->get_withcookie($url);
        if ($result) {
            $data = json_decode(xrayws::instance()->lastresponse());
            if ($data === false) {
                return false;
            }
            $result = $data;
        }

        return $result;
    }

    /**
     * @param string $domain
     * @param null|int $start
     * @param null|int $count
     * @return bool|mixed
     * @throws \Exception
     * @throws \dml_exception
     */
    public static function domaininfo($domain, $start = null, $count = null) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        if ($baseurl === false) {
            return false;
        }
        $url = sprintf('%s/%s', $baseurl, $domain);
        return self::generic_getcall($url, $start, $count);
    }

    /**
     * Returns list of all available courses for specified domain
     * @param string $domain
     * @param null|int $start
     * @param null|int $count
     * @return bool|mixed
     * @throws \Exception
     * @throws \dml_exception
     */
    public static function courses($domain, $start = null, $count = null) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        if ($baseurl === false) {
            return false;
        }
        $url = sprintf('%s/%s/course', $baseurl, $domain);
        return self::generic_getcall($url, $start, $count);
    }

    /**
     * Return specific course report
     * @param string $domain Name of the domain
     * @param int $courseid numeric id of the course within domain
     * @param string $report name of the report to be used (See wiki documetation for available types)
     * @param int $userid - numerical user id value
     * @param string $date - single date in format YYYY-MM-DD
     * @param string $subtype - optional report type (usually empty)
     * @param null|int $start pagination start (default null)
     * @param null|int $count pagination element count (default null)
     * @return bool|mixed
     * @throws \Exception
     * @throws \dml_exception
     */
    public static function course($domain, $courseid, $report = '', $userid = null, $date = '', $subtype = '', $start = null, $count = null) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        if ($baseurl === false) {
            return false;
        }
        $url = sprintf('%s/%s/course/%s', $baseurl, $domain, $courseid);
        if (!empty($userid)) {
            $url .= "/{$userid}";
        }
        if (!empty($report)) {
            $url .= "/{$report}";
        }
        if (!empty($subtype)) {
            $url .= "/{$subtype}";
        }
        if (!empty($date)) {
            $url .= "/{$date}";
        }
        return self::generic_getcall($url, $start, $count);
    }

    /**
     * Get list of participants for domain
     * @param string $domain
     * @param null|int $start
     * @param null|int $count
     * @return bool|mixed
     */
    public static function participants($domain, $start = null, $count = null) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        if ($baseurl === false) {
            return false;
        }

        $url = sprintf('%s/participant', $baseurl, $domain);
        return self::generic_getcall($url, $start, $count);
    }

    /**
     * @param $domain
     * @param $userid
     * @param $report
     * @param string $subtype
     * @param string $date
     * @param null $start
     * @param null $count
     * @return bool|mixed
     */
    public static function participantreport($domain, $userid, $report, $subtype = '', $date = '', $start = null, $count = null) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        if ($baseurl === false) {
            return false;
        }

        $url = sprintf('%s/participant/%s/%s', $baseurl, $domain, $userid, $report);
        if (!empty($subtype)) {
            $url .= "/{$subtype}";
        }
        if (!empty($date)) {
            $url .= "/{$date}";
        }
        return self::generic_getcall($url, $start, $count);
    }
}
