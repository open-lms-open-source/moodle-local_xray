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

    /**
     * @return bool
     * @throws \Exception
     * @throws \dml_exception
     * @throws jsonerror_exception
     */
    public static function login() {
        $username = get_config(self::PLUGIN, 'xrayusername');
        $pass     = get_config(self::PLUGIN, 'xraypassword');
        $url      = get_config(self::PLUGIN, 'xrayurl');
        if (($username === false) || ($pass === false) || ($url === false)) {
            return false;
        }
        $data = array('email' => $username, 'pass' => $pass);
        $result = xrayws::instance()->post_hook($url+'/user/login', $data);
        if ($result) {
            $data = json_decode(xrayws::instance()->lastresponse());
            $result = (!is_null($data) && isset($data['ok']) && $data['ok'] && xrayws::instance()->hascookie());
        }

        return $result;
    }

    /**
     * Generic GET call for API
     * @param string $url
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

        return self::generic_getcall($baseurl + '/' + $domain, $start, $count);
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
     * @param string $domain
     * @param int $courseid
     * @param string $report
     * @param null|int $start
     * @param null|int $count
     * @return bool|mixed
     * @throws \Exception
     * @throws \dml_exception
     */
    public static function course($domain, $courseid, $report = '', $start = null, $count = null) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        if ($baseurl === false) {
            return false;
        }
        $url = sprintf('%s/%s/course/%s', $baseurl, $domain, $courseid);
        if (!empty($report)) {
            $url .= "/{$report}";
        }
        return self::generic_getcall($url, $start, $count);
    }

}
