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
 * JSON validation helpers.
 *
 * @package   local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class validationhelper
 * @package local_xray
 */
abstract class validationhelper {

    /**
     * @param  string[] $pattern
     * @param  string[] $replacement
     * @param  string   $subject
     * @return string
     */
    protected static function replace(array $pattern, array $replacement, $subject) {
        $result = '';

        if (!empty($subject)) {
            $localresult = preg_replace($pattern, $replacement, $subject);
            if ($localresult != $subject) {
                $result = $localresult;
            }
        }

        return $result;
    }

    /**
     * @param  string[] $replacement
     * @param  string   $url
     * @return string
     */
    protected static function url_replace(array $replacement, $url) {
        $subject = parse_url($url, PHP_URL_PATH);
        $pattern = [
            '#^/([^/]+)/course/([^/]+)/([^/]+)$#',
            '#^/([^/]+)/course/([^/]+)/([^/]+)/elements/([^/]+)$#',
            '#^/([^/]+)/data/([^/]+)/([^/]+)/accessible$#',
            '#^/user/login$#',
            '#^/user/accesstoken$#',
            '#^/((?!(course|data))[^/]+)$#'
        ];

        return self::replace($pattern, $replacement, $subject);
    }

    /**
     * @param  string $url
     * @return string
     */
    public static function generate_schema_name($url) {
        $replacement = [
            'course-report-${3}-schema.json',
            'course-element-${3}-${4}-schema.json',
            'data-accessible-${2}-${3}-schema.json',
            'user-login-schema.json',
            'user-accesstoken-schema.json',
            'domain-schema.json'
        ];

        return self::url_replace($replacement, $url);
    }

    /**
     * @param  string $url
     * @return null|string
     */
    public static function get_schema($url) {
        global $CFG;
        $filename = self::generate_schema_name($url);
        $result = null;
        if (!empty($filename)) {
            $result = realpath($CFG->dirroot.'/local/xray/schemas/'.$filename);
        }
        return $result;
    }

    /**
     * @param  string  $json - json incoming data
     * @param  string  $url - url that was used
     * @return string[]
     */
    public static function validate_schema($json, $url) {
        global $CFG;

        /* @noinspection PhpIncludeInspection */
        require_once($CFG->dirroot.'/local/xray/vendor/autoload.php');

        $file = self::get_schema($url);
        if (empty($file)) {
            // No schema for validation. Silently continue.
            return [];
        }

        $data = json_decode($json);
        $cerr = json_last_error();
        if ($cerr != JSON_ERROR_NONE) {
            return ["JSON decoding error: ".json_last_error_msg()];
        }

        try {
            /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
            /* @noinspection PhpUndefinedClassInspection */
            $retriever = new \JsonSchema\Uri\UriRetriever;
            $schema = $retriever->retrieve('file://' . $file);

            /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
            /* @noinspection PhpUndefinedClassInspection */
            $refresolver = new \JsonSchema\RefResolver();
            $refresolver->resolve($schema);

            // Validate.
            /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
            /* @noinspection PhpUndefinedClassInspection */
            $validator = new \JsonSchema\Validator();
            $validator->check($data, $schema);
            $result = [];
            $resultarr = $validator->getErrors();

            $retriever   = null;
            $schema      = null;
            $data        = null;
            $refresolver = null;
            $validator   = null;

            if (!empty($resultarr)) {
                foreach ($resultarr as $error) {
                    $result[] = 'Property: '.$error['property'].
                        ' Message: '.$error['message'].
                        ' Constraint: '.$error['constraint'];
                }
            }
        } catch (\Exception $e) {
            $result = ['Exception: '.$e->getMessage()];
        }

        return $result;
    }
}
