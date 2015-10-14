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
     * @param int $timest
     * @param string $dir
     */
    public static function coursecategories($timest, $dir) {

        $sql = "
                SELECT id,
                       name,
                       description
                FROM   {course_categories}
                WHERE  (timemodified = 0) OR (timemodified >= :timemodified)";

        $params = array('timemodified' => $timest);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param string $dir
     */
    public static function courseinfo($timest, $dir) {
        $startdate = self::to_timestamp('startdate');
        $timecreated = self::to_timestamp('timecreated');
        $timemodified = self::to_timestamp('timemodified');
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
           WHERE   timecreated >= :timecreated";

        $params = array('timecreated' => $timest);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param string $dir
     */
    public static function userlist($timest, $dir) {
        $timecreated = self::to_timestamp('timecreated');
        $timemodified = self::to_timestamp('timemodified');
        $firstaccess = self::to_timestamp('firstaccess');
        $lastaccess = self::to_timestamp('lastaccess');

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
                WHERE  deleted = :deleted
                       AND
                       timecreated >= :timecreated";

        $params = array('timecreated' => $timest, 'deleted' => false);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param string $dir
     */
    public static function enrolment($timest, $dir) {

        $sql = "SELECT l.id AS id,
                       cu.id AS courseid,
                       l.roleid,
                       l.userid AS participantid
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
                       l.timemodified >= :timemodified
                       ";

        $params = array('ctxt' => CONTEXT_COURSE, 'deleted' => false, 'timemodified' => $timest);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * Export accesslog
     *
     * @param int $timest
     * @param string $dir
     * @throws \ddl_exception
     */
    public static function accesslog($timest, $dir) {
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
                  l.time >= :time";

        $params = array('time' => $timest, 'ctxt' => CONTEXT_COURSE, 'deleted' => false);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param string $dir
     */
    public static function forums($timest, $dir) {
        $timemodified = self::to_timestamp('timemodified');
        $sql = "
            SELECT id,
                   course AS courseid,
                   type,
                   name,
                   intro,
                   {$timemodified}
            FROM   {forum}
            WHERE  timemodified >= :time";

        $params = array('time' => $timest);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param string $dir
     */
    public static function threads($timest, $dir) {
        $timemodified = self::to_timestamp('timemodified');
        $sql = "
            SELECT id,
                   forum AS forumid,
                   name,
                   userid AS participantid,
                   groupid,
                   {$timemodified}
            FROM   {forum_discussions}
            WHERE  timemodified >= :time";

        $params = array('time' => $timest);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param string $dir
     */
    public static function posts($timest, $dir) {
        $created = self::to_timestamp('created');
        $modified = self::to_timestamp('modified');
        $sql = "
            SELECT id,
                   parent,
                   discussion as threadid,
                   userid as participantid,
                   {$created},
                   {$modified},
                   subject,
                   message
            FROM {forum_posts}
            WHERE created >= :created
        ";

        $params = array('created' => $timest);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param string $dir
     */
    public static function quiz($timest, $dir) {
        $sql = "
            SELECT id,
                   course AS courseid,
                   name,
                   attempts,
                   grade
            FROM {quiz}
            WHERE timecreated >= :created
        ";

        $params = array('created' => $timest);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * @param int $timest
     * @param string $dir
     */
    public static function grades($timest, $dir) {
        $wherecond = '(gg.timemodified >= :added)';
        if ($timest == 0) {
            $wherecond .= ' OR (gg.timemodified IS NULL)';
        }

        $sql = "
          SELECT     gg.id,
                     gg.userid AS participantid,
                     gi.iteminstance AS quizid,
                     gg.rawgrade,
                     gg.finalgrade,
                     gg.locktime,
                     gg.timecreated,
                     gg.timemodified
          FROM       {grade_grades}   gg
          INNER JOIN {grade_items}    gi ON gi.id = gg.itemid AND gi.itemmodule = :module
          WHERE      {$wherecond}";

        $params = array('added' => $timest, 'module' => 'quiz');

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
    public static function doexport($sql, array $params, $filename, $dir) {
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
            self::$meta[] = (object)array('name' => $filenamer, 'table' => $filename);

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
     * @return mixed
     * @throws \Exception
     */
    public static function compresstargz($dirbase, $dirname) {
        $transdir = $dirbase.DIRECTORY_SEPARATOR.$dirname;

        $exportfiles = array_diff(scandir($transdir), array('..', '.'));
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
                throw new \Exception($lastmsg, $ret);
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
     * @param string $dir
     */
    public static function exportcsv($timest, $dir) {
        self::$meta = array();

        // Order of export matters. Do not change unless sure.
        self::coursecategories($timest, $dir);
        self::courseinfo($timest, $dir);
        self::userlist($timest, $dir);
        self::enrolment($timest, $dir);
        self::accesslog($timest, $dir);
        self::forums($timest, $dir);
        self::threads($timest, $dir);
        self::posts($timest, $dir);
        self::quiz($timest, $dir);
        self::grades($timest, $dir);

        self::exportmetadata($dir);
    }
}
