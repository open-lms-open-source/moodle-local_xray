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
use local_xray\local\api\s3client;

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
     * @var \stdClass
     */
    private $config = null;

    /**
     * data_sync constructor.
     */
    public function __construct() {
        $config = get_config('local_xray');
        $this->config = $config;
    }

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('datasync', 'local_xray');
    }

    /**
     * Get storage prefix
     * default value is rawData/
     * it can be configured like this in config.php or control panel (has to end with forward slash):
     *
     * $CFG->forced_plugin_settings['local_xray']['bucketprefix'] = 'somevalue/';
     *
     * @return string bucket storage root directory path
     */
    public function get_storageprefix() {
        if (!isset($this->config->bucketprefix)) {
            $config = 'rawData/';
        } else {
            $config = $this->config->bucketprefix;
        }
        return $config;
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB;
        try {
            // Check if it is enabled?
            if ($this->config === false) {
                throw new \Exception('Plugin is not configured!');
            }

            if (!$this->config->enablesync) {
                throw new \Exception('Data Synchronization is not enabled!');
            }

            sync_log::create_msg("Start data sync.")->trigger();

            $storage = new auto_clean();
            $DB->set_debug(($CFG->debug == DEBUG_DEVELOPER) && $CFG->debugdisplay);
            $timeend = time() - (2 * MINSECS);
            $dir = $storage->get_directory();
            data_export::export_csv(0, $timeend, $dir);
            $DB->set_debug(false);

            $this->upload($storage);

            sync_log::create_msg("Completed data sync.")->trigger();
        } catch (\Exception $e) {
            if ($DB->get_debug()) {
                $DB->set_debug(false);
            }
            mtrace($e->getMessage());
            mtrace($e->getTraceAsString());
            sync_failed::create_from_exception($e)->trigger();
            if (defined('XRAY_RUNNING_CLI_EXPORT') && XRAY_RUNNING_CLI_EXPORT) {
                throw $e;
            }
        }
    }

    /**
     * @param auto_clean $storage
     * @throws \Exception
     */
    protected function upload(auto_clean $storage) {
        if (!empty($this->config->newformat)) {
            $this->upload_new($storage->get_dirbase(), $storage->get_dirname());
        } else {
            $this->upload_legacy($storage->get_dirbase(), $storage->get_dirname());
        }
    }

    /**
     * @param string $dirbase
     * @param string $dirname
     * @throws \Exception
     * @throws \coding_exception
     */
    protected function upload_legacy($dirbase, $dirname) {

        $s3 = s3client::create($this->config);

        list($compfile, $destfile) = data_export::compress($dirbase, $dirname);
        if ($compfile !== null) {
            $cleanfile = new auto_clean_file($compfile);
            ($cleanfile);
            $uploadresult = $s3->upload(
                $this->config->s3bucket,
                $this->get_storageprefix() . $destfile,
                fopen($compfile, 'rb'),
                'private',
                ['debug' => true]
            );

            // Save counters only when entire process passed OK.
            if (empty($this->config->disablecounterincrease)) {
                data_export::store_counters();
            }

            sync_log::create_msg("Uploaded {$destfile}.")->trigger();
        } else {
            sync_log::create_msg("No data to upload.")->trigger();
        }
    }

    /**
     * @param  string $dirbase
     * @param  string $dirname
     * @throws \Exception
     */
    protected function upload_new($dirbase, $dirname) {

        $s3 = s3client::create($this->config);

        $result = data_export::compress($dirbase, $dirname);

        if ($result) {
            $transdir = $dirbase . DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR;
            $s3->uploadDirectory(
                $transdir,
                $this->config->s3bucket,
                $this->get_storageprefix().$this->config->xrayclientid,
                ['debug' => true]
            );

            // Save counters only when entire process passed OK.
            if (empty($this->config->disablecounterincrease)) {
                data_export::store_counters();
            }

            sync_log::create_msg("Uploaded export.")->trigger();
        }
    }
}
