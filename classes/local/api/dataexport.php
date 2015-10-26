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
 * Class dataexport for exporting raw data for xray processing
 *
 * @package local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dataexport {
    /**
     * @var mixed
     */
    protected static $meta = null;

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
     * Generate the greates where condition
     *
     * @param  string $field1
     * @param  string $field2
     * @param  string $value
     * @return string
     */
    public static function greatest($field1, $field2 = null, $value) {
        if ($value == 0) {
            $format = "(COALESCE(%1\$s, 0) >= %3\$s)";
        } else {
            $format = "( (COALESCE(%1\$s, 0) = 0) OR (COALESCE(%1\$s, 0) >= %3\$s) )";
        }
        if (!empty($field2)) {
            if ($value == 0) {
                $sformat = "(COALESCE(%2\$s, 0) >= %3\$s)";
            } else {
                $sformat = "( (COALESCE(%2\$s, 0) = 0) OR (COALESCE(%2\$s, 0) >= %3\$s) )";
            }
            $format = "( ".
                      "( (COALESCE(%1\$s, 0) = 0) AND {$sformat} )".
                      " OR ".
                      $format.
                      " )";
        }
        return sprintf($format, $field1, $field2, (int)$value);
    }

    /**
     * @param string      $field1
     * @param null|string $field2
     * @param int         $from
     * @param null|int    $to
     * @return string
     */
    public static function rangewhere($field1, $field2 = null, $from, $to = null) {
        if (empty($to)) {
            return self::greatest($field1, $field2, $from);
        }
        $format = "( (COALESCE(%1\$s, 0) = 0) OR (COALESCE(%1\$s, 0) BETWEEN %3\$s AND %4\$s) )";
        if (!empty($field2)) {
            $format = "( ".
                      "( (COALESCE(%1\$s, 0) = 0) AND ".
                      "( (COALESCE(%2\$s, 0) = 0) OR (COALESCE(%2\$s, 0) BETWEEN %3\$s AND %4\$s) ) )".
                      " OR ".
                      $format.
                      " )";
        }
        return sprintf($format, $field1, $field2, (int)$from, (int)$to);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function coursecategories($timest, $timeend, $dir) {
        $timemodified = self::to_timestamp('timemodified');
        $wherecond = self::rangewhere('timemodified', null, $timest, $timeend);
        $sql = "
                SELECT id,
                       name,
                       description,
                       {$timemodified}
                FROM   {course_categories}
                WHERE  {$wherecond}";

        $params = ['timestart' => $timest, 'timeend' => $timeend];

        self::doexport($sql, $params, __FUNCTION__, $dir);
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
        $wherecond = self::rangewhere('timemodified', 'timecreated', $timest, $timeend);

        $sql = "
            SELECT id,
                   fullname,
                   shortname,
                   summary,
                   category,
                   format,
                   visible,
                   {$startdate},
                   {$timecreated},
                   {$timemodified}
           FROM    {course}
           WHERE   (category <> 0)
                   AND
                   {$wherecond}";

        self::doexport($sql, null, __FUNCTION__, $dir);
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
        $wherecond = self::rangewhere('timemodified', 'timecreated', $timest, $timeend);

        $sql = "SELECT id,
                       firstname,
                       lastname,
                       '' AS gender,
                       email,
                       suspended,
                       deleted,
                       {$timecreated},
                       {$timemodified},
                       {$firstaccess},
                       {$lastaccess}
                FROM   {user}
                WHERE  deleted = 0
                       AND
                       {$wherecond}";

        $params = null;

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function enrolment($timest, $timeend, $dir) {
        $wherecond = self::rangewhere('l.timemodified', null, $timest, $timeend);
        $timemodified = self::to_timestamp('l.timemodified', true, 'timemodified');
        $sql = "SELECT l.id AS id,
                       cu.id AS courseid,
                       l.roleid,
                       l.userid AS participantid,
                       {$timemodified}
                FROM   {role_assignments} l,
                       {context} c,
                       {user} u,
                       {course} cu
                WHERE  c.contextlevel= :ctxt
                       AND
                       c.instanceid = cu.id
                       AND
                       l.contextid = c.id
                       AND
                       l.userid = u.id
                       AND
                       u.deleted = :deleted
                       AND
                       {$wherecond}
                       ";

        $params = ['ctxt' => CONTEXT_COURSE, 'deleted' => false];

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * Export accesslog
     *
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     * @throws \ddl_exception
     */
    public static function accesslog($timest, $timeend, $dir) {
        global $DB;

        $DB->delete_records('local_xray_uctmp');

        $sqli = "
            INSERT INTO {local_xray_uctmp} (userid, courseid)
            (
                SELECT DISTINCT ra.userid, c.id AS courseid
                FROM   {role_assignments} ra,
                       {context} ctx,
                       {course} c,
                       {user} u
                WHERE  ctx.contextlevel = :ctxt
                       AND
                       ra.contextid = ctx.id
                       AND
                       ctx.instanceid = c.id
                       AND
                       ra.userid = u.id
                       AND
                       u.deleted = :deleted
                       AND
                       c.category <> 0
            )
        ";
        // Update fresh recordset.
        $DB->execute($sqli, array('ctxt' => CONTEXT_COURSE, 'deleted' => false));
        $time = self::to_timestamp('l.time', true, 'time');
        // Now export.
        $sql = "
            SELECT l.id,
                   l.userid AS participantid,
                   l.course AS courseid,
                   {$time},
                   l.ip,
                   l.action,
                   l.info,
                   l.module,
                   l.url
            FROM   {log} l,
                   {local_xray_uctmp} rx
            WHERE rx.courseid = l.course
                  AND
                  rx.userid = l.userid
                  AND
                  l.time BETWEEN :timestart AND :timeend";

        $params = ['timestart' => $timest, 'timeend' => $timeend, 'ctxt' => CONTEXT_COURSE, 'deleted' => false];

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function forums($timest, $timeend, $dir) {
        $timemodified = self::to_timestamp('f.timemodified', true, 'timemodified');
        $wherecond = self::rangewhere('f.timemodified', null, $timest, $timeend);

        $sql = "
            SELECT f.id,
                   f.course AS courseid,
                   f.type,
                   f.name,
                   f.intro,
                   {$timemodified}
            FROM   {forum}  f,
                   {course} c
            WHERE  (f.course = c.id)
                   AND
                   (c.category <> 0)
                   AND
                   {$wherecond}";

        $params = null;

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function threads($timest, $timeend, $dir) {
        $timemodified = self::to_timestamp('f.timemodified', true, 'timemodified');
        $wherecond = self::rangewhere('f.timemodified', null, $timest, $timeend);
        $sql = "
            SELECT f.id,
                   f.forum AS forumid,
                   f.name,
                   f.userid AS participantid,
                   f.groupid,
                   {$timemodified}
            FROM   {forum_discussions} f,
                   {course} c
            WHERE  (f.course = c.id)
                   AND
                   (c.category <> 0)
                   AND
                   {$wherecond}";

        $params = null;

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function posts($timest, $timeend, $dir) {
        $created = self::to_timestamp('fp.created', true, 'created');
        $modified = self::to_timestamp('fp.modified', true, 'modified');
        $wherecond = self::rangewhere('fp.modified', 'fp.created', $timest, $timeend);

        $sql = "
            SELECT fp.id,
                   fp.parent,
                   fp.discussion AS threadid,
                   fp.userid AS participantid,
                   {$created},
                   {$modified},
                   fp.subject,
                   fp.message
            FROM   {forum_posts} fp,
                   {forum_discussions} fd,
                   {course} c
            WHERE  (fp.discussion = fd.id)
                   AND
                   (fd.course = c.id)
                   AND
                   (c.category <> 0)
                   AND
                   {$wherecond}
        ";

        $params = null;

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function hsuforums($timest, $timeend, $dir) {
        $timemodified = self::to_timestamp('f.timemodified', true, 'timemodified');
        $wherecond = self::rangewhere('f.timemodified', null, $timest, $timeend);

        $sql = "
            SELECT f.id,
                   f.course AS courseid,
                   f.type,
                   f.name,
                   f.intro,
                   {$timemodified}
            FROM   {hsuforum}  f,
                   {course} c
            WHERE  (f.course = c.id)
                   AND
                   (c.category <> 0)
                   AND
                   {$wherecond}";

        $params = null;

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function hsuthreads($timest, $timeend, $dir) {
        $timemodified = self::to_timestamp('f.timemodified', true, 'timemodified');
        $wherecond = self::rangewhere('f.timemodified', null, $timest, $timeend);
        $sql = "
            SELECT f.id,
                   f.forum AS forumid,
                   f.name,
                   f.userid AS participantid,
                   f.groupid,
                   {$timemodified}
            FROM   {hsuforum_discussions} f,
                   {course} c
            WHERE  (f.course = c.id)
                   AND
                   (c.category <> 0)
                   AND
                   {$wherecond}";

        $params = null;

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function hsuposts($timest, $timeend, $dir) {
        $created = self::to_timestamp('fp.created', true, 'created');
        $modified = self::to_timestamp('fp.modified', true, 'modified');
        $wherecond = self::rangewhere('fp.modified', 'fp.created', $timest, $timeend);

        $sql = "
            SELECT fp.id,
                   fp.parent,
                   fp.discussion AS threadid,
                   fp.userid AS participantid,
                   {$created},
                   {$modified},
                   fp.subject,
                   fp.message
            FROM   {hsuforum_posts} fp,
                   {hsuforum_discussions} fd,
                   {course} c
            WHERE  (fp.discussion = fd.id)
                   AND
                   (fd.course = c.id)
                   AND
                   (c.category <> 0)
                   AND
                   {$wherecond}
        ";

        $params = null;

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function quiz($timest, $timeend, $dir) {
        $wherecond = self::rangewhere('q.timemodified', 'q.timecreated', $timest, $timeend);
        $timemodified = self::to_timestamp('q.timemodified', true, 'timemodified');
        $sql = "
            SELECT q.id,
                   q.course AS courseid,
                   q.name,
                   q.attempts,
                   q.grade,
                   {$timemodified}
            FROM   {quiz} q,
                   {course} c
            WHERE  (q.course = c.id)
                   AND
                   (c.category <> 0)
                   AND
                   {$wherecond}
        ";

        $params = null;

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function grades($timest, $timeend, $dir) {
        $wherecond = self::rangewhere('gg.timemodified', 'gg.timecreated', $timest, $timeend);

        $sql = "
          SELECT     gg.id,
                     gg.userid AS participantid,
                     gi.iteminstance AS quizid,
                     gg.rawgrade,
                     gg.finalgrade,
                     gg.locktime,
                     gg.timecreated,
                     gg.timemodified
          FROM       {grade_grades} gg
          INNER JOIN {grade_items}  gi ON gi.id = gg.itemid AND gi.itemmodule = 'quiz'
          WHERE      {$wherecond}";

        $params = null;

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @return mixed|string
     */
    public static function getdir() {
        $dir = get_config('local_xray', 'exportlocation');
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
     * @param string $sql
     * @param array $params
     * @param string $filename
     * @param string $dir
     * @return bool
     */
    public static function doexport($sql, $params = null, $filename, $dir) {
        global $DB;

        $count     = 250000;
        $pos       = 0;
        $counter   = 0;
        $fcount    = 1;
        $recordset = null;

        do {
            $recordset = $DB->get_recordset_sql($sql, $params, $pos, $count);
            $recordset->rewind();
            if (!$recordset->valid()) {
                $recordset->close();
                $recordset = null;
                break;
            }

            $filenamer = sprintf('%s_%08d.csv', $filename, $fcount);
            $exportf   = sprintf('%s%s%s', $dir, DIRECTORY_SEPARATOR, $filenamer);
            $file      = new csvfile($exportf);

            foreach ($recordset as $record) {
                $write = $file->writecsv($record);
                if ($write === false) {
                    break;
                }
                $counter++;
            }

            $file->close();
            $recordset->close();

            $file      = null;
            $recordset = null;

            // Store metadata for later.
            self::$meta[] = (object)['name' => $filenamer, 'table' => $filename];

            $pos    += $count;
            $fcount += 1;

        } while ($counter >= $pos);

    }

    /**
     * @param string $prefix
     * @return string
     */
    protected static function generatefilename($prefix) {
        return $prefix.'_'.(string)(int)(microtime(true) * 1000.0).'.tar.gz';
    }

    /**
     * @param string $dirbase
     * @param string $dirname
     * @return string[]
     */
    public static function compress($dirbase, $dirname) {
        $usenative = get_config('local_xray', 'enablepacker');
        if ($usenative) {
            $result = self::compresstargznative($dirbase, $dirname);
        } else {
            $result = self::compresstargz($dirbase, $dirname);
        }

        return $result;
    }

    /**
     * @param string $dirbase
     * @param string $dirname
     * @return string[]
     * @throws \moodle_exception
     */
    public static function compresstargz($dirbase, $dirname) {
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
            $admin = get_config('local_xray', 'xrayadmin');
            $basefile = self::generatefilename($admin);
            $archivefile = $dirbase . DIRECTORY_SEPARATOR . $basefile;
            $destfile = $admin . '/' . $basefile;

            $tgzpacker = get_file_packer('application/x-gzip');
            $result = $tgzpacker->archive_to_pathname($files, $archivefile);
            if (!$result) {
                print_error('error_compress', 'local_xray');
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
    public static function compresstargznative($dirbase, $dirname) {
        $transdir = $dirbase . DIRECTORY_SEPARATOR . $dirname;

        $exportfiles = array_diff(scandir($transdir), ['..', '.']);
        $compfile = null;
        $destfile = null;

        if (!empty($exportfiles)) {
            $admin = get_config('local_xray', 'xrayadmin');
            $tarpath = get_config('local_xray', 'packertar');
            $bintar = empty($tarpath) ? 'tar' : $tarpath;
            $escdir = escapeshellarg($transdir);
            // We have to use microseconds timestamp because of nodejs...
            $basefile = self::generatefilename($admin);
            $compfile = $dirbase . DIRECTORY_SEPARATOR . $basefile;
            $escfile = escapeshellarg($compfile);
            $esctar = escapeshellarg($bintar);
            $destfile = $admin . '/' . $basefile;
            $command = escapeshellcmd("{$esctar} -C {$escdir} -zcf {$escfile} .");
            $ret = 0;
            $lastmsg = system($command, $ret);
            if ($ret != 0) {
                // We have error code should not upload...
                print_error('error_generic', 'local_xray', '', $lastmsg);
            }
        }

        return array($compfile, $destfile);
    }

    /**
     * @param string $dir
     */
    public static function exportmetadata($dir) {
        $exportf  = sprintf('%s%smeta.json', $dir, DIRECTORY_SEPARATOR);

        if (!empty(self::$meta)) {
            $jsexport = json_encode(self::$meta, JSON_PRETTY_PRINT);
            file_put_contents($exportf, $jsexport);
        }
    }

    /**
     * @param string $dir
     */
    public static function deletedir($dir) {
        $exportfiles = array_diff(scandir($dir), array('..', '.'));
        foreach ($exportfiles as $file) {
            unlink($dir.DIRECTORY_SEPARATOR.$file);
        }

        rmdir($dir);
    }

    /**
     * @param int $timest
     * @param int $timeend
     * @param string $dir
     */
    public static function exportcsv($timest, $timeend, $dir) {
        self::$meta = array();

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

        self::exportmetadata($dir);
    }
}
