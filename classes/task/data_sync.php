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
use local_xray\api\dataexport;

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
 * php admin/tool/task/cli/schedule_task.php --execute=\\local_xray\\task\\data_sync
 *
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

            \local_xray\event\sync_log::create_msg("Start data sync.")->trigger();

            require_once($CFG->dirroot.'/local/xray/lib/vendor/aws/aws-autoloader.php');

            $s3 = new \Aws\S3\S3Client([
                'version' => '2006-03-01',
                'region'  => $config->s3bucketregion,
                'credentials' => [
                    'key'    => $config->awskey,
                    'secret' => $config->awssecret,
                ],
            ]);

            if (!$s3->doesBucketExist($config->s3bucket)) {
                throw new \Exception("S3 bucket {$config->s3bucket} does not exist!");
            }

            // Get last export timestamp. If none use 0.
            $data = \local_xray\api\wsapi::datalist();
            if ($data === false) {
                \local_xray\api\xrayws::instance()->print_error();
            }

            $timest = 0;
            $dates = array();
            foreach ($data as $item) {
                try {
                    $dt = new \DateTime($item->added);
                    $dates[] = $dt->getTimestamp();
                } catch (\Exception $e) {
                    // Silence it.
                }
            }
            if (!empty($dates)) {
                arsort($dates);
                $timest = $dates[0];
            }

            $dirbase  = dataexport::getdir();
            $dirname  = uniqid('export_', true);
            $transdir = $dirbase.DIRECTORY_SEPARATOR.$dirname;
            make_writable_directory($transdir);

            dataexport::exportcsv($timest, $transdir);

            list($compfile, $destfile) = dataexport::compresstargz($dirbase, $dirname);
            if ($compfile !== null) {
                $uploadresult = $s3->upload($config->s3bucket,
                    $destfile,
                    fopen($compfile, 'rb'),
                    'private',
                    array('debug' => true));
                $metadata = $uploadresult->get('@metadata');
                if ($metadata['statusCode'] != 200) {
                    throw new \Exception("Upload to S3 bucket failed!");
                }

                \local_xray\event\sync_log::create_msg("Uploaded {$destfile}.")->trigger();

                unlink($compfile);
            } else {
                \local_xray\event\sync_log::create_msg("No data to upload.")->trigger();
            }

            // Remove the directory.
            dataexport::deletedir($transdir);

            \local_xray\event\sync_log::create_msg("Completed data sync.")->trigger();

        } catch (\Exception $e) {
            mtrace($e->getMessage());
            \local_xray\event\sync_failed::create_from_exception($e)->trigger();
        }
    }
}
