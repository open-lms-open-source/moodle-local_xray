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
 * AWS helper.
 *
 * @package   local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2017 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

use local_aws_sdk\aws_sdk;

defined('MOODLE_INTERNAL') || die();

/**
 * Class  s3client - helper for easy getting of S3 client
 * @package local_xray
 */
abstract class s3client {

    /**
     * @param  null|\stdClass $config
     * @param  bool $cache
     * @return \Aws\S3\S3Client|null
     * @throws \Exception
     */
    public static function create($config = null, $cache = true) {

        static $s3cli = null;

        if ($cache and ($s3cli !== null)) {
            return $s3cli;
        }

        if (empty($config)) {
            $config = get_config('local_xray');
        }

        // Check if it is enabled?
        if ($config === false) {
            throw new \Exception('Unable to create S3 client!');
        }

        aws_sdk::autoload();

        /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        /* @noinspection PhpUndefinedClassInspection */
        $s3cli = \Aws\S3\S3Client::factory(
            [
                  'version'     => '2006-03-01'
                , 'region'      => $config->s3bucketregion
                , 'scheme'      => $config->s3protocol
                , 'retries'     => (int)$config->s3uploadretry
                , 'credentials' => [
                     'key'    => $config->awskey
                   , 'secret' => $config->awssecret
                  ]
            ]
        );

        return $s3cli;
    }

}
