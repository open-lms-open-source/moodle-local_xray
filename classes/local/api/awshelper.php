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

defined('MOODLE_INTERNAL') || die();

/**
 * @param  null|\stdClass $config
 * @param  bool $cache
 * @return \Aws\S3\S3Client|null
 * @throws \Exception
 */
function get_s3client($config = null, $cache = true) {
    global $CFG;

    static $s3 = null;

    if ($cache and ($s3 !== null)) {
        return $s3;
    }

    if (empty($config)) {
        $config = get_config('local_xray');
    }

    // Check if it is enabled?
    if ($config === false) {
        throw new \Exception("Unable to create S3 client!");
    }

    /* @noinspection PhpIncludeInspection */
    require_once($CFG->dirroot."/local/xray/lib/vendor/aws/aws-autoloader.php");

    /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
    /* @noinspection PhpUndefinedClassInspection */
    $s3 = \Aws\S3\S3Client::factory(
        [
              \Aws\Common\Enum\ClientOptions::VERSION         => '2006-03-01'
            , \Aws\Common\Enum\ClientOptions::REGION          => $config->s3bucketregion
            , \Aws\Common\Enum\ClientOptions::SCHEME          => $config->s3protocol
            , \Aws\Common\Enum\ClientOptions::BACKOFF         => true
            , \Aws\Common\Enum\ClientOptions::BACKOFF_RETRIES => (int)$config->s3uploadretry
            , \Aws\Common\Enum\ClientOptions::BACKOFF_LOGGER  => new \Guzzle\Log\ClosureLogAdapter(
            function ($message) {
                mtrace($message);
            })
            , \Aws\Common\Enum\ClientOptions::CREDENTIALS     => [
                  'key'    => $config->awskey
                , 'secret' => $config->awssecret
            ]
        ]
    );

    return $s3;
}
