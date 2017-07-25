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
 * AWS validation helpers.
 *
 * @package   local_xray
 * @author    Darko Miletic
 * @copyright Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class validationaws
 * @package local_xray
 */
abstract class validationaws {

    /**
     * Disable Time tracing for not damaging json response
     */
    const DISABLE_TIME_TRACE = true;

    /**
     * This test can indicate several issues:
     * Plugin is not configured
     * Plugin is not correctly configured (incorrect clientid, password or URL)
     * Lack of conectivity (external or internal conectivity issues)
     * Web service is down
     * Malformed responses
     *
     * @return validationresponse
     * @throws \moodle_exception
     */
    public static function check_ws_connect() {
        global $CFG;

        $wsapisteps = 5;
        $wsapires = new validationresponse('ws_connect', $wsapisteps);

        $config = get_config('local_xray');
        if (empty($config)) {
            $wsapires->add_result(get_string("error_wsapi_config_params_empty", wsapi::PLUGIN));
            return $wsapires;
        }

        /** @var string[] $params */
        $params = [
              'xrayurl'
            , 'xrayusername'
            , 'xraypassword'
            , 'xrayclientid'
        ];

        foreach ($params as $property) {
            if (!isset($config->{$property}) || empty($config->{$property})) {
                $wsapires->add_result(get_string("error_wsapi_config_{$property}", wsapi::PLUGIN));
            }
        }

        if (!$wsapires->is_successful()) {
            return $wsapires;
        }

        try {
            // Testing login.
            $wsapires->set_reason(get_string('error_wsapi_reason_login', wsapi::PLUGIN));
            $wsapires->set_reason_fields(array('xrayurl', 'xrayusername', 'xraypassword'));

            // Omit cache for testing that login works.
            $CFG->forced_plugin_settings['local_xray']['curlcache'] = 0;

            $loginres = wsapi::login();
            if (!$loginres) {
                throw new \moodle_exception(xrayws::instance()->errorinfo());
            }

            // Increase step.
            $wsapires->step();

            // Testing the query endpoints.
            $wsapires->set_reason(get_string('error_wsapi_reason_accesstoken', wsapi::PLUGIN));
            $wsapires->set_reason_fields(array('xrayurl', 'xrayclientid'));
            $tokenres = wsapi::accesstoken();
            if (!$tokenres) {
                throw new \moodle_exception(xrayws::instance()->errorinfo());
            }
            // Increase step.
            $wsapires->step();

            // Testing login.
            $wsapires->set_reason(get_string('error_wsapi_reason_accountcheck', wsapi::PLUGIN));
            $wsapires->set_reason_fields(array('xrayurl', 'xrayusername', 'xraypassword'));
            $accountchkres = wsapi::accountcheck();
            if (!$accountchkres) {
                throw new \moodle_exception(xrayws::instance()->errorinfo());
            }
            // Increase step.
            $wsapires->step();

            $wsapires->set_reason(get_string('error_wsapi_reason_domaininfo', wsapi::PLUGIN));
            $wsapires->set_reason_fields(array('xrayurl', 'xrayclientid'));
            $domainres = wsapi::domaininfo();
            if (!$domainres) {
                throw new \moodle_exception(xrayws::instance()->errorinfo());
            } else {
                $requiredkeys = array(
                    'name',
                    'courses',
                    'activecourses',
                    'analysedcourses',
                    'participants',
                    'instructors',
                    'totalreports'
                );

                $domainarr = (array) $domainres->data;
                $numexistingkeys = count(array_intersect_key(array_flip($requiredkeys), $domainarr));
                $numreqkeys = count($requiredkeys);

                if ($numexistingkeys !== $numreqkeys) {
                    $missingkeys = [];
                    foreach ($requiredkeys as $key) {
                        if (!array_key_exists($key, $domainarr)) {
                            $missingkeys[] = $key;
                        }
                    }

                    $error = get_string(
                        "error_wsapi_domaininfo_incomplete",
                        wsapi::PLUGIN, implode(", ", $missingkeys)
                    );
                    throw new \moodle_exception($error);
                }
            }
            // Increase step.
            $wsapires->step();

            $wsapires->set_reason(get_string('error_wsapi_reason_courses', wsapi::PLUGIN));
            $wsapires->set_reason_fields(array('xrayurl', 'xrayclientid'));
            $coursesres = wsapi::courses();
            if (!$coursesres) {
                throw new \moodle_exception(xrayws::instance()->errorinfo());
            }
            // Increase step.
            $wsapires->step();

        } catch (\Exception $ex) {
            $wsapires->register_error('wsapi', $ex->getMessage());
        }

        if ($wsapires->is_successful() && !$wsapires->is_finished()) {
            $wsapires->register_error('wsapi');
        }

        return $wsapires;
    }

