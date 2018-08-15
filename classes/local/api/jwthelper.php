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
 * JSON Web Token helpers.
 *
 * @package   local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class jwthelper
 * @package local_xray
 */
abstract class jwthelper {
    const ALGO    = 'HS256';
    const PLUGIN  = 'local_xray';
    const TIMEOUT = HOURSECS;

    /**
     * Token timeout can be specified in the config.php
     *
     * $CFG->forced_plugin_settings['local_xray']['token_timeout'] = <nr of seconds>;
     *
     * @return int - token timeout in seconds starting at the moment of creation
     */
    public static function token_timeout() {
        $timeout = get_config(self::PLUGIN, 'token_timeout');
        if (empty($timeout)) {
            $timeout = self::TIMEOUT;
        }
        return $timeout;
    }

    /**
     * Validates JSON Web Token
     * For this to work AWS secret and client id must be configured in the module global settings section.
     *
     * @param  string $token - JW Token
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \DomainException
     * @throws \Firebase\JWT\SignatureInvalidException
     * @throws \Firebase\JWT\BeforeValidException
     * @throws \Firebase\JWT\ExpiredException
     */
    public static function validate_token($token) {
        global $CFG;
        /* @noinspection PhpIncludeInspection */
        require_once($CFG->dirroot.'/local/xray/vendor/autoload.php');

        $result = false;
        $key = get_config(self::PLUGIN, 'xraypassword');
        $cid = get_config(self::PLUGIN, 'xrayclientid');

        /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $payload = \Firebase\JWT\JWT::decode($token, $key, [self::ALGO]);
        if (property_exists($payload, 'iss') and ($payload->iss === $cid)) {
            $result = true;
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function generate_access() {
        global $COURSE, $USER, $CFG;

        $result = [];

        $cid = get_config(self::PLUGIN, 'xrayclientid');
        $context = \context_course::instance($COURSE->id);
        $canviewreports = has_capability('local/xray:view', $context);
        if ($canviewreports) {
            $courseobject = (object) [
                'path'      => '/coursereports',
                'parameter' => [
                    (object) ['name' => 'name'                                     ],
                    (object) ['name' => 'courseid'                                 ],
                    (object) ['name' => 't'                                        ],
                    (object) ['name' => 'uid'      , 'values'   => [(int)$USER->id]],
                    (object) ['name' => 'forumid'  , 'required' => false           ],
                    (object) ['name' => 'forumtype', 'required' => false           ],
                    (object) ['name' => 'jouleurl' , 'values'   => [$CFG->wwwroot] ],
                    (object) ['name' => 'cid'      , 'values'   => [$cid]          ],
                ]
            ];

            $result[] = $courseobject;
        }

        if (has_capability('local/xray:systemreports_view', \context_system::instance())) {
            $domainobject = (object) [
                'path'      => '/domainreports',
                'parameter' => [
                    (object) ['name' => 't'                                     ],
                    (object) ['name' => 'cid'     , 'values' => [$cid]          ],
                    (object) ['name' => 'jouleurl', 'values' => [$CFG->wwwroot] ],
                ]
            ];

            $result[] = $domainobject;
        }

        return $result;
    }

    /**
     * Returns generated JSON Web Token according to the RFC 7519 (https://tools.ietf.org/html/rfc7519)
     * For this to work AWS secret must be configured in the module global settings section.
     * Token expires in token_timeout value. Default is 1h.
     *
     * @return bool|string
     */
    public static function get_token() {
        global $CFG;

        $key = get_config(self::PLUGIN, 'xraypassword');
        $iss = get_config(self::PLUGIN, 'xrayclientid');
        $token = false;

        if (!empty($key) and !empty($iss)) {
            $access = self::generate_access();
            $now = time();
            $payload = [];
            $payload['iss'] = $iss;
            if (!empty($access)) {
                $payload['access'] = $access;
            }
            $payload['nbf'] = $now;
            $payload['exp'] = $now + self::token_timeout();

            /* @noinspection PhpIncludeInspection */
            require_once($CFG->dirroot.'/local/xray/vendor/autoload.php');

            try {
                /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
                $jwt = \Firebase\JWT\JWT::encode($payload, $key, self::ALGO);
                $token = $jwt;
            } catch (\Exception $e) {
                // Silence the exception.
                ($e);
            }
        }

        return $token;
    }

    /**
     * Returns ready parameters to be included in Shiny chart url
     *
     * Example:
     *   $charturl = new moodle_url('https://shinyserver.foo/');
     *   $params = local_xray\local\api\jwthelper::get_token_params();
     *   if ($params !== false) {
     *     $charturl->params($params);
     *   }
     *   return $charturl;
     *
     * @return array|bool
     */
    public static function get_token_params() {
        $token  = self::get_token();
        $cid    = get_config(self::PLUGIN, 'xrayclientid');
        $result = false;
        if (!empty($token) and !empty($cid)) {
            $result = ['t' => $token, 'cid' => $cid];
        }
        return $result;
    }

}
