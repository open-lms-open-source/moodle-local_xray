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

    public static function coursecategories($timest, $dir) {

        $sql = "
                SELECT id,
                       name,
                       description
                FROM   {course_categories}
                WHERE  timemodified > :timemodified";

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
           WHERE   timecreated > :timecreated";

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
                       timecreated > :timecreated";

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
                       l.timemodified > :timemodified
                       ";

        $params = array('ctxt' => CONTEXT_COURSE, 'deleted' => false, 'timemodified' => $timest);

        self::doexport($sql, $params, __FUNCTION__, $dir);
    }

    public static function accesslog($timest, $dir) {

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
            WHERE l.time > :time";

        $params = array('time' => $timest, 'ctxt' => CONTEXT_COURSE, 'deleted' => false);

        self::doexport($sql, $params, __FUNCTION__, $dir);
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
            WHERE  timemodified > :time";

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
            WHERE  timemodified > :time";

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
            WHERE created > :created
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
            WHERE timecreated > :created
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
          FROM       {grade_grades} gg
          INNER JOIN {course_modules} cm ON cm.id = gg.itemid
          INNER JOIN {modules} mo ON cm.module = mo.id AND mo.name='quiz'
          WHERE      cm.added > :added";

        $params = array('added' => $timest);

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

        $count     = 10000;
        $pos       = 0;
        $counter   = 0;
        $file      = new csvfile($dir . DIRECTORY_SEPARATOR. "{$filename}.csv");
        $recordset = null;
        $header    = false;

        do {

            $recordset = $DB->get_recordset_sql($sql, $params, $pos, $count);
            $recordset->rewind();
            if (!$recordset->valid()) {
                break;
            }
            foreach ($recordset as $record) {
                if (!$header) {
                    $write = $file->writecsvheader($record);
                    if ($write === false) {
                        break;
                    }
                    $header = true;
                }
                $write = $file->writecsv($record);
                if ($write === false) {
                    break;
                }
                $counter++;
            }

            $recordset = null;

            $pos += $count;

        } while($counter >= $pos);

        $file->close();

        $file = null;
    }

    /**
     * @param string $dirbase
     * @param string $dirname
     * @return mixed
     * @throws \Exception
     */
    public static function compresstargz($dirbase, $dirname) {
        $transdir = $dirbase.DIRECTORY_SEPARATOR.$dirname;

        $admin = get_config('local_xray', 'xrayadmin');
        $tarpath = get_config('local_xray', 'packertar');
        $bintar  = empty($tarpath) ? 'tar' : $tarpath;
        $escdir  = escapeshellarg($transdir);
        $basefile = $admin.'_'.(string)time().'.tar.gz';
        $compfile = $dirbase.DIRECTORY_SEPARATOR.$basefile;
        $escfile = escapeshellarg($compfile);
        $esctar  = escapeshellarg($bintar);
        $destfile = $admin.'/'.$basefile;
        $command = escapeshellcmd("{$esctar} -C {$escdir} -zcf {$escfile} .");
        $ret = 0;
        $lastmsg = system($command, $ret);
        if ($ret != 0) {
            // We have error code should not upload...
            throw new \Exception($lastmsg, $ret);
        }

        return array($compfile, $destfile);
    }

    public static function exportcsv($timest, $dir) {
        self::accesslog($timest, $dir);
        self::coursecategories($timest, $dir);
        self::courseinfo($timest, $dir);
        self::enrolment($timest, $dir);
        self::forums($timest, $dir);
        self::grades($timest, $dir);
        self::posts($timest, $dir);
        self::userlist($timest, $dir);
        self::threads($timest, $dir);
        self::quiz($timest, $dir);

        // TODO: prepare metadata json file.
    }
}
