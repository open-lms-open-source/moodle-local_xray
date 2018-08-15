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
 * @package   local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class wsapi
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class wsapi {
    const PLUGIN = 'local_xray';

    const XRAYLIST          = 'xrayList';
    const XRAYTABLE         = 'xrayTable';
    const XRAYPLOT          = 'xrayPlot';
    const XRAYSECTIONHEADER = 'xraySectionHeader';
    const XRAYHEATMAP       = 'xrayHeatmap';

    /**
     *
     * @return bool
     * @throws \Exception
     * @throws \dml_exception
     * @throws jsonerror_exception
     */
    public static function login() {
        if (xrayws::instance()->hascookie()) {
            return true;
        }

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

        return $result;
    }

    /**
     * Returns result in this format:
     * array(
     *   (object)array('ok' => true,
     *                 'account' => 'login-email'
     * )
     *
     * @return bool
     */
    public static function accountcheck() {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        if (empty($baseurl)) {
            return false;
        }

        $data = self::generic_getcall($baseurl);

        return !is_null($data) && isset($data->ok) && $data->ok;
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
        $setting = cache::cache_timeout_hours();
        $validhours = empty($setting) ? 1 : $setting;
        $data = array('domain' => $domain, 'validhours' => $validhours);
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
     * Generic http post call
     * @param string $url
     * @param string $postdata
     * @return bool|mixed
     */
    protected static function generic_postcall($url, $postdata) {
        if (empty($url)) {
            return false;
        }

        if (!xrayws::instance()->hascookie()) {
            if (!self::login()) {
                return false;
            }
        }

        $result = xrayws::instance()->post_withcookie($url, [], [], $postdata);
        if ($result) {
            $data = json_decode(xrayws::instance()->lastresponse());
            if ($data === false) {
                return array('ok' => false, 'error' => xrayws::instance()->lastresponse());
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
     * Returns list of all available valid courses for specified domain
     *
     * @param null|int $start
     * @param null|int $count
     * @return bool|mixed
     * @throws \Exception
     * @throws \dml_exception
     */
    public static function validcourses($start = null, $count = null) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        $domain = get_config(self::PLUGIN, 'xrayclientid');
        if (empty($baseurl) || empty($domain)) {
            return false;
        }
        $url = sprintf('%s/%s/course/valid', $baseurl, $domain);
        return self::generic_getcall($url, $start, $count);
    }

    /**
     * Return specific course report
     *
     * @param int $courseid numeric id of the course within domain
     * @param string $report name of the report to be used (See wiki documentation for available types)
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

    /**
     * @param string $reportid
     * @param string $elementname
     * @return bool|mixed
     */
    public static function report_accessibility($reportid, $elementname) {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        $domain = get_config(self::PLUGIN, 'xrayclientid');
        if (empty($baseurl) || empty($domain)) {
            return false;
        }
        $url = sprintf('%s/%s/data/%s/%s/accessible', $baseurl, $domain, $reportid, $elementname);
        return self::generic_getcall($url);
    }

    /**
     * Create complete url to xray image and check if url is available using token.
     *
     * @param $graphelement - Element of graph to show sent from xray side.
     * @return \moodle_url
     * @throws \moodle_exception
     */
    public static function get_imgurl_xray($graphelement) {

        if (defined('BEHAT_SITE_RUNNING')) {
            global $OUTPUT;
            // Return X-ray logo for behat test.
            $imgurl = $OUTPUT->image_url("xray-logo", "local_xray");
            return $imgurl;
        }

        $result = false;
        $cfgxray = get_config('local_xray');
        $imgurl = new \moodle_url(sprintf('%s/%s/%s', $cfgxray->xrayurl, $cfgxray->xrayclientid, $graphelement->uuid));
        $imgurl->param('accesstoken', self::accesstoken());

        $curlopts = array(
            'CURLOPT_TIMEOUT' => 2,
            'CURLOPT_CONNECTTIMEOUT' => 2
        );

        if (isset($cfgxray->connecttimeout) && !empty($cfgxray->connecttimeout)) {
            $curlopts['CURLOPT_CONNECTTIMEOUT'] = $cfgxray->connecttimeout;
        }

        if (isset($cfgxray->timeout) && !empty($cfgxray->timeout)) {
            $curlopts['CURLOPT_TIMEOUT'] = $cfgxray->timeout;
        }

        $ch = new \curl(['debug' => false]);
        $ch->head($imgurl, $curlopts);

        // Object for message if we found a problem with result.
        $a = new \stdClass();
        $a->url = $imgurl->out();
        $a->graphelement = $graphelement->elementName;

        $cerrno = $ch->get_errno();
        if ($cerrno != CURLE_OK) {
            // Error with curl connection.
            $a->error = $ch->error;
            print_error("xrayws_error_graphs", "local_xray", '', $a);
        }

        if (!empty($ch->info['content_type']) && preg_match('#^image/.*#', $ch->info['content_type'])) {
            $result = $imgurl;
        } else {
            // Incorrect format returned from xray side, the content type returned is not an image.
            $a->error = get_string("xrayws_error_graphs_incorrect_contentype", "local_xray", $ch->info['content_type']);
            print_error("xrayws_error_graphs", "local_xray", '', $a);
        }

        return $result;
    }

    /**
     * Save the courses filter value for analysis.
     *
     * @param string[] $cids
     * @return mixed
     */
    public static function save_analysis_filter($cids) {
        global $CFG;
        if (defined('BEHAT_SITE_RUNNING') ||
                (isset($CFG->local_xray_disable_analysisfilter) &&
                $CFG->local_xray_disable_analysisfilter)) {
            $res = (object) array('ok' => true);
            return $res;
        }

        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        $domain = get_config(self::PLUGIN, 'xrayclientid');
        if (empty($baseurl) || empty($domain)) {
            return false;
        }
        $url = sprintf('%s/%s/analysisfilter/course', $baseurl, $domain);

        $postdata = '{ "enabled" : ['.implode(',', $cids).']}';

        return self::generic_postcall($url, $postdata);
    }

    /**
     * Get the courses filter value for analysis.
     *
     * @return boolean|array False if there was an issue, array with courses otherwise
     */
    public static function get_analysis_filter() {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        $domain = get_config(self::PLUGIN, 'xrayclientid');
        if (empty($baseurl) || empty($domain)) {
            return false;
        }
        $url = sprintf('%s/%s/analysisfilter/course', $baseurl, $domain);

        return self::generic_getcall($url);
    }

    /**
     * Get the dashboard data for X-Ray reports.
     *
     * @param int $courseid
     * @return bool|mixed
     * @throws \Exception
     * @throws \dml_exception
     */
    public static function dashboard($courseid) {
        $baseurl = get_config(self::PLUGIN, 'xraydashboardurl');
        $domain = get_config(self::PLUGIN, 'xrayclientid');
        $pass = get_config(self::PLUGIN, 'xraypassword');
        $url = sprintf('%s/%s', $baseurl, 'dashboard');

        if (empty($url) || empty($domain) || empty($pass)) {
            return false;
        }

        $params = [];
        $params['courseid'] = $courseid;
        $params['domain'] = $domain;
        $params['hash'] = md5('xTrEm35A1t'.$pass);

        if (!empty($params)) {
            $query = http_build_query($params, null, '&');
            $url .= '?' . $query;
        }

        $result = xrayws::instance()->get($url);

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
     * Get the risk configuration on X-Ray side.
     *
     * @return boolean|array False if there was an issue, array with risk configuration.
     */
    public static function get_risk_configuration() {
        $baseurl = get_config(self::PLUGIN, 'xrayurl');
        $domain = get_config(self::PLUGIN, 'xrayclientid');
        if (empty($baseurl) || empty($domain)) {
            return false;
        }
        $url = sprintf('%s/%s/editconf', $baseurl, $domain);

        return self::generic_getcall($url);
    }
}
