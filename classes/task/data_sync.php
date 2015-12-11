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
 * @package   local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\task;

use core\task\scheduled_task;
use local_xray\local\api\auto_clean_file;
use local_xray\local\api\data_export;
use local_xray\event\sync_log;
use local_xray\event\sync_failed;
use local_xray\local\api\auto_clean;

defined('MOODLE_INTERNAL') || die();

/**
 * Class data_sync - implementation of the task
 *
 * IMPORTANT NOTICE: Due to a bug https://tracker.moodle.org/browse/MDL-48156
 * this scheduled task will work ONLY in Moodle 2.8.1+
 * In order to backport this for earlier versions of Moodle a classic cron implementation
 * should be used.
 *
 * To manually execute run:
 *
 * php -f admin/tool/task/cli/schedule_task.php -- --execute=\\local_xray\\task\\data_sync
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        global $CFG, $DB;
        try {
            $config = get_config('local_xray');

            // Check if it is enabled?
            if ($config === false) {
                throw new \Exception('Plugin is not configured!');
            }

            if (!$config->enablesync) {
                throw new \Exception('Data Synchronization is not enabled!');
            }

            sync_log::create_msg("Start data sync.")->trigger();

            require_once($CFG->dirroot."/local/xray/lib/vendor/aws/aws-autoloader.php");

            $s3 = new \Aws\S3\S3Client([
                'version' => '2006-03-01',
                'region'  => $config->s3bucketregion,
                'scheme'  => $config->s3protocol,
                'credentials' => [
                    'key'    => $config->awskey,
                    'secret' => $config->awssecret,
                ],
            ]);

            $storage = new auto_clean();
            $DB->set_debug(($CFG->debug == DEBUG_DEVELOPER) && $CFG->debugdisplay);
            $timeend = time() - (2 * HOURSECS);
            data_export::export_csv(0, $timeend, $storage->get_directory());
            $DB->set_debug(false);

            list($compfile, $destfile) = data_export::compress($storage->get_dirbase(), $storage->get_dirname());
            if ($compfile !== null) {
                $cleanfile = new auto_clean_file($compfile);
                ($cleanfile);
                $uploadresult = null;
                // We will try several times to upload file.
                $retrycount = (int)$config->s3uploadretry;
                for ($count = 0; $count < $retrycount; $count++) {
                    try {
                        $uploadresult = $s3->upload($config->s3bucket,
                            $destfile,
                            fopen($compfile, 'rb'),
                            'private',
                            array('debug' => true));
                        break;
                    } catch (\Exception $e) {
                        sync_failed::create_from_exception($e)->trigger();
                        if ($count = ($retrycount - 1)) {
                            throw $e;
                        }
                        sleep(1);
                    }
                }
                $metadata = $uploadresult->get('@metadata');
                if ($metadata['statusCode'] != 200) {
                    throw new \Exception("Upload to S3 bucket failed!");
                }

                // Save counters only when entire process passed OK.
                data_export::store_counters();

                sync_log::create_msg("Uploaded {$destfile}.")->trigger();
            } else {
                sync_log::create_msg("No data to upload.")->trigger();
            }

            sync_log::create_msg("Completed data sync.")->trigger();
        } catch (\Exception $e) {
            if ($DB->get_debug()) {
                $DB->set_debug(false);
            }
            mtrace($e->getMessage());
            mtrace($e->getTraceAsString());
            sync_failed::create_from_exception($e)->trigger();
        }
    }
}
