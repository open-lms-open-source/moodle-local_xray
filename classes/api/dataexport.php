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
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Moodlerooms
 */

namespace local_xray\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class dataexport for exporting raw data for xray processing
 * @package local_xray
 */
class dataexport {
    /**
     * @var mixed
     */
    protected static $meta = null;

    public static function coursecategories($timest, $dir) {

        $sql = "
                SELECT id,
                       name,
                       description
                FROM   {course_categories}
                WHERE  timemodified >= :timemodified";

        $params = array('timemodified' => $timest);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    public static function courseinfo($timest, $dir) {

        $sql = "
            SELECT id,
                   fullname,
                   shortname,
                   summary,
                   category,
                   FROM_UNIXTIME(startdate)    AS startdate,
                   FROM_UNIXTIME(timecreated)  AS timecreated,
                   FROM_UNIXTIME(timemodified) AS timemodified
           FROM    {course}
           WHERE   timecreated >= :timecreated";

        $params = array('timecreated' => $timest);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    public static function userlist($timest, $dir) {

        $sql = "SELECT id,
                       firstname,
                       lastname,
                       '' AS gender,
                       email,
                       suspended,
                       deleted,
                       FROM_UNIXTIME(timecreated)  AS timecreated,
                       FROM_UNIXTIME(timemodified) AS timemodified,
                       FROM_UNIXTIME(firstaccess)  AS firstaccess,
                       FROM_UNIXTIME(lastaccess)   AS lastaccess
                FROM   {user}
                WHERE  deleted = :deleted
                       AND
                       timecreated >= :timecreated";

        $params = array('timecreated' => $timest, 'deleted' => false);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

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
     * @param int $timest
     * @param string $dir
     * @throws \ddl_exception
     */
    public static function accesslog($timest, $dir) {
        global $DB;

        $dbmanager = $DB->get_manager();
        $tablename = 'usercoursetmp';
        $table     = new \xmldb_table($tablename);
        $userid    = $table->add_field('userid'  , XMLDB_TYPE_INTEGER, '10', false, XMLDB_NOTNULL, false, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', false, XMLDB_NOTNULL, false, null, $userid);
        $table->add_index('tmpckey' , XMLDB_INDEX_NOTUNIQUE, array('userid', 'courseid'));

        if (!$dbmanager->table_exists($table)) {
            $dbmanager->create_table($table);
        } else {
            // Do cleanup.
            $DB->delete_records($table->getName());
        }

        $sqli = "
            INSERT INTO {usercoursetmp} (userid, courseid)
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

        // Now export.
        $sql = "
            SELECT l.id,
                   l.userid AS participantid,
                   l.course AS courseid,
                   FROM_UNIXTIME(l.time) AS time,
                   l.ip,
                   l.action,
                   l.info,
                   l.module,
                   l.url
            FROM   {log} l,
                   {usercoursetmp} rx
            WHERE rx.courseid = l.course
                  AND
                  rx.userid = l.userid
                  AND
                  l.time >= :time";

        $params = array('time' => $timest, 'ctxt' => CONTEXT_COURSE, 'deleted' => false);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    /**
     * Keeping this for historical reasons only. Should not be used.
     * @param int $timest
     * @param string $dir
     */
    public static function accesslog_old($timest, $dir) {

        $sql = "
            SELECT l.id,
                   l.userid AS participantid,
                   l.course AS courseid,
                   FROM_UNIXTIME(l.time) AS time,
                   l.ip,
                   l.action,
                   l.info,
                   l.module,
                   l.url
            FROM   {log} l
            INNER JOIN
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
              ) rx ON rx.courseid = l.course AND rx.userid = l.userid
            WHERE l.time >= :time";

        $params = array('time' => $timest, 'ctxt' => CONTEXT_COURSE, 'deleted' => false);

        self::doexport($sql, $params, 'accesslog_old', $dir);
    }

    public static function forums($timest, $dir) {

        $sql = "
            SELECT id,
                   course AS courseid,
                   type,
                   name,
                   intro,
                   FROM_UNIXTIME(timemodified) AS timemodified
            FROM   {forum}
            WHERE  timemodified >= :time";

        $params = array('time' => $timest);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    public static function threads($timest, $dir) {

        $sql = "
            SELECT id,
                   forum AS forumid,
                   name,
                   userid AS participantid,
                   groupid,
                   FROM_UNIXTIME(timemodified) AS timemodified
            FROM   {forum_discussions}
            WHERE  timemodified >= :time";

        $params = array('time' => $timest);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    public static function posts($timest, $dir) {

        $sql = "
            SELECT id,
                   parent,
                   discussion as threadid,
                   userid as participantid,
                   FROM_UNIXTIME(created)  AS created,
                   FROM_UNIXTIME(modified) AS modified,
                   subject,
                   message
            FROM {forum_posts}
            WHERE created >= :created
        ";

        $params = array('created' => $timest);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

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

    public static function grades($timest, $dir) {
        ($timest);

        $sql = "
          SELECT     gg.id,
                     gg.userid AS participantid,
                     cm.instance AS quizid,
                     gg.rawgrade,
                     gg.finalgrade
          FROM       {grade_grades}   gg
          INNER JOIN {course_modules} cm ON cm.id     = gg.itemid
          INNER JOIN {modules}        mo ON cm.module = mo.id     AND mo.name = :module
          WHERE      cm.added >= :added";

        $params = array('added' => $timest, 'module' => 'quiz');

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

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

        } while($counter >= $pos);

    }

    /**
     * @param string $prefix
     * @return string
     */
    protected static function generatefilename($prefix) {
        return $prefix.'_'.(string)(int)(microtime(true)*1000.0).'.tar.gz';
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

    public static function exportmetadata($dir) {
        $exportf  = sprintf('%s%smeta.json', $dir, DIRECTORY_SEPARATOR);

        if (!empty(self::$meta)) {
            $jsexport = json_encode(self::$meta, JSON_PRETTY_PRINT);
            file_put_contents($exportf, $jsexport);
        }
    }

    public static function deletedir($dir) {
        $exportfiles = array_diff(scandir($dir), array('..', '.'));
        foreach ($exportfiles as $file) {
            unlink($dir.DIRECTORY_SEPARATOR.$file);
        }

        rmdir($dir);
    }

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
