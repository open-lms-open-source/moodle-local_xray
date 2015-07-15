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

/**
 * A helper class to access REST api
 *
 * @author Darko Miletic
 */
class xrayws {
    const HTTP_GET     = 'GET';
    const HTTP_POST    = 'POST';
    const HTTP_PUT     = 'PUT';
    const HTTP_HEAD    = 'HEAD';
    const HTTP_DELETE  = 'DELETE';

    /**
     * @var null|xrayws
     */
    private static $instance = null;
    /**
     * @var null|string
     */
    private $cookie = null;

    /**
     * @var null|resource
     */
    private $curl = null;
    /**
     * @var null|string
     */
    private $error = null;
    /**
     * @var int
     */
    private $errorno = 0;

    /**
     * @var null|memfile
     */
    private $memfile = null;

    /**
     * @var null|string
     */
    private $rawresponse = null;

    /**
     * @var null|int
     */
    private $lasthttpcode = null;

    /**
     * @throws nocurl_exception
     */
    private function __construct() {
        // Something.
        if (!function_exists('curl_init')) {
            throw new nocurl_exception();
        }

        $this->curl = curl_init();
        $this->memfile = new memfile();
    }

    private function __clone() {
        // Prevent cloning.
    }

    public function __destruct() {
        // We leave destructor public on purporse. There are some cases when it may be called outside of context.
        if (is_resource($this->curl)) {
            curl_close($this->curl);
            $this->curl = null;
        }
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
     * @param array $custopts
     * @return bool
     */
    public function setopts(array $custopts = array()) {
        $this->memfile->reset();
        $this->rawresponse = null;
        $this->lasthttpcode = null;

        $standard = array(
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS      => 0,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FILE           => $this->memfile->get(),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT        => 3,
            CURLOPT_USERAGENT      => 'MoodleXRayClient/1.0',
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_HEADER         => false,
            CURLOPT_NOPROGRESS     => true,
            CURLOPT_FAILONERROR    => false,
        );

        $certpath = self::getcert();
        if (!empty($certpath)) {
            $standard[CURLOPT_CAINFO] = $certpath;
            $standard[CURLOPT_SSL_VERIFYPEER] = true;
            $standard[CURLOPT_SSL_VERIFYHOST] = 2;
        }

        $options = $standard + $custopts;
        $result = curl_setopt_array($this->curl, $options);
        return $result;
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

        // Next comes the default from php.ini
        $cacert = ini_get('curl.cainfo');
        if (!empty($cacert) and is_readable($cacert)) {
            return realpath($cacert);
        }

        /**
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
                self::instance()->cookie = trim(substr($header, $pos + 11));
            }
        }

        return $length;
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
    public function request($url, $method, array $custheaders = array(), array $options = array()) {
        if (empty($url)) {
            throw new nourl_exception();
        }
        if (empty($method)) {
            throw new norequestmethod_exception();
        }
        $ctype = 'application/json; charset=utf-8';
        $standardheader = array("Accept: {$ctype}", "Content-Type: {$ctype}");
        $headers = array_merge($standardheader, $custheaders);
        $useopts = array(
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_URL           => $url,
            CURLOPT_HTTPHEADER    => $headers,
        );
        $fullopts = $options + $useopts;
        curl_reset($this->curl);
        if (!$this->setopts($fullopts)) {
            throw new curlerror_exception($this->curl);
        }
        $response = curl_exec($this->curl);
        $this->error = curl_error($this->curl);
        $this->errorno = curl_errno($this->curl);
        $this->rawresponse = $this->memfile->get_content();
        $this->memfile->close();
        if ($response) {
            $httpcode = $this->getinfo(CURLINFO_HTTP_CODE);
            if ($httpcode !== false) {
                $this->lasthttpcode = $httpcode;
                if ($httpcode >= 400) {
                    $response = false;
                }
            }
        }

        return $response;
    }

    /**
     * @return null|string
     */
    public function lastresponse() {
        return $this->rawresponse;
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
        if (!empty($this->cookie)) {
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
        return $this->cookie;
    }

    public function resetcookie() {
        $this->cookie = null;
    }

    /**
     * @param null $opt
     * @return mixed
     */
    public function getinfo($opt = null) {
        $result = ($opt === null) ? curl_getinfo($this->curl) : curl_getinfo($this->curl, $opt);
        return $result;
    }

    /**
     * @return bool
     */
    public function hascookie() {
        return ($this->cookie !== null);
    }

    /**
     * @return int|null
     */
    public function lasthttpcode() {
        return $this->lasthttpcode;
    }
}
