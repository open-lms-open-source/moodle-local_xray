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
            $result = (!is_null($data) && isset($data->ok) && $data->ok && xrayws::instance()->hascookie());
            if (!$result) {
                // Should check this some more?
                xrayws::instance()->resetcookie();
            }
        }

        /* @var bool $result */
        return $result;
    }

    public static function adminlogin() {
        $username = get_config(self::PLUGIN, 'xrayadmin');
        $pass     = get_config(self::PLUGIN, 'xrayadminkey');
        $baseurl  = get_config(self::PLUGIN, 'xrayadminserver');
        if (($username === false) || ($pass === false) || ($baseurl === false)) {
            return false;
        }
        $data = array('username' => $username, 'accesskey' => $pass);
        $url = sprintf('%s/user/keylogin', $baseurl);
        $result = xrayws::instance()->post_hook($url, $data);
        if ($result) {
            $data = json_decode(xrayws::instance()->lastresponse());
            $result = (!is_null($data) && isset($data->ok) && $data->ok && xrayws::instance()->hascookie());
            if (!$result) {
                // Should check this some more?
                xrayws::instance()->resetcookie();
            }
        }

        /* @var bool $result */
        return $result;
    }

    /**
     * Returns result in this format:
     * array(
     *   (object)array('id' => someid,
     *                 'filename' => 'uploaded filename',
     *                 'added' => 'date when added in ISO8601 format',
     *                 'analysed' => true|false,
     *                 'uploadstatus' => 0|1,
     *                 'datasize' => filesize,
     *                 'users_id' => someuserid
     *                 )
     *  // various objects go on
     * )
     *
     * @return bool|mixed
     */
    public static function getdatalist() {
        $baseurl  = get_config(self::PLUGIN, 'xrayadminserver');
        if ($baseurl === false) {
            return false;
        }

        $url = sprintf('%s/data/list', $baseurl);
        return self::generic_admingetcall($url);
    }

    /**
     * @return string
     */
    public static function mypublicip() {
        $ip = file_get_contents('https://api.ipify.org/');
        if (!$ip) {
            $ip = file_get_contents('http://myexternalip.com/raw');
        }
        if (!empty($ip)) {
            $ip = trim($ip);
        }
        return $ip;
    }


    /**
     * @return bool|mixed
     * @throws jsonerror_exception
     */
    public static function accesstoken() {
        $baseurl  = get_config(self::PLUGIN, 'xrayurl');
        $domain   = get_config(self::PLUGIN, 'xrayclientid');
        if (($domain === false) or ($baseurl === false)) {
            return false;
        }
        $result = false;
        $data = array('domain' => $domain, 'validhours' => 1);
        $url = sprintf('%s/user/accesstoken', $baseurl);
        if (!xrayws::instance()->hascookie()) {
            if (!self::adminlogin()) {
                return $result;
            }
        }
        $options[CURLOPT_COOKIE] = xrayws::instance()->getcookie();
        $result = xrayws::instance()->post($url, $data, array(), $options);
        if ($result) {
            $data = json_decode(xrayws::instance()->lastresponse());
            $result = (($data !== null) and isset($data->ok) and $data->ok and isset($data->token));
            if ($result) {
                $result = $data->token;
            }
        }

        return $result;
    }

    /**
     * @param $url
     * @return bool|mixed
     */
    protected static function generic_admingetcall($url) {
        if (empty($url)) {
            return false;
        }

        if (!xrayws::instance()->hascookie()) {
            if (!self::adminlogin()) {
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
     * @param string $url
     * @param null|int $start
     * @param null|int $count
     * @param null|string $sortfield
     * @param null|
     * @return bool|mixed
     */
    protected static function generic_getcall($url, $start = null, $count = null, $sortfield = null, $sortorder = null) {
        if (empty($url)) {
            return false;
        }

        $params = [];
        if ($start !== null) {
            if ($count === null) {
                $count = 20;
            }
            $params['start'] = $start;
            $params['count'] = $count;
        }
        if ($sortfield !== null) {
            if ($sortorder == null) {
                $sortorder = 'asc';
            }
            $params['sort'] = $sortfield;
            $params['order'] = $sortorder;
        }
        if (!empty($params)) {
            $query = http_build_query($params, null, '&');
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
     *
     * @param null|int $start
     * @param null|int $count
     * @return bool|mixed
     * @throws \Exception
     * @throws \dml_exception
     */
    public static function domaininfo($start = null, $count = null) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        $domain = get_config(self::PLUGIN, 'xrayclientid');
        if (empty($baseurl) || empty($domain)) {
            return false;
        }
        $url = sprintf('%s/%s', $baseurl, $domain);
        return self::generic_getcall($url, $start, $count);
    }

    /**
     * Returns list of all available courses for specified domain
     *
     * @param null|int $start
     * @param null|int $count
     * @return bool|mixed
     * @throws \Exception
     * @throws \dml_exception
     */
    public static function courses($start = null, $count = null) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        $domain = get_config(self::PLUGIN, 'xrayclientid');
        if (empty($baseurl) || empty($domain)) {
            return false;
        }
        $url = sprintf('%s/%s/course', $baseurl, $domain);
        return self::generic_getcall($url, $start, $count);
    }

    /**
     * Return specific course report
     *
     * @param int $courseid numeric id of the course within domain
     * @param string $report name of the report to be used (See wiki documetation for available types)
     * @param int $userid - numerical user id value
     * @param string $date - single date in format YYYY-MM-DD
     * @param string $subtype - optional report type (usually empty)
     * @param null|int $start pagination start (default null)
     * @param null|int $count pagination element count (default null)
     * @param null|string $sortfield field to sort on
     * @param null|string $sortorder sort order (asc|desc)
     * @return bool|mixed
     * @throws \Exception
     * @throws \dml_exception
     */
    public static function course( $courseid, $report = '', $userid = null, $date = '', $subtype = '',
                                   $start = null, $count = null, $sortfield = null, $sortorder = null) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        $domain = get_config(self::PLUGIN, 'xrayclientid');
        if (empty($baseurl) || empty($domain)) {
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
        return self::generic_getcall($url, $start, $count, $sortfield, $sortorder);
    }

    /**
     *
     * @param int $courseid
     * @param string $name - element name (element1, etc.)
     * @param string $report
     * @param null $userid
     * @param string $date
     * @param string $subtype
     * @param int $start
     * @param int $count
     * @param null|string $sortfield field to sort on
     * @param null|string $sortorder sort order (asc|desc)
     * @return bool|mixed
     */
    public static function courseelement($courseid, $name, $report = '', $userid = null, $date = '', $subtype = '',
                                         $start = null, $count = null, $sortfield = null, $sortorder = null) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        $domain = get_config(self::PLUGIN, 'xrayclientid');
        if (empty($baseurl) || empty($domain)) {
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
        $url .= "/elements/{$name}";
        return self::generic_getcall($url, $start, $count, $sortfield, $sortorder);
    }

    /**
     * Get list of participants for domain
     *
     * @param null|int $start
     * @param null|int $count
     * @param null|string $sortfield field to sort on
     * @param null|string $sortorder sort order (asc|desc)
     * @return bool|mixed
     */
    public static function participants($start = null, $count = null, $sortfield = null, $sortorder = null) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        $domain = get_config(self::PLUGIN, 'xrayclientid');
        if (empty($baseurl) || empty($domain)) {
            return false;
        }

        $url = sprintf('%s/participant', $baseurl, $domain);
        return self::generic_getcall($url, $start, $count, $sortfield, $sortorder);
    }

    /**
     *
     * @param $userid
     * @param $report
     * @param string $subtype
     * @param string $date
     * @param null $start
     * @param null $count
     * @param null|string $sortfield field to sort on
     * @param null|string $sortorder sort order (asc|desc)
     * @return bool|mixed
     */
    public static function participantreport($userid, $report, $subtype = '', $date = '', $start = null, $count = null,
                                             $sortfield = null, $sortorder = null) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        $domain = get_config(self::PLUGIN, 'xrayclientid');
        if (empty($baseurl) || empty($domain)) {
            return false;
        }

        $url = sprintf('%s/participant/%s/%s', $baseurl, $domain, $userid, $report);
        if (!empty($subtype)) {
            $url .= "/{$subtype}";
        }
        if (!empty($date)) {
            $url .= "/{$date}";
        }
        return self::generic_getcall($url, $start, $count, $sortfield, $sortorder);
    }

    /**
     *
     * @param int $userid
     * @param string $report
     * @param string $name - name of the element
     * @param string $subtype
     * @param string $date
     * @param int $start
     * @param int $count
     * @param null|string $sortfield field to sort on
     * @param null|string $sortorder sort order (asc|desc)
     * @return bool|mixed
     */
    public static function participantreportelements($userid, $report, $name, $subtype = '', $date = '',
                                                     $start = null, $count = null, $sortfield = null, $sortorder = null) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        $domain = get_config(self::PLUGIN, 'xrayclientid');
        if (empty($baseurl) || empty($domain)) {
            return false;
        }

        $url = sprintf('%s/participant/%s/%s', $baseurl, $domain, $userid, $report);
        if (!empty($subtype)) {
            $url .= "/{$subtype}";
        }
        if (!empty($date)) {
            $url .= "/{$date}";
        }
        $url .= "/elements/{$name}";
        return self::generic_getcall($url, $start, $count, $sortfield, $sortorder);
    }

    /**
     * @return bool|mixed
     */
    public static function datalist() {
        $baseurl = get_config(self::PLUGIN, 'xrayadminserver');
        if (empty($baseurl)) {
            return false;
        }

        $url = sprintf('%s/data/list', $baseurl);
        return self::generic_admingetcall($url);
    }
}
