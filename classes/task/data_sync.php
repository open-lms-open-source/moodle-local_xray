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
 * Scheduled task for data sync with XRay.
 *
 * @package local_xray
 * @author Darko Miletic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Moodlerooms
 */

namespace local_xray\task;

use core\task\scheduled_task;

defined('MOODLE_INTERNAL') || die();

/**
 * Class data_sync - implementation of the task
 * @package local_xray
 */
class data_sync extends scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('datasync', 'local_xray');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG;
        try {

            if (is_callable('mr_off') and mr_off('xray', 'local')) {
                throw new \Exception('Plugin is not enabled in control panel!');
            }

            $config = get_config('local_xray');

            // Check if it is enabled?
            if ($config === false) {
                throw new \Exception('Plugin is not configured!');
            }

            if (!$config->enablesync) {
                throw new \Exception('Data Synchronization is not enabled!');
            }

            require_once($CFG->dirroot.'/local/xray/lib/vendor/aws/aws-autoloader.php');

            $s3 = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => $config->s3bucketregion,
                'credentials' => [
                    'key'    => $config->awskey,
                    'secret' => $config->awssecret,
                ],
            ]);

            if (!$s3->doesBucketExist($config->s3bucket)) {
                throw new \Exception("S3 bucket {$config->s3bucket} does not exist!");
            }

            // TODO: do export and package files.

            // TODO: prepare source and prefix correctly.
            $source = '';
            $prefix = '';
            $s3->uploadDirectory($source, $config->s3bucket, $prefix, array('debug' => true));

        } catch (\Exception $e) {
            \local_xray\event\sync_failed::create_from_exception($e)->trigger();
        }
    }
}
