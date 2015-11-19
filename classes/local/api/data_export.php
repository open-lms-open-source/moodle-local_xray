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
 * Data export API.
 *
 * @package local_xray
 * @author Darko Miletic
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class data_export for exporting raw data for xray processing
 *
 * @package local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_export {

    const PLUGIN = 'local_xray';
    const RECCOUNT = 50000;

    /**
     * @var array
     */
    protected static $meta = array();

    /**
     * @var array
     */
    protected static $counters = array();

    /**
     * @param string $base
     * @return string
     */
    public static function get_maxdate_setting($base) {
        return "{$base}_maxdate";
    }

    /**
     * Determines the maximum ammount of records to be fetched in one take. Default is 50000.
     * To set different value add this to the config.php:
     * $CFG->forced_plugin_settings = array('local_xray' => array('maxrecords' => yourvaluehere));
     *
     * @return int
     */
    public static function get_max_record_count() {
        $result = get_config(self::PLUGIN, 'maxrecords');
        if (empty($result)) {
            $result = self::RECCOUNT;
        }

        return $result;
    }

    /**
     * @param $fieldname
     * @param bool|true $doalias
     * @param null $alias - If alias is null original fieldname is used
     * @return string
     */
    public static function to_timestamp($fieldname, $doalias = true, $alias = null) {
        global $DB;
        $format = '';
        switch($DB->get_dbfamily()) {
            case 'mysql':
                $format = 'FROM_UNIXTIME(%s)';
                break;
            case 'postgres':
                $format = "TO_CHAR(TO_TIMESTAMP(%s), 'YYYY-MM-DD HH24:MI:SS')";
                break;
            case 'mssql':
                $format = "CONVERT(VARCHAR, DATEADD(S, %s, '1970-01-01'), 120)";
                break;
            case 'oracle':
                $format = "TO_CHAR(TO_DATE('1970-01-01', 'YYYY-MM-DD')".
                          " + NUMTODSINTERVAL(%s, 'SECOND'), 'YYYY-MM-DD HH24:MI:SS')";
                break;
            case 'sqlite':
                $format = "DATETIME(%s, 'unixepoch', 'localtime')";
                break;
        }

        if ($doalias) {
            if (empty($alias)) {
                $alias = $fieldname;
            }
            $format .= ' AS %s';
        }

        return sprintf($format, $fieldname, $alias);
    }

    /**
     * @param array $addmore
     * @return array
     */
    public static function default_params($addmore = null) {
        $result = ['lastid' => 0];
        if (is_array($addmore)) {
            $result += $addmore;
        }
        return $result;
    }

    /**
     * @param string      $field1
     * @param null|string $field2
     * @param int         $from
     * @param null|int    $to
     * @param string      $fn
     * @param null|string $idfield
     * @return array
     */
    public static function range_where($field1, $field2 = null, $from, $to, $fn, $idfield = 'id') {
        $maxdatestore = get_config(self::PLUGIN, self::get_maxdate_setting($fn));
        if (!empty($maxdatestore)) {
            $from = $maxdatestore;
        }

        $sqlgt = " {$idfield} > :lastid
                   ORDER BY {$idfield} ASC ";

        $sqlparams = [
            ['sql' => $sqlgt, 'params' => null]
        ];

        // Use lastid only if not exporting for the first time.
        $lastidstore = get_config(self::PLUGIN, $fn);
        if (!empty($lastidstore) && ($fn != 'accesslog')) {
            $sqlbetween = " {$field1} BETWEEN :from+1 AND :to
                             AND
                             {$idfield} <= :lastid
                             ORDER BY {$idfield} ASC ";
            $sqlparams[] = ['sql' => $sqlbetween, 'params' => ['from' => $from, 'to' => $to]];

            $sqlbetween1 = " {$field1} = :from
                             AND
                             {$idfield} > :lastid
                             ORDER BY {$idfield} ASC ";
            $sqlparams[] = ['sql' => $sqlbetween1, 'params' => ['from' => $from, 'to' => $to]];
            if (!empty($field2)) {
                $sqlbetween2 = " {$field2} BETWEEN :from+1 AND :to
                             AND
                             {$field1} = 0
                             AND
                             {$idfield} <= :lastid
                             ORDER BY {$idfield} ASC ";
                $sqlparams[] = ['sql' => $sqlbetween2, 'params' => ['from' => $from, 'to' => $to]];

                $sqlbetween3 = " {$field2} = :from
                             AND
                             {$field1} = 0
                             AND
                             {$idfield} > :lastid
                             ORDER BY {$idfield} ASC ";
                $sqlparams[] = ['sql' => $sqlbetween3, 'params' => ['from' => $from, 'to' => $to]];

            }
        }

        return $sqlparams;
    }

    /**
     * @param  string $sqlbase
     * @param  array  $params
     * @param  string $method
     * @param  string $dir
     * @return void
     */
    public static function dispatch_query($sqlbase, $params, $method, $dir) {
        foreach ($params as $query) {
            self::do_export($sqlbase.$query['sql'], self::default_params($query['params']), $method, $dir);
        }
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function coursecategories($timest, $timeend, $dir) {
        $timemodified = self::to_timestamp('timemodified');

        $sql = "
                SELECT
                       id,
                       name,
                       description,
                       {$timemodified},
                       timemodified AS traw
                FROM   {course_categories}
                WHERE
                       ";
        $wherecond = self::range_where('timemodified', null, $timest, $timeend, __FUNCTION__);
        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function courseinfo($timest, $timeend, $dir) {
        $startdate = self::to_timestamp('startdate');
        $timecreated = self::to_timestamp('timecreated');
        $timemodified = self::to_timestamp('timemodified');

        $sql = "
            SELECT
                   id,
                   fullname,
                   shortname,
                   summary,
                   category,
                   format,
                   visible,
                   {$startdate},
                   {$timecreated},
                   {$timemodified},
                   CASE
                        WHEN timemodified = 0 THEN timecreated
                        ELSE timemodified
                   END AS traw
           FROM    {course}
           WHERE
                   (category <> 0)
                   AND
                   ";

        $wherecond = self::range_where('timemodified', 'timecreated', $timest, $timeend, __FUNCTION__);
        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function userlist($timest, $timeend, $dir) {
        $timecreated = self::to_timestamp('timecreated');
        $timemodified = self::to_timestamp('timemodified');
        $firstaccess = self::to_timestamp('firstaccess');
        $lastaccess = self::to_timestamp('lastaccess');

        $sql = "
                SELECT
                       id,
                       firstname,
                       lastname,
                       '' AS gender,
                       email,
                       suspended,
                       deleted,
                       {$timecreated},
                       {$timemodified},
                       {$firstaccess},
                       {$lastaccess},
                       CASE
                            WHEN timemodified = 0 THEN timecreated
                            ELSE timemodified
                       END AS traw
                FROM   {user}
                WHERE
                       deleted = 0
                       AND
                       ";

        $wherecond = self::range_where('timemodified', 'timecreated', $timest, $timeend, __FUNCTION__);
        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function enrolment($timest, $timeend, $dir) {
        $wherecond = self::range_where('l.timemodified', null, $timest, $timeend, __FUNCTION__, 'l.id');
        $timemodified = self::to_timestamp('l.timemodified', true, 'timemodified');
        $sql = "
                SELECT
                           l.id AS id,
                           c.instanceid AS courseid,
                           l.roleid,
                           l.userid AS participantid,
                           {$timemodified},
                           l.timemodified AS traw
                FROM       {role_assignments} l
                INNER JOIN {context}          c  ON l.contextid = c.id AND c.contextlevel= 50
                WHERE
                           EXISTS (SELECT u.id FROM {user} u WHERE l.userid = u.id AND u.deleted = 0)
                           AND
                           EXISTS (SELECT c.id FROM {course} c WHERE c.instanceid = c.id AND c.category <> 0)
                           AND
                           ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * Export log information
     *
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function accesslog($timest, $timeend, $dir) {
        $time = self::to_timestamp('l.time', true, 'time');
        $wherecond = self::range_where('l.time', null, $timest, $timeend, __FUNCTION__, 'l.id');

        $sql = "
            SELECT
                   l.id,
                   l.userid AS participantid,
                   l.course AS courseid,
                   {$time},
                   l.ip,
                   l.action,
                   l.info,
                   l.module,
                   l.url,
                   l.time AS traw
            FROM   {log} l
            WHERE
                   EXISTS (
                        SELECT DISTINCT ra.userid, ctx.instanceid AS courseid
                        FROM       {role_assignments} ra
                        INNER JOIN {context}          ctx ON ra.contextid = ctx.id AND ctx.contextlevel = 50
                        WHERE
                              EXISTS (SELECT c.id FROM {course} c WHERE ctx.instanceid = c.id AND c.category <> 0)
                              AND
                              EXISTS (SELECT u.id FROM {user}   u WHERE ra.userid = u.id      AND u.deleted = 0)
                              AND
                              ctx.instanceid = l.course
                              AND
                              ra.userid = l.userid
                   )
                   AND
          ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function forums($timest, $timeend, $dir) {
        $timemodified = self::to_timestamp('f.timemodified', true, 'timemodified');
        $wherecond = self::range_where('f.timemodified', null, $timest, $timeend, __FUNCTION__, 'f.id');

        $sql = "
            SELECT
                   f.id,
                   f.course AS courseid,
                   f.type,
                   f.name,
                   f.intro,
                   {$timemodified},
                   f.timemodified AS traw
            FROM   {forum} f
            WHERE
                   EXISTS (SELECT c.id FROM {course} c WHERE f.course = c.id AND c.category <> 0)
                   AND
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function threads($timest, $timeend, $dir) {
        $timemodified = self::to_timestamp('f.timemodified', true, 'timemodified');
        $wherecond = self::range_where('f.timemodified', null, $timest, $timeend, __FUNCTION__, 'f.id');
        $sql = "
            SELECT
                   f.id,
                   f.forum AS forumid,
                   f.name,
                   f.userid AS participantid,
                   f.groupid,
                   {$timemodified},
                   f.timemodified AS traw
            FROM   {forum_discussions} f
            WHERE
                   EXISTS (SELECT c.id FROM {course} c WHERE f.course = c.id AND c.category <> 0)
                   AND
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function posts($timest, $timeend, $dir) {
        $created = self::to_timestamp('fp.created', true, 'created');
        $modified = self::to_timestamp('fp.modified', true, 'modified');
        $wherecond = self::range_where('fp.modified', 'fp.created', $timest, $timeend, __FUNCTION__, 'fp.id');

        $sql = "
            SELECT
                   fp.id,
                   fp.parent,
                   fp.discussion AS threadid,
                   fp.userid AS participantid,
                   {$created},
                   {$modified},
                   fp.subject,
                   fp.message,
                   CASE
                        WHEN fp.modified = 0 THEN fp.created
                        ELSE fp.modified
                   END AS traw
            FROM   {forum_posts} fp
            WHERE
                   EXISTS (
                      SELECT fd.id
                      FROM   {forum_discussions} fd
                      WHERE
                             EXISTS (SELECT c.id FROM {course} c WHERE fd.course = c.id AND c.category <> 0)
                             AND
                             fp.discussion = fd.id
                   )
                   AND
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function hsuforums($timest, $timeend, $dir) {
        $timemodified = self::to_timestamp('f.timemodified', true, 'timemodified');
        $wherecond = self::range_where('f.timemodified', null, $timest, $timeend, __FUNCTION__, 'f.id');

        $sql = "
            SELECT
                   f.id,
                   f.course AS courseid,
                   f.type,
                   f.name,
                   f.intro,
                   {$timemodified},
                   f.timemodified AS traw
            FROM   {hsuforum} f
            WHERE
                   EXISTS (SELECT c.id FROM {course} c WHERE f.course = c.id AND c.category <> 0)
                   AND
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function hsuthreads($timest, $timeend, $dir) {
        $timemodified = self::to_timestamp('f.timemodified', true, 'timemodified');
        $wherecond = self::range_where('f.timemodified', null, $timest, $timeend, __FUNCTION__, 'f.id');
        $sql = "
            SELECT
                   f.id,
                   f.forum AS forumid,
                   f.name,
                   f.userid AS participantid,
                   f.groupid,
                   {$timemodified},
                   f.timemodified AS traw
            FROM   {hsuforum_discussions} f
            WHERE
                   EXISTS (SELECT c.id FROM {course} c WHERE f.course = c.id AND c.category <> 0)
                   AND
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function hsuposts($timest, $timeend, $dir) {
        $created = self::to_timestamp('fp.created', true, 'created');
        $modified = self::to_timestamp('fp.modified', true, 'modified');
        $wherecond = self::range_where('fp.modified', 'fp.created', $timest, $timeend, __FUNCTION__, 'fp.id');

        $sql = "
            SELECT
                   fp.id,
                   fp.parent,
                   fp.discussion AS threadid,
                   fp.userid AS participantid,
                   {$created},
                   {$modified},
                   fp.subject,
                   fp.message,
                   CASE
                        WHEN fp.modified = 0 THEN fp.created
                        ELSE fp.modified
                   END AS traw
            FROM   {hsuforum_posts} fp
            WHERE
                   EXISTS (
                      SELECT fd.id
                      FROM   {hsuforum_discussions} fd
                      WHERE
                             EXISTS (SELECT c.id FROM {course} c WHERE fd.course = c.id AND c.category <> 0)
                             AND
                             fp.discussion = fd.id
                   )
                   AND
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function quiz($timest, $timeend, $dir) {
        $wherecond = self::range_where('q.timemodified', 'q.timecreated', $timest, $timeend, __FUNCTION__, 'q.id');
        $timemodified = self::to_timestamp('q.timemodified', true, 'timemodified');
        $sql = "
            SELECT
                   q.id,
                   q.course AS courseid,
                   q.name,
                   q.attempts,
                   q.grade,
                   {$timemodified},
                   q.timemodified AS traw
            FROM   {quiz} q
            WHERE
                   EXISTS (SELECT c.id FROM {course} c WHERE q.course = c.id AND c.category <> 0)
                   AND
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function grades($timest, $timeend, $dir) {
        $wherecond = self::range_where('gg.timemodified', 'gg.timecreated', $timest, $timeend, __FUNCTION__, 'gg.id');
        $timecreated = self::to_timestamp('gg.timecreated', true, 'timecreated');
        $timemodified = self::to_timestamp('gg.timemodified', true, 'timemodified');

        $sql = "
          SELECT
                     gg.id,
                     gg.userid AS participantid,
                     gi.iteminstance AS quizid,
                     gg.rawgrade,
                     gg.finalgrade,
                     gg.locktime,
                     {$timecreated},
                     {$timemodified},
                     CASE
                          WHEN gg.timemodified = 0 THEN gg.timecreated
                          ELSE gg.timemodified
                     END AS traw
          FROM       {grade_grades} gg
          INNER JOIN {grade_items}  gi ON gi.id = gg.itemid AND gi.itemmodule = 'quiz'
          WHERE
                     ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @return mixed|string
     */
    public static function get_dir() {
        $dir = get_config(self::PLUGIN, 'exportlocation');
        if (empty($dir) or !is_dir($dir) or !is_writable($dir)) {
            $dir = get_config('core', 'tempdir');
        }
        // Normalize final result and remove ending DIRECTORY_SEPARATOR if present.
        $dir = realpath($dir);
        return $dir;
    }

    /**
     * Unfortunately due to way MySQL query execution is implemented in Moodle we can not fetch entire recordset
     *
     * The problem we have to solve is related to the way data are structured and used in Moodle. Excluding log tables
     * all other tables in the system can (and often do) have records that are updated a posteriori. This means that we
     * can not rely only on last read record id but always take into account updated timemodified field if it is
     * available.
     *
     * @param string $sql
     * @param array  $params
     * @param string $filename
     * @param string $dir
     * @return bool
     */
    public static function do_export($sql, $params = null, $filename, $dir) {
        global $DB;

        if (!timer::within_time()) {
            return;
        }

        $count     = self::get_max_record_count();
        $pos       = 0;
        $counter   = 0;
        $fcount    = 1;
        $recordset = null;
        $lastid    = null;
        $maxdate   = null;
        if (!is_array($params)) {
            $params = array();
        }

        $lastidstore = get_config(self::PLUGIN, $filename);
        if (!empty($lastidstore)) {
            $lastid = $lastidstore;
        }

        do {
            if ($lastid !== null) {
                $params['lastid' ] = $lastid;
            }
            $recordset = $DB->get_recordset_sql($sql, $params, 0, $count);
            $recordset->rewind();
            if (!$recordset->valid()) {
                $recordset->close();
                $recordset = null;
                break;
            }

            $filenamer = sprintf('%s_%08d.csv', $filename, $fcount);
            $exportf   = sprintf('%s%s%s', $dir, DIRECTORY_SEPARATOR, $filenamer);
            $file      = new csv_file($exportf);

            foreach ($recordset as $record) {
                $cmaxdate = $record->traw;
                unset($record->traw);
                $write = $file->write_csv($record);
                if ($write === false) {
                    break;
                }
                $counter++;
                if ($record->id > $lastidstore) {
                    $lastid = $record->id;
                }
                if ($maxdate < $cmaxdate) {
                    $maxdate = $cmaxdate;
                }
            }

            $file->close();
            $recordset->close();

            $file      = null;
            $recordset = null;

            // Store metadata for later.
            self::$meta[] = (object)['name' => $filenamer, 'table' => $filename];

            $pos    += $count;
            $fcount += 1;

        } while (($counter >= $pos) && timer::within_time());

        if (!empty($lastid)) {
            self::$counters[] = ['setting' => $filename, 'value' => $lastid];
        }
        if (!empty($maxdate)) {
            self::$counters[] = ['setting' => self::get_maxdate_setting($filename), 'value' => $maxdate];
        }
    }

    /**
     * Save stored counters.
     */
    public static function store_counters() {
        foreach (self::$counters as $item) {
            set_config($item['setting'], $item['value'], self::PLUGIN);
        }
    }

    /**
     * @param string $prefix
     * @return string
     */
    protected static function generate_filename($prefix) {
        return $prefix.'_'.(string)(int)(microtime(true) * 1000.0).'.tar.gz';
    }

    /**
     * @param string $dirbase
     * @param string $dirname
     * @return string[]
     */
    public static function compress($dirbase, $dirname) {
        $usenative = get_config(self::PLUGIN, 'enablepacker');
        if ($usenative) {
            $result = self::compress_targz_native($dirbase, $dirname);
        } else {
            $result = self::compress_targz($dirbase, $dirname);
        }

        return $result;
    }

    /**
     * @param string $dirbase
     * @param string $dirname
     * @return string[]
     * @throws \moodle_exception
     */
    public static function compress_targz($dirbase, $dirname) {
        $transdir = $dirbase . DIRECTORY_SEPARATOR . $dirname;

        // Get the list of files in directory.
        $filestemp = get_directory_list($transdir, '', false, true, true);
        $files = [];
        foreach ($filestemp as $file) {
            $files[$file] = $transdir . DIRECTORY_SEPARATOR . $file;
        }

        $archivefile = null;
        $destfile = null;

        if (!empty($files)) {
            $admin = get_config(self::PLUGIN, 'xrayadmin');
            $basefile = self::generate_filename($admin);
            $archivefile = $dirbase . DIRECTORY_SEPARATOR . $basefile;
            $destfile = $admin . '/' . $basefile;

            $tgzpacker = get_file_packer('application/x-gzip');
            $result = $tgzpacker->archive_to_pathname($files, $archivefile);
            if (!$result) {
                print_error('error_compress', self::PLUGIN);
            }
        }

        return array($archivefile, $destfile);
    }

    /**
     * @param string $dirbase
     * @param string $dirname
     * @return string[]
     * @throws \moodle_exception
     */
    public static function compress_targz_native($dirbase, $dirname) {
        $transdir = $dirbase . DIRECTORY_SEPARATOR . $dirname;

        $exportfiles = array_diff(scandir($transdir), ['..', '.']);
        $compfile = null;
        $destfile = null;

        if (!empty($exportfiles)) {
            $admin = get_config(self::PLUGIN, 'xrayadmin');
            $tarpath = get_config(self::PLUGIN, 'packertar');
            $bintar = empty($tarpath) ? 'tar' : $tarpath;
            $escdir = escapeshellarg($transdir);
            // We have to use microseconds timestamp because of nodejs...
            $basefile = self::generate_filename($admin);
            $compfile = $dirbase . DIRECTORY_SEPARATOR . $basefile;
            $escfile = escapeshellarg($compfile);
            $esctar = escapeshellarg($bintar);
            $destfile = $admin . '/' . $basefile;
            $command = escapeshellcmd("{$esctar} -C {$escdir} -zcf {$escfile} .");
            $ret = 0;
            $lastmsg = system($command, $ret);
            if ($ret != 0) {
                // We have error code should not upload...
                print_error('error_generic', self::PLUGIN, '', $lastmsg);
            }
        }

        return array($compfile, $destfile);
    }

    /**
     * @param string $dir
     */
    public static function export_metadata($dir) {
        $exportf  = sprintf('%s%smeta.json', $dir, DIRECTORY_SEPARATOR);

        if (!empty(self::$meta)) {
            $jsexport = json_encode(self::$meta, JSON_PRETTY_PRINT);
            file_put_contents($exportf, $jsexport);
        }
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function export_csv($timest, $timeend, $dir) {
        self::$meta = array();

        $timeframe = (int)get_config(self::PLUGIN, 'exporttime_hours') * HOURSECS +
                     (int)get_config(self::PLUGIN, 'exporttime_minutes') * MINSECS;
        // In case timeframe is 0 - there would be no limit to the execution.
        timer::start($timeframe);

        // Order of export matters. Do not change unless sure.
        self::coursecategories($timest, $timeend, $dir);
        self::courseinfo($timest, $timeend, $dir);
        self::userlist($timest, $timeend, $dir);
        self::enrolment($timest, $timeend, $dir);
        self::accesslog($timest, $timeend, $dir);
        self::forums($timest, $timeend, $dir);
        self::threads($timest, $timeend, $dir);
        self::posts($timest, $timeend, $dir);
        self::hsuforums($timest, $timeend, $dir);
        self::hsuthreads($timest, $timeend, $dir);
        self::hsuposts($timest, $timeend, $dir);
        self::quiz($timest, $timeend, $dir);
        self::grades($timest, $timeend, $dir);

        self::export_metadata($dir);

        mtrace("Export data execution time: ".timer::end()." sec.");
    }

    /**
     * Resets all progress settings
     *
     * @return void
     */
    public static function delete_progress_settings() {
        $items = ['coursecategories', 'courseinfo',
                  'userlist', 'enrolment', 'accesslog',
                  'forums', 'threads', 'posts', 'hsuforums',
                  'hsuthreads', 'hsuposts', 'quiz', 'grades'];
        foreach ($items as $item) {
            set_config($item, null, self::PLUGIN);
            set_config(self::get_maxdate_setting($item), null, self::PLUGIN);
        }
    }

}
