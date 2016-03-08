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
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * A helper class to access REST api
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class xrayws {
    const HTTP_GET     = 'GET';
    const HTTP_POST    = 'POST';
    const HTTP_PUT     = 'PUT';
    const HTTP_HEAD    = 'HEAD';
    const HTTP_DELETE  = 'DELETE';

    const ERR_UNKNOWN  = 1010;
    const ERR_JSON     = 1020;
    const PLUGIN       = 'local_xray';
    const COOKIE       = 'local_xray_cookie';

    /**
     * @var null|xrayws
     */
    private static $instance = null;
    /**
     * @var null|string
     */
    private $cookie = null;

    /**
     * @var null|string
     */
    private $error = null;
    /**
     * @var int
     */
    private $errorno = 0;

    /**
     * @var null|string
     */
    private $errorstring = null;

    /**
     * @var null|memfile
     */
    private $memfile = null;

    /**
     * @var null|string
     */
    private $rawresponse = null;

    /**
     * @var null|string
     */
    private $respheaders = null;

    /**
     * @var null|int
     */
    private $lasthttpcode = null;

    /**
     * @var null|array
     */
    private $curlinfo = null;

    /**
     * @var null|cache
     */
    private $cache = null;

    /**
     * @throws nocurl_exception
     */
    private function __construct() {
        // Something.
        if (!function_exists('curl_init')) {
            throw new nocurl_exception();
        }

        $this->memfile = new memfile();
        $this->cache = new cache();
    }

    private function __clone() {
        // Prevent cloning.
    }

    /**
     * @return xrayws|null
     * @throws nocurl_exception
     */
    public static function instance() {
        if (self::$instance === null) {
            $c = __CLASS__;
            self::$instance = new $c();
        }
        return self::$instance;
    }

    /**
     * Reset cURL cache for current user.
     */
    public function reset_cache() {
        $this->cache->refresh();
    }

    /**
     *
     * @param array $custopts
     * @return array
     */
    public function getopts(array $custopts = array()) {
        $this->memfile->reset();
        $this->rawresponse  = null;
        $this->lasthttpcode = null;
        $this->respheaders  = null;
        $this->error        = null;
        $this->errorno      = 0;
        $this->errorstring  = null;
        $this->curlinfo     = null;

        $standard = array(
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS      => 0,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FILE           => $this->memfile->get(),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_USERAGENT      => 'MoodleXRayClient/1.0',
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_HEADER         => true,
            CURLOPT_NOPROGRESS     => true,
            CURLOPT_FAILONERROR    => false,
        );

        $certpath = self::getcert();
        if (!empty($certpath)) {
            $standard[CURLOPT_CAINFO] = $certpath;
            $standard[CURLOPT_SSL_VERIFYPEER] = true;
            $standard[CURLOPT_SSL_VERIFYHOST] = 2;
        }

        // Set $CFG->forced_plugin_settings['local_xray'][connecttimeout] to custom connect timeout.
        // For more details on this option take a look at
        // CURLOPT_CONNECTTIMEOUT on http://php.net/manual/en/function.curl-setopt.php .
        $connecttimeout = get_config('local_xray', 'connecttimeout');
        if ($connecttimeout !== false) {
            $standard[CURLOPT_CONNECTTIMEOUT] = $connecttimeout;
        }

        // Set $CFG->forced_plugin_settings['local_xray'][timeout] to custom timeout.
        // For more details on this option take a look at
        // CURLOPT_TIMEOUT on http://php.net/manual/en/function.curl-setopt.php .
        $timeout = get_config('local_xray', 'timeout');
        if ($timeout !== false) {
            $standard[CURLOPT_TIMEOUT] = $timeout;
        }

        // Proxy support.
        $proxyhost = get_config('local_xray', 'proxyhost');
        $proxyport = get_config('local_xray', 'proxyport');
        if (!empty($proxyhost)) {
            if (!empty($proxyport)) {
                $proxyhost .= ':'.$proxyport;
            }
            $standard[CURLOPT_PROXY] = $proxyhost;
        }
        $proxyuser = get_config('local_xray', 'proxyuser');
        $proxypwd  = get_config('local_xray', 'proxypwd');
        if (!empty($proxyuser) && !empty($proxypwd)) {
            $standard[CURLOPT_PROXYUSERPWD] = $proxyuser.':'.$proxypwd;
            $standard[CURLOPT_PROXYAUTH   ] = CURLAUTH_BASIC | CURLAUTH_NTLM;
        }
        $proxytype = get_config('local_xray', 'proxytype');
        if (!empty($proxytype)) {
            if (strcasecmp('SOCKS5', $proxytype) == 0) {
                $standard[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
            } else {
                $standard[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
                $standard[CURLOPT_HTTPPROXYTUNNEL] = false;
            }
        }

        $options = $standard + $custopts;

        return $options;
    }

    /**
     * Returns path to the available CA Certificate needed to establish secure
     * connection to the web service endpoint.
     *
     * @return null|string
     */
    public static function getcert() {
        global $CFG;

        // Bundle in dataroot always wins if present.
        $cacert = "$CFG->dataroot/xrayca.pem";
        if (is_readable($cacert)) {
            return realpath($cacert);
        }

        // Next comes the default from php.ini.
        $cacert = ini_get('curl.cainfo');
        if (!empty($cacert) and is_readable($cacert)) {
            return realpath($cacert);
        }

        /*
         * This is a standard set of certificates that ships with Moodle
         * Should work for any standard issue certificate.
         * For self-signed certificates make sure to set xrayca.pem or curl.cainfo properly
         */
        $cacert = "$CFG->libdir/cacert.pem";
        if (is_readable($cacert)) {
            return realpath($cacert);
        }

        return null;
    }

    /**
     * @param resource $ch
     * @param string $header
     * @return int
     */
    public static function header_callback($ch, $header) {
        ($ch === null); // To remove unused variable warning.
        $length = strlen($header);
        if ($length > 11) {
            $pos = stripos($header, 'set-cookie:');
            if ($pos !== false) {
                $cookie = trim(substr($header, $pos + 11));
                if (stripos($cookie, 'connect.sid=') !== false) {
                    self::instance()->setcookie($cookie);
                }
            }
        }

        return $length;
    }

    /**
     * Request to webservice.
     *
     * If you are running behat test, this simulate call to webservice, loading response from local json file.
     * This search file generated with script "xray_fetch.sh" in folder local/xray/test/fixtures.
     *
     * @param string $url
     * @param string $method
     * @param array $custheaders
     * @param array $options
     * @return mixed
     * @throws \Exception
     * @throws norequestmethod_exception
     * @throws nourl_exception
     */
    public function request($url, $method, array $custheaders = array(), array $options = array()) {
        global $CFG;

        if (empty($url)) {
            throw new nourl_exception();
        }
        if (empty($method)) {
            throw new norequestmethod_exception();
        }

        /*
         * Running behat test.
         */
        if (defined('BEHAT_SITE_RUNNING')) {

            $result = ""; // Return json from file or empty.
            $parse = parse_url($url);
            $params = explode("/", $parse["path"]);

            if (isset($params[1]) && $params[1] == "error") {
                // With this name of instance, we simulate a error in connection to xray.
                $this->errorno     = self::ERR_UNKNOWN;
                $this->errorstring = 'error_generic';
                $this->error       = get_string("error_behat_instancefail", self::PLUGIN);

            } else {

                // Get filename of json to return.
                $filename = $this->behat_getjsonfile($params);
                if (!empty($filename)) {
                    // Get json file.
                    $result = file_get_contents($CFG->dirroot."/local/xray/tests/fixtures/$filename");
                    if ($result) {
                        // Call to user/login, set cookie required.
                        if (isset($params[1]) && isset($params[2]) && ($params[1] == "user") && ($params[2] == "login")) {
                            $this->setcookie("behat_test");
                        }
                    }
                }

                if (empty($result)) {
                    // Json file dont found. Set error for debug.
                    $this->errorno     = self::ERR_UNKNOWN;
                    $this->error       = get_string("error_behat_getjson", "local_xray", $filename);
                    $this->errorstring = 'error_generic';
                }
            }
            // Set rawresponse , this will be used in generic_call.
            $this->rawresponse = $result;
            return $result;
        }

        $ctype = 'application/json; charset=UTF-8';
        $standardheader = array("Accept: {$ctype}", "Content-Type: {$ctype}");
        $headers = array_merge($standardheader, $custheaders);
        $useopts = array(
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_URL           => $url,
            CURLOPT_HTTPHEADER    => $headers,
        );
        $fullopts = $options + $useopts;
        $curlopts = $this->getopts($fullopts);
        if ($ret = $this->cache->get($url)) {
            $this->rawresponse = $ret;
            return true;
        }
        $curl = new nethold($curlopts);
        $response = $curl->exec();
        $this->error = $curl->geterror();
        $this->errorno = $curl->geterrno();
        $this->curlinfo = $curl->getinfo();
        $rawresponse = $this->memfile->get_content();
        $this->memfile->close();
        if (!empty($rawresponse)) {
            list($this->respheaders, $this->rawresponse) = explode("\r\n\r\n", $rawresponse, 2);
        }
        if ($response) {
            $httpcode = isset($this->curlinfo['http_code']) ? $this->curlinfo['http_code'] : false;
            if ($httpcode !== false) {
                $this->lasthttpcode = $httpcode;
                if ($httpcode >= 400) {
                    $response = false;
                    $contentype = isset($this->curlinfo['content_type']) ? $this->curlinfo['content_type'] : false;
                    if ($contentype && (stripos($contentype, 'application/json') !== false)) {
                        $decode = json_decode($this->rawresponse);
                        if ($decode === false) {
                            $this->errorno     = json_last_error();
                            $this->error       = json_last_error_msg();
                            $this->errorstring = 'error_generic';
                        } else if (property_exists($decode, 'error')) {
                            $this->errorno     = self::ERR_UNKNOWN;
                            $this->error       = $decode->error;
                            $this->errorstring = 'xrayws_error_server';
                        }
                    }
                }
            }
        } else {
            if ($this->errorno) {
                $this->errorstring = 'xrayws_error_curl';
            }
        }

        if ($response) {
            $result = validationhelper::validate_schema($this->rawresponse, $url);
            if (!empty($result)) {
                $this->errorno     = self::ERR_JSON;
                $this->error       = "Unexpected JSON format: " . implode(', ', $result);
                $this->errorstring = 'xrayws_error_server';
                $response = false;
            } else {
                $this->cache->set($url, $this->rawresponse);
            }
        }

        return $response;
    }

    /**
     * Function used for behat tests.
     * Get filename to return in call of webservice when behat is running.
     *
     * @param array $params
     * @return string
     */
    private function behat_getjsonfile(array $params) {

        $filename = "";

        // Call to course reports / course element report / individual reports.
        if (isset($params[2]) && $params[2] == "course" ) {

            if (isset($params[4]) && $params[4] == "forum" &&
                isset($params[6]) && $params[6] == "discussion") {
                // Call to discussion individual forum.
                $filename = "course-report-discussionreportindividualforum-final.json";
            }

            if (isset($params[5])) {

                switch($params[5]) {
                    case "elements":
                        // Call to course elements reports.
                        $filename = sprintf("course-element-%s-%s-final.json", $params[4], $params[6]);
                        break;
                    case "activity":
                        // Call to activity report individual report.
                        $filename = "course-report-activityreportindividual-final.json";
                        break;
                    case "discussion":
                        // Call to discussion report individual report.
                        $filename = "course-report-discussionreportindividual-final.json";
                        break;
                }
            }

            if (isset($params[4]) && !isset($params[5])) {
                // Call to complete course report.
                $filename = sprintf("course-report-%s-final.json", $params[4]);
            }
        }

        // Call to accessibledata.
        if (isset($params[2]) && isset($params[5]) && $params[2] == "data" && $params[5] == "accessible") {
            $filename = sprintf("data-accessible-%s-final.json", $params[4]);
        }

        // Call to user/login or user/accesstoken.
        if (isset($params[1]) && isset($params[2]) && ($params[1] == "user") &&
            ($params[2] == "accesstoken" || $params[2] == "login")) {
            $filename = sprintf('%s-%s-final.json', $params[1], $params[2]);
        }

        return $filename;
    }

    /**
     * @return null|string
     */
    public function lastresponse() {
        return $this->rawresponse;
    }

    /**
     * @return null|string
     */
    public function response_headers() {
        return $this->respheaders;
    }

    /**
     * @return string
     */
    public function request_headers() {
        return isset($this->curlinfo['request_header']) ? $this->curlinfo['request_header'] : '';
    }

    /**
     * Method should be used for printing Exception based error status.
     * @param bool $extrainfoondebug
     * @return string
     */
    public function errorinfo($extrainfoondebug = true) {
        $lasterrormsg = $this->geterrormsg();
        // In case debug mode is on show extra debug information.
        if ($extrainfoondebug) {
            $lasterrorcode = sprintf("Error code: %s", $this->geterrorcode());
            if (get_config('core', 'debug') == DEBUG_DEVELOPER) {
                if (CLI_SCRIPT) {
                    $lasterrormsg .= $lasterrorcode. "\n";
                    $lasterrormsg .= "Web Service request time: ";
                    $lasterrormsg .= $this->curlinfo['total_time']." s \n";
                    $requestheaders = $this->request_headers();
                    if (!empty($requestheaders)) {
                        $lasterrormsg .= "Request headers:\n";
                        $lasterrormsg .= $this->request_headers();
                        $lasterrormsg .= "\n\n";
                        $lasterrormsg .= "Response headers:\n";
                        $lasterrormsg .= $this->response_headers();
                        $lasterrormsg .= "\n\n";
                        $lasterrormsg .= "Response body:\n";
                        $lasterrormsg .= $this->lastresponse();
                        $lasterrormsg .= "\n";
                    }
                } else {
                    $lasterrormsg  = \html_writer::span($this->geterrormsg()) . \html_writer::empty_tag('br');
                    $lasterrormsg .= \html_writer::span($lasterrorcode) . \html_writer::empty_tag('br');
                    $calltitle = \html_writer::span('Web Service request time:');
                    $calltime = \html_writer::span(" ".$this->curlinfo['total_time']." s");
                    $lasterrormsg .= \html_writer::div($calltitle . $calltime);
                    $requestheaders = $this->request_headers();
                    if (!empty($requestheaders)) {
                        $lasterrormsg .= \html_writer::empty_tag('br');
                        $rtitle = \html_writer::span('Request headers:');
                        $request = \html_writer::tag('pre', s($this->request_headers()), array('title' => 'Request headers'));
                        $lasterrormsg .= \html_writer::div($rtitle . $request);
                        $lasterrormsg .= \html_writer::empty_tag('br');
                        $rstitle = \html_writer::span('Response headers:');
                        $response = \html_writer::tag('pre', s($this->response_headers()), array('title' => 'Response headers'));
                        $lasterrormsg .= \html_writer::div($rstitle . $response);
                        $lasterrormsg .= \html_writer::empty_tag('br');
                        $responsebody = \html_writer::tag('pre', s($this->lastresponse()), array('title' => 'Response body'));
                        $rsbodytitle = \html_writer::span('Response body:');
                        $lasterrormsg .= \html_writer::div($rsbodytitle . $responsebody);
                    }
                }
            }
        }
        return $lasterrormsg;
    }

    /**
     * Throws an exception with all data
     * @throws \moodle_exception
     */
    public function print_error() {
        print_error($this->errorstring, self::PLUGIN, '', $this->errorinfo());
    }


    /**
     * @param $url
     * @param null $data
     * @param array $custheaders
     * @param array $options
     * @return mixed
     * @throws curlerror_exception
     * @throws jsonerror_exception
     * @throws norequestmethod_exception
     * @throws nourl_exception
     */
    public function post($url, $data = null, array $custheaders = array(), array $options = array()) {
        if (!empty($data)) {
            $jdata = json_encode($data);
            if ($jdata === false) {
                throw new jsonerror_exception();
            }
            $custheaders[] = 'Content-Length: ' . strlen($jdata);
            $options[CURLOPT_POSTFIELDS] = $jdata;
        }
        return $this->request($url, self::HTTP_POST, $custheaders, $options);
    }

    /**
     * @param string $url
     * @param array $custheaders
     * @param array $options
     * @return mixed
     * @throws curlerror_exception
     * @throws norequestmethod_exception
     * @throws nourl_exception
     */
    public function get($url, array $custheaders = array(), array $options = array()) {
        return $this->request($url, self::HTTP_GET, $custheaders, $options);
    }

    /**
     * @param string $url
     * @param null|mixed $data
     * @param array $custheaders
     * @param array $options
     * @return mixed
     * @throws jsonerror_exception
     */
    public function post_hook($url, $data = null, array $custheaders = array(), array $options = array()) {
        $options[CURLOPT_HEADERFUNCTION] = array(__CLASS__, 'header_callback');
        return $this->post($url, $data, $custheaders, $options);
    }


    /**
     * @param string $url
     * @param string $method
     * @param array $custheaders
     * @param array $options
     * @return mixed
     * @throws curlerror_exception
     * @throws norequestmethod_exception
     * @throws nourl_exception
     */
    public function request_withcookie($url, $method, array $custheaders = array(), array $options = array()) {
        if ($this->hascookie()) {
            $options[CURLOPT_COOKIE] = $this->cookie;
        }
        return $this->request($url, $method, $custheaders, $options);
    }

    /**
     * @param string $url
     * @param array $custheaders
     * @param array $options
     * @return mixed
     */
    public function get_withcookie($url, array $custheaders = array(), array $options = array()) {
        return $this->request_withcookie($url, self::HTTP_GET, $custheaders, $options);
    }

    /**
     * @param string $url
     * @param array $custheaders
     * @param array $options
     * @return mixed
     */
    public function post_withcookie($url, array $custheaders = array(), array $options = array()) {
        return $this->request_withcookie($url, self::HTTP_POST, $custheaders, $options);
    }

    /**
     * @return int
     */
    public function geterrorcode() {
        return $this->errorno;
    }

    /**
     * @return null|string
     */
    public function geterrormsg() {
        return $this->error;
    }

    /**
     * @return null|string
     */
    public function getcookie() {
        if (!PHPUNIT_TEST) {
            if (empty($this->cookie)) {
                $value = $this->cache->get(self::COOKIE);
                if (!empty($value)) {
                    $this->cookie = $value;
                }
            }
        }
        return $this->cookie;
    }

    public function resetcookie() {
        $this->cache->delete(self::COOKIE);
        $this->cookie = null;
    }

    public function setcookie($value) {
        if (!empty($value)) {
            $this->cache->set(self::COOKIE, $value);
            $this->cookie = $value;
        }
    }

    /**
     * @param null $opt
     * @return mixed
     */
    public function getinfo($opt = null) {
        $result = ($opt === null) ? $this->curlinfo : $this->curlinfo[$opt];
        return $result;
    }

    /**
     * @return bool
     */
    public function hascookie() {
        $value = $this->getcookie();
        return !empty($value);
    }

    /**
     * @return int|null
     */
    public function lasthttpcode() {
        return $this->lasthttpcode;
    }
}