    /**
     * This test can indicate several issues:
     * Plugin is not configured
     * Plugin is not correctly configured (incorrect AWS credentials)
     * Lack of conectivity (external or internal conectivity issues)
     * S3 is down
     * S3 does not have correct permissions
     *
     * @return validationresponse
     * @throws \moodle_exception
     */
    public static function check_s3_bucket() {
        $awssteps = 5;
        $awsres = new validationresponse('s3_bucket', $awssteps);
        $config = get_config('local_xray');
        if (empty($config)) {
            $awsres->add_result(get_string("error_wsapi_config_params_empty", wsapi::PLUGIN));
            return $awsres;
        }

        /** @var string[] $params */
        $params = [
              'awskey'
            , 'awssecret'
            , 's3bucket'
            , 's3bucketregion'
            , 's3protocol'
            , 's3uploadretry'
        ];

        foreach ($params as $property) {
            if (!isset($config->{$property}) || empty($config->{$property})) {
                $awsres->add_result(get_string("error_awssync_config_{$property}", wsapi::PLUGIN));
            }
        }

        if (!$awsres->is_successful()) {
            return $awsres;
        }

        try {
            // Testing AWS client creation.
            $awsres->set_reason(get_string('error_aws_reason_client_create', wsapi::PLUGIN));
            $awsres->set_reason_fields(
                array(
                    'awskey', 'awssecret',
                    's3bucket', 's3bucketregion',
                    's3protocol', 's3uploadretry'
                )
            );

            $s3 = s3client::create($config);

            // Increase step.
            $awsres->step();

            /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
            /* @noinspection PhpUndefinedClassInspection */
            $task = new \local_xray\task\data_sync();
            $location = $task->get_storageprefix()."{$config->xrayclientid}/";
            $objectkey = $location.'sample.test.upload.txt';
            $objectcontent = "sample test: ".md5(uniqid(rand(), true));

            // Testing AWS object listing.
            $awsres->set_reason(get_string('error_aws_reason_object_list', wsapi::PLUGIN));
            $awsres->set_reason_fields(array('xrayclientid', 'awskey', 'awssecret',
                    's3bucket', 's3bucketregion', 's3protocol', 's3uploadretry'));
            // Try to list the directory and get no more than 1 items to speed things up.
            // It does not matter if there are no objects, we are checking the access rights here.
            $objects = $s3->listObjects(
                [
                      'Bucket'  => $config->s3bucket
                    , 'Prefix'  => $location
                    , 'MaxKeys' => 1
                ]
            );
            ($objects);
            // Increase step.
            $awsres->step();

            // Now we try to upload simple file.
            $awsres->set_reason(get_string('error_aws_reason_upload_file', wsapi::PLUGIN));
            $awsres->set_reason_fields(array('xrayclientid', 'awskey', 'awssecret',
                    's3bucket', 's3bucketregion', 's3protocol', 's3uploadretry'));
            $uploadfile = tmpfile();
            if ($uploadfile !== false) {
                fwrite($uploadfile, $objectcontent);
                rewind($uploadfile);
                $uploadresult = $s3->upload(
                      $config->s3bucket
                    , $objectkey
                    , $uploadfile
                    , 'private'
                    , ['debug' => true]
                );
            } else {
                throw new \moodle_exception("Unable to create temporary file!");
            }
            // Increase step.
            $awsres->step();

            // Now we try to download simple file.
            $awsres->set_reason(get_string('error_aws_reason_download_file', wsapi::PLUGIN));
            $awsres->set_reason_fields(array('xrayclientid', 'awskey', 'awssecret',
                    's3bucket', 's3bucketregion', 's3protocol', 's3uploadretry'));
            $awsobject = $s3->getObject(array(
                'Bucket' => $config->s3bucket,
                'Key'    => $objectkey
            ));

            if ($awsobject['Body'] != $objectcontent) {
                throw new \moodle_exception("S3 bucket corrupt.");
            }

            // Increase step.
            $awsres->step();

            // Now we try to erase simple file.
            $awsres->set_reason(get_string('error_aws_reason_erase_file', wsapi::PLUGIN));
            $awsres->set_reason_fields(array('xrayclientid', 'awskey', 'awssecret',
                    's3bucket', 's3bucketregion', 's3protocol', 's3uploadretry'));
            $delresult = $s3->deleteObject(array(
                'Bucket' => $config->s3bucket,
                'Key'    => $objectkey
            ));

            $delmetadata = $delresult->get('@metadata');

            if ($delmetadata['statusCode'] !== 204) {
                throw new \moodle_exception("Deletion on S3 bucket failed!");
            }
            // Increase step.
            $awsres->step();

        } catch (\Exception $ex) {
            $awsres->register_error('awssync', $ex->getMessage());
        }

        if ($awsres->is_successful() && !$awsres->is_finished()) {
            $awsres->register_error('awssync');
        }

        return $awsres;
    }



    /**
     * This test can indicate several issues:
     * Plugin is not configured
     * Plugin is not correctly configured (incorrect compression parameters)
     * System does not support compression as configured in plugin
     *
     * @return validationresponse
     * @throws \moodle_exception
     */
    public static function check_compress() {
        global $CFG;

        $compresssteps = 0;
        $compressres = new validationresponse('compress', $compresssteps);

        $config = get_config('local_xray');
        if (empty($config)) {
            $compressres->add_result(get_string("error_wsapi_config_params_empty", wsapi::PLUGIN));
            return $compressres;
        }

        $params = [
              'enablepacker' => ['packertar']
            , 'exportlocation' => []
        ];

        foreach ($params as $prop => $subprops) {
            if (!isset($config->{$prop})) {
                $compressres->add_result(get_string("error_compress_config_{$prop}", wsapi::PLUGIN));
            } else if ($config->{$prop} == true) {
                foreach ($subprops as $subprop) {
                    if (!isset($config->{$subprop})
                        || ( gettype($config->{$subprop}) == 'string' && empty($config->{$subprop}))) {
                        $compressres->add_result(get_string("error_compress_config_{$subprop}", wsapi::PLUGIN));
                    }
                }
            }
        }

        if (!$compressres->is_successful()) {
            return $compressres;
        }

        try {
            // Export database and compress files.
            $timeend = time() - (2 * MINSECS);
            $storage = new auto_clean();

            $filelist = [];
            define('DISABLE_MTRACE_DEBUG', self::DISABLE_TIME_TRACE);
            define('DISABLE_EXPORT_COUNTERS', true);

            // We can not permit extreme export. It has to be minimal since it is just test.
            $CFG->forced_plugin_settings['local_xray']['maxrecords'            ] = 10;
            $CFG->forced_plugin_settings['local_xray']['exporttime_hours'      ] = 0;
            $CFG->forced_plugin_settings['local_xray']['exporttime_minutes'    ] = 0.0334; // Set to 2sec.
            $CFG->forced_plugin_settings['local_xray']['disablecounterincrease'] = true;

            data_export::export_csv(0, $timeend, $storage->get_directory());

            // Store list of files before compression.
            $dirlist = $storage->listdir_as_array();
            foreach ($dirlist as $filepath) {
                $filelist[] = basename($filepath);
            }
            $numfiles = count($filelist);

            // Execute compression.
            $compressres->set_reason('');
            $compressres->set_reason_fields(array('packertar'));
            $compressresult = data_export::compress($storage->get_dirbase(), $storage->get_dirname());

            if ($compressresult !== true) { // TGZ Compression.
                list($compfile, $destfile) = $compressresult;
                ($destfile);
                if (empty($compfile)) {
                    throw new \moodle_exception('error_compress_files', wsapi::PLUGIN);
                }

                // Get tgz_extractor library.
                require_once($CFG->dirroot.'/lib/filestorage/tgz_extractor.php');
                require_once($CFG->dirroot.'/lib/filestorage/tgz_packer.php');

                $compfiles = (new \tgz_extractor($compfile))->list_files();
                $tgzfilelist = [];
                foreach ($compfiles as $cfile) {
                    if ('./' !== $cfile->pathname) {
                        $tgzfilelist[] = basename($cfile->pathname);
                    }
                }

                if (count(array_intersect($tgzfilelist, $filelist)) != $numfiles) {
                    throw new \moodle_exception('error_compress_files', wsapi::PLUGIN);
                }

            } else { // BZ2 Compression.
                $bcompfiles = $storage->listdir_as_array();

                $bz2filelist = [];
                foreach ($bcompfiles as $bfile) {
                    $bz2filelist[] = basename($bfile, '.bz2');
                }

                if (count(array_intersect($bz2filelist, $filelist)) != $numfiles) {
                    throw new \moodle_exception('error_compress_files', wsapi::PLUGIN);
                }
            }

        } catch (\Exception $ex) {
            $emsg = $ex->getMessage();
            if (!empty($emsg)) {
                $compressres->register_error('compress', $emsg);
            } else {
                $compressres->add_result(get_string("error_compress_exception", wsapi::PLUGIN, $emsg));
            }
        }

        return $compressres;
    }
}
