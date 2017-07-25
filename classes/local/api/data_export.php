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
 * @package   local_xray
 * @author    Darko Miletic <darko.miletic@blackboard.com>
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class data_export for exporting raw data for xray processing
 *
 * @package   local_xray
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_export {

    const PLUGIN = 'local_xray';
    const RECCOUNT = 500000;

    /**
     * @var array
     */
    protected static $meta = [];

    /**
     * @var array
     */
    protected static $counters = [];

    /**
     * Set it to default.
     */
    public static function reset_counter_storage() {
        self::$counters = [];
    }

    /**
     * @param  string $base
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
     * @return string
     */
    public static function special_join() {
        global $DB;
        $result = '';
        $family = $DB->get_dbfamily();
        if ($family == 'mysql') {
            $result = 'STRAIGHT_JOIN';
        }
        return $result;
    }

    /**
     * @param  string $fieldname
     * @param  bool   $doalias
     * @param  null   $alias - If alias is null original fieldname is used
     * @return string
     */
    public static function to_timestamp($fieldname, $doalias = true, $alias = null) {
        global $DB;
        $format = '';
        // Do not use textual datetime representation in the new format.
        $newformat = get_config(self::PLUGIN, 'newformat');
        if ($newformat) {
            return $fieldname;
        }

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
     * @param  array $addmore
     * @return array
     */
    public static function default_params($addmore = null) {
        $result = ['lastid' => 0];
        if (is_array($addmore)) {
            $result += $addmore;
        }
        return $result;
    }

    /* @noinspection PhpTooManyParametersInspection */
    /**
     * @param  string $field1
     * @param  string $field2
     * @param  int    $from
     * @param  int    $to
     * @param  string $fn
     * @param  string $idfield
     * @param  bool $skipextra
     * @return array
     *
     */
    public static function range_where($field1, $field2 = null, $from, $to, $fn, $idfield = 'id', $skipextra = false) {
        global $DB;

        if (!defined('DISABLE_EXPORT_COUNTERS')) {
            $maxdatestore = get_config(self::PLUGIN, self::get_maxdate_setting($fn));
            if (!empty($maxdatestore)) {
                $from = (int)$maxdatestore;
            }
        } else {
            $skipextra = true;
        }

        $sqlgt = " {$idfield} > :lastid
                   ORDER BY {$idfield} ASC ";

        $sqlparams = [
            ['sql' => $sqlgt, 'params' => null]
        ];

        // Use lastid only if not exporting for the first time.
        $lastidstore = get_config(self::PLUGIN, $fn);
        if (!empty($lastidstore) && !$skipextra) {
            if ($from > $to) {
                if ($DB->get_debug()) {
                    mtrace("BBB - {$fn} - Start date more recent than end date!!! from: {$from} to: {$to}");
                }
            }

            $sqlbetween = " {$field1} BETWEEN (:from + 1) AND :to
                             AND
                             {$idfield} <= :lastid
                             ORDER BY {$idfield} ASC ";
            $sqlparams[] = ['sql' => $sqlbetween, 'params' => ['from' => $from, 'to' => $to]];

            if (!empty($field2)) {
                $sqlbetween2 = " {$field2} BETWEEN (:from + 1) AND :to
                                 AND
                                 {$field1} = 0
                                 AND
                                 {$idfield} <= :lastid
                                 ORDER BY {$idfield} ASC ";
                $sqlparams[] = ['sql' => $sqlbetween2, 'params' => ['from' => $from, 'to' => $to]];

                $sqlbetween3 = " {$field2} BETWEEN (:from + 1) AND :to
                                 AND
                                 {$field1} IS NULL
                                 AND
                                 {$idfield} <= :lastid
                                 ORDER BY {$idfield} ASC ";
                $sqlparams[] = ['sql' => $sqlbetween3, 'params' => ['from' => $from, 'to' => $to]];
            }
        }

        return $sqlparams;
    }

    /**
     * @param  string $method
     * @param  string $optional
     * @return string
     */
    public static function exportpath($method, $optional = '') {
        $newformat = get_config(self::PLUGIN, 'newformat');
        if ($newformat) {
            $fixedprefix = get_config(self::PLUGIN, 'fixedprefix');
            if (empty($optional) && !empty($fixedprefix)) {
                $optional = $fixedprefix;
            }
            $postfix = empty($optional) ? uniqid('_') : $optional;
            $method = $method.DIRECTORY_SEPARATOR.$method.$postfix;
        }

        return $method;
    }

    /**
     * @param  string $sqlbase
     * @param  array  $params
     * @param  string $method
     * @param  string $dir
     * @return void
     */
    public static function dispatch_query($sqlbase, $params, $method, $dir) {
        $counter = 0;
        foreach ($params as $query) {
            $ccounter = self::do_export($sqlbase.$query['sql'], self::default_params($query['params']), $method, $dir, $counter);
            if ($ccounter > $counter) {
                $counter = $ccounter;
            }
        }
    }

    /**
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function coursecategories($timest, $timeend, $dir) {
        $sqltimemodified = self::to_timestamp('timemodified');

        $sql = "
                SELECT id,
                       name,
                       description,
                       {$sqltimemodified},
                       timemodified AS traw
                  FROM {course_categories}
                 WHERE
                       ";
        $wherecond = self::range_where('timemodified', null, $timest, $timeend, __FUNCTION__);
        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * Deleted course categories
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function coursecategories_delete($timest, $timeend, $dir) {
        $sqltimedeleted = self::to_timestamp('timedeleted');

        $sql = "
                SELECT id,
                       category,
                       {$sqltimedeleted},
                       timedeleted AS traw
                  FROM {local_xray_coursecat}
                 WHERE
                       ";
        $wherecond = self::range_where('timedeleted', null, $timest, $timeend, __FUNCTION__, 'id', true);
        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function courseinfo($timest, $timeend, $dir) {
        $sqlstartdate = self::to_timestamp('startdate');
        $sqltimecreated = self::to_timestamp('timecreated');
        $sqltimemodified = self::to_timestamp('timemodified');

        $sql = "
            SELECT id,
                   fullname,
                   shortname,
                   summary,
                   category,
                   format,
                   visible,
                   {$sqlstartdate},
                   {$sqltimecreated},
                   {$sqltimemodified},
                   CASE
                        WHEN timemodified = 0 THEN timecreated
                        ELSE timemodified
                   END AS traw
              FROM {course}
             WHERE
                   (category <> 0)
                   AND
                   ";

        $wherecond = self::range_where('timemodified', 'timecreated', $timest, $timeend, __FUNCTION__);
        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * Deleted courses
     *
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function courseinfo_delete($timest, $timeend, $dir) {
        $sqltimedeleted = self::to_timestamp('timedeleted');

        $sql = "
                SELECT id,
                       course,
                       {$sqltimedeleted},
                       timedeleted AS traw
                  FROM {local_xray_course}
                 WHERE
                       ";
        $wherecond = self::range_where('timedeleted', null, $timest, $timeend, __FUNCTION__, 'id', true);
        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function userlist($timest, $timeend, $dir) {
        $sqltimecreated = self::to_timestamp('timecreated');
        $sqltimemodified = self::to_timestamp('timemodified');
        $sqlfirstaccess = self::to_timestamp('firstaccess');
        $sqllastaccess = self::to_timestamp('lastaccess');

        $sql = "
                SELECT id,
                       firstname,
                       lastname,
                       '' AS gender,
                       email,
                       suspended,
                       deleted,
                       {$sqltimecreated},
                       {$sqltimemodified},
                       {$sqlfirstaccess},
                       {$sqllastaccess},
                       CASE
                            WHEN timemodified = 0 THEN timecreated
                            ELSE timemodified
                       END AS traw
                  FROM {user}
                 WHERE
                       ";

        $wherecond = self::range_where('timemodified', 'timecreated', $timest, $timeend, __FUNCTION__);
        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function userlistv2($timest, $timeend, $dir) {
        $sqltimecreated = self::to_timestamp('timecreated');
        $sqltimemodified = self::to_timestamp('timemodified');
        $sqlfirstaccess = self::to_timestamp('firstaccess');
        $sqllastaccess = self::to_timestamp('lastaccess');

        $sql = "
                SELECT id,
                       username,
                       firstname,
                       lastname,
                       '' AS gender,
                       email,
                       suspended,
                       deleted,
                       {$sqltimecreated},
                       {$sqltimemodified},
                       {$sqlfirstaccess},
                       {$sqllastaccess},
                       CASE
                            WHEN timemodified = 0 THEN timecreated
                            ELSE timemodified
                       END AS traw
                  FROM {user}
                 WHERE
                       ";

        $wherecond = self::range_where('timemodified', 'timecreated', $timest, $timeend, __FUNCTION__);
        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     *
     * Export course enrollments
     *
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function enrolmentv2($timest, $timeend, $dir) {
        $wherecond = self::range_where('ue.timemodified', null, $timest, $timeend, __FUNCTION__, 'ue.id');

        $sql = "
               SELECT ue.id,
                      e.courseid,
                      ue.userid AS participantid,
                      ue.timemodified,
                      ue.timemodified AS traw
                 FROM {user_enrolments} ue
                 JOIN {enrol}            e ON ue.enrolid = e.id
                WHERE
                      EXISTS (SELECT c.id FROM {course} c WHERE e.courseid = c.id AND c.category <> 0)
                      AND
                      EXISTS (SELECT u.id FROM {user}   u WHERE ue.userid  = u.id AND u.deleted   = 0)
                      AND
                          ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     *
     * Export enrollment deletions
     *
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function enrolment_deletev2($timest, $timeend, $dir) {

        $sql = "
                SELECT id,
                       enrolid,
                       userid,
                       courseid,
                       timedeleted,
                       timedeleted AS traw
                  FROM {local_xray_enroldel}
                 WHERE
                       ";
        $wherecond = self::range_where('timedeleted', null, $timest, $timeend, __FUNCTION__, 'id', true);
        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function roles($timest, $timeend, $dir) {
        $wherecond = self::range_where('l.timemodified', null, $timest, $timeend, __FUNCTION__, 'l.id');
        $sqltimemodified = self::to_timestamp('l.timemodified', true, 'timemodified');
        $sql = "
                SELECT l.id AS id,
                       ctx.instanceid AS courseid,
                       l.roleid,
                       l.userid AS participantid,
                       {$sqltimemodified},
                       l.timemodified AS traw
                  FROM {role_assignments} l
                  JOIN {context}          ctx ON l.contextid = ctx.id AND ctx.contextlevel= 50
                 WHERE
                       EXISTS (SELECT u.id FROM {user}   u WHERE l.userid = u.id AND u.deleted = 0)
                       AND
                       EXISTS (SELECT c.id FROM {course} c WHERE ctx.instanceid = c.id AND c.category <> 0)
                       AND
                           ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * Removed role assignments within a course
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function roles_delete($timest, $timeend, $dir) {
        $sqltimedeleted = self::to_timestamp('timedeleted');

        $sql = "
                SELECT id,
                       role,
                       userid,
                       course,
                       {$sqltimedeleted},
                       timedeleted AS traw
                  FROM {local_xray_roleunas}
                 WHERE
                       ";
        $wherecond = self::range_where('timedeleted', null, $timest, $timeend, __FUNCTION__, 'id', true);
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
        $sqltime = self::to_timestamp('l.time', true, 'time');
        $wherecond = self::range_where('l.time', null, $timest, $timeend, __FUNCTION__, 'l.id', true);

        $sql = "
            SELECT l.id,
                   l.userid AS participantid,
                   l.course AS courseid,
                   {$sqltime},
                   l.ip,
                   l.action,
                   l.info,
                   l.module,
                   l.url,
                   l.time AS traw
              FROM {log} l
             WHERE
                   EXISTS (
                        SELECT DISTINCT
                               ra.userid,
                               ctx.instanceid AS courseid
                          FROM {role_assignments} ra
                          JOIN {context}          ctx ON ra.contextid = ctx.id AND ctx.contextlevel = 50
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
     * Export standard log information
     * For now only userid, courseid and timestamp are used in calculations.
     *
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function standardlog($timest, $timeend, $dir) {
        $sqltime = self::to_timestamp('l.timecreated', true, 'time');
        $wherecond = self::range_where('l.timecreated', null, $timest, $timeend, __FUNCTION__, 'l.id', true);

        $sql = "
            SELECT l.id,
                   l.userid AS participantid,
                   l.courseid AS courseid,
                   {$sqltime},
                   l.ip,
                   l.action,
                   l.other AS info,
                   l.component,
                   l.target,
                   l.objecttable,
                   l.objectid,
                   l.timecreated AS traw
              FROM {logstore_standard_log} l
             WHERE
                   EXISTS (
                        SELECT DISTINCT
                               ra.userid,
                               ctx.instanceid AS courseid
                          FROM {role_assignments} ra
                          JOIN {context}          ctx ON ra.contextid = ctx.id AND ctx.contextlevel = 50
                         WHERE
                               EXISTS (SELECT c.id FROM {course} c WHERE ctx.instanceid = c.id AND c.category <> 0)
                               AND
                               EXISTS (SELECT u.id FROM {user}   u WHERE ra.userid = u.id      AND u.deleted = 0)
                               AND
                               ctx.instanceid = l.courseid
                               AND
                               ra.userid = l.userid
                               AND
                               l.courseid <> 0
                   )
                   AND
          ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function forums($timest, $timeend, $dir) {
        $sqltimemodified = self::to_timestamp('f.timemodified', true, 'timemodified');
        $wherecond = self::range_where('f.timemodified', null, $timest, $timeend, __FUNCTION__, 'f.id');

        $sql = "
            SELECT f.id,
                   cm.id    AS activityid,
                   f.course AS courseid,
                   f.type,
                   f.name,
                   f.intro,
                   {$sqltimemodified},
                   f.timemodified AS traw
              FROM {forum} f
              JOIN {course_modules} cm ON cm.instance = f.id
             WHERE
                   EXISTS (SELECT c.id FROM {course}   c WHERE f.course = c.id    AND c.category <> 0)
                   AND
                   EXISTS (SELECT mo.id FROM {modules} mo WHERE mo.name = 'forum' AND cm.module = mo.id)
                   AND
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function threads($timest, $timeend, $dir) {
        $sqltimemodified = self::to_timestamp('f.timemodified', true, 'timemodified');
        $wherecond = self::range_where('f.timemodified', null, $timest, $timeend, __FUNCTION__, 'f.id');
        $sql = "
            SELECT f.id,
                   f.forum AS forumid,
                   f.name,
                   f.userid AS participantid,
                   f.groupid,
                   {$sqltimemodified},
                   f.timemodified AS traw
              FROM {forum_discussions} f
             WHERE
                   EXISTS (SELECT c.id FROM {course} c WHERE f.course = c.id AND c.category <> 0)
                   AND
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * Deleted discussions
     *
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function threads_delete($timest, $timeend, $dir) {
        $sqltimedeleted = self::to_timestamp('timedeleted');
        $wherecond = self::range_where('timedeleted', null, $timest, $timeend, __FUNCTION__, 'id', true);
        $sql = "
            SELECT id,
                   discussion,
                   cm AS activityid,
                   {$sqltimedeleted},
                   timedeleted AS traw
              FROM {local_xray_disc}
             WHERE
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function posts($timest, $timeend, $dir) {
        $sqlcreated = self::to_timestamp('fp.created', true, 'created');
        $sqlmodified = self::to_timestamp('fp.modified', true, 'modified');
        $wherecond = self::range_where('fp.modified', 'fp.created', $timest, $timeend, __FUNCTION__, 'fp.id');

        $sql = "
            SELECT fp.id,
                   fp.parent,
                   fp.discussion AS threadid,
                   fp.userid AS participantid,
                   {$sqlcreated},
                   {$sqlmodified},
                   fp.subject,
                   fp.message,
                   CASE
                        WHEN fp.modified = 0 THEN fp.created
                        ELSE fp.modified
                   END AS traw
              FROM {forum_posts} fp
             WHERE
                   EXISTS (
                      SELECT fd.id
                        FROM {forum_discussions} fd
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
     * Posts delete
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function posts_delete($timest, $timeend, $dir) {
        $sqltimedeleted = self::to_timestamp('timedeleted');
        $wherecond = self::range_where('timedeleted', null, $timest, $timeend, __FUNCTION__, 'id', true);
        $sql = "
            SELECT id,
                   post,
                   discussion,
                   cm AS activityid,
                   {$sqltimedeleted},
                   timedeleted AS traw
              FROM {local_xray_post}
             WHERE
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function hsuforums($timest, $timeend, $dir) {
        $sqltimemodified = self::to_timestamp('f.timemodified', true, 'timemodified');
        $wherecond = self::range_where('f.timemodified', null, $timest, $timeend, __FUNCTION__, 'f.id');

        $sql = "
            SELECT f.id,
                   cm.id    AS activityid,
                   f.course AS courseid,
                   f.type,
                   f.name,
                   f.intro,
                   {$sqltimemodified},
                   f.timemodified AS traw
              FROM {hsuforum} f
              JOIN {course_modules} cm ON cm.instance = f.id
             WHERE
                   EXISTS (SELECT c.id FROM {course} c WHERE f.course = c.id AND c.category <> 0)
                   AND
                   EXISTS (SELECT mo.id FROM {modules} mo WHERE mo.name = 'hsuforum' AND cm.module = mo.id)
                   AND
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function hsuthreads($timest, $timeend, $dir) {
        $sqltimemodified = self::to_timestamp('f.timemodified', true, 'timemodified');
        $wherecond = self::range_where('f.timemodified', null, $timest, $timeend, __FUNCTION__, 'f.id');
        $sql = "
            SELECT f.id,
                   f.forum AS forumid,
                   f.name,
                   f.userid AS participantid,
                   f.groupid,
                   {$sqltimemodified},
                   f.timemodified AS traw
              FROM {hsuforum_discussions} f
             WHERE
                   EXISTS (SELECT c.id FROM {course} c WHERE f.course = c.id AND c.category <> 0)
                   AND
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * Moodlerooms forums discussion delete
     *
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function hsuthreads_delete($timest, $timeend, $dir) {
        $sqltimedeleted = self::to_timestamp('timedeleted');
        $wherecond = self::range_where('timedeleted', null, $timest, $timeend, __FUNCTION__, 'id', true);
        $sql = "
            SELECT id,
                   discussion,
                   cm AS activityid,
                   {$sqltimedeleted},
                   timedeleted AS traw
              FROM {local_xray_hsudisc}
             WHERE
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function hsuposts($timest, $timeend, $dir) {
        $sqlcreated = self::to_timestamp('fp.created', true, 'created');
        $sqlmodified = self::to_timestamp('fp.modified', true, 'modified');
        $wherecond = self::range_where('fp.modified', 'fp.created', $timest, $timeend, __FUNCTION__, 'fp.id');

        $sql = "
            SELECT fp.id,
                   fp.parent,
                   fp.discussion AS threadid,
                   fp.userid AS participantid,
                   {$sqlcreated},
                   {$sqlmodified},
                   fp.subject,
                   fp.message,
                   CASE
                        WHEN fp.modified = 0 THEN fp.created
                        ELSE fp.modified
                   END AS traw
              FROM {hsuforum_posts} fp
             WHERE
                   EXISTS (
                      SELECT fd.id
                        FROM {hsuforum_discussions} fd
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
     * Deleted Moodlerooms forum posts
     *
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function hsuposts_delete($timest, $timeend, $dir) {
        $sqltimedeleted = self::to_timestamp('timedeleted');
        $wherecond = self::range_where('timedeleted', null, $timest, $timeend, __FUNCTION__, 'id', true);
        $sql = "
            SELECT id,
                   post,
                   discussion,
                   cm AS activityid,
                   {$sqltimedeleted},
                   timedeleted AS traw
              FROM {local_xray_hsupost}
             WHERE
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function quiz($timest, $timeend, $dir) {
        $wherecond = self::range_where('q.timemodified', 'q.timecreated', $timest, $timeend, __FUNCTION__, 'q.id');
        $sqltimemodified = self::to_timestamp('q.timemodified', true, 'timemodified');
        $sql = "
            SELECT q.id,
                   cm.id    AS activityid,
                   q.course AS courseid,
                   q.name,
                   q.attempts,
                   q.grade,
                   {$sqltimemodified},
                   q.timemodified AS traw
              FROM {quiz} q
              JOIN {course_modules} cm ON cm.instance = q.id
             WHERE
                   EXISTS (SELECT c.id FROM {course} c WHERE q.course = c.id AND c.category <> 0)
                   AND
                   EXISTS (SELECT mo.id FROM {modules} mo WHERE mo.name = 'quiz' AND cm.module = mo.id)
                   AND
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * Deleted course modules (activities)
     *
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function activity_delete($timest, $timeend, $dir) {
        $sqltimedeleted = self::to_timestamp('timedeleted');
        $wherecond = self::range_where('timedeleted', null, $timest, $timeend, __FUNCTION__, 'id', true);
        $sql = "
            SELECT id,
                   cm as activityid,
                   course,
                   {$sqltimedeleted},
                   timedeleted AS traw
              FROM {local_xray_cm}
             WHERE
                   ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function grades($timest, $timeend, $dir) {
        $wherecond = self::range_where('gg.timemodified', 'gg.timecreated', $timest, $timeend, __FUNCTION__, 'gg.id');

        $special = self::special_join();

        $sql = "
          SELECT $special
                 gg.id,
                 gg.userid AS participantid,
                 cm.id AS activityid,
                 gi.id AS gradeitemid,
                 gi.courseid,
                 gi.itemname,
                 CASE
                      WHEN gi.itemtype = 'course' THEN gi.itemtype
                      WHEN gi.itemtype = 'mod'    THEN gi.itemmodule
                 END AS itemtype,
                 gg.rawgrademax,
                 gg.rawgrademin,
                 gg.rawgrade,
                 gg.finalgrade,
                 gg.locktime,
                 gg.timecreated,
                 gg.timemodified,
                 CASE
                      WHEN COALESCE(gg.timemodified, 0) = 0 THEN gg.timecreated
                      ELSE gg.timemodified
                 END AS traw
            FROM {grade_grades}   gg
            JOIN {grade_items}    gi ON gi.id       = gg.itemid       AND gi.itemtype IN('mod', 'course')
       LEFT JOIN {modules}        mo ON mo.name     = gi.itemmodule   AND gi.itemtype = 'mod'
       LEFT JOIN {course_modules} cm ON cm.instance = gi.iteminstance AND cm.module   = mo.id AND gi.itemtype = 'mod'
           WHERE
                     ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * Export grade history
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     */
    public static function grades_history($timest, $timeend, $dir) {
        global $CFG;
        $disabled = get_config('core', 'disablegradehistory');
        if ($disabled) {
            // Grade history is disabled. Nothing to do.
            return;
        }

        /* @noinspection PhpIncludeInspection */
        require_once($CFG->libdir.'/grade/constants.php');

        $wherecond = self::range_where('gh.timemodified', null, $timest, $timeend, __FUNCTION__, 'gh.id');

        $sql = "
            SELECT gh.id,
                   CASE gh.action
                      WHEN 1 THEN 'INSERT'
                      WHEN 2 THEN 'UPDATE'
                      WHEN 3 THEN 'DELETE'
                      ELSE        'UNKNOWN'
                   END AS action,
                   gh.itemid,
                   gh.userid,
                   gh.rawgrademax,
                   gh.rawgrademin,
                   gh.rawgrade,
                   gh.finalgrade,
                   gh.loggeduser,
                   gh.timemodified,
                   gh.timemodified AS traw
              FROM {grade_grades_history} gh
              JOIN {grade_items}          gi ON gh.itemid = gi.id
             WHERE
        ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param  int $timest
     * @param  int $timeend
     * @param  string $dir
     * @return void
     */
    public static function groups($timest, $timeend, $dir) {
        $wherecond = self::range_where('timemodified', 'timecreated', $timest, $timeend, __FUNCTION__);

        $sql = "
            SELECT id,
                   courseid,
                   name,
                   description,
                   timecreated,
                   timemodified,
                   CASE
                        WHEN timemodified = 0 THEN timecreated
                        ELSE timemodified
                   END AS traw
              FROM {groups} g
             WHERE
                   EXISTS (SELECT c.id FROM {course} c WHERE c.id = g.courseid)
                   AND
        ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param  int $timest
     * @param  int $timeend
     * @param  string $dir
     * @return void
     */
    public static function groups_deleted($timest, $timeend, $dir) {
        $wherecond = self::range_where('timedeleted', null, $timest, $timeend, __FUNCTION__);

        $sql = "
            SELECT id,
                   groupid,
                   timedeleted,
                   timedeleted AS traw
              FROM {local_xray_groupdel}
             WHERE
        ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param  int $timest
     * @param  int $timeend
     * @param  string $dir
     * @return void
     */
    public static function groups_members($timest, $timeend, $dir) {
        $wherecond = self::range_where('timeadded', null, $timest, $timeend, __FUNCTION__);

        $sql = "
            SELECT id,
                   groupid,
                   userid AS participantid,
                   timeadded,
                   timeadded AS traw
              FROM {groups_members} gm
             WHERE
                   EXISTS (SELECT g.id FROM {groups} g WHERE g.id = gm.groupid)
                   AND
                   EXISTS (SELECT u.id FROM {user}   u WHERE u.id = gm.userid AND u.deleted = 0)
                   AND
        ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @param  int $timest
     * @param  int $timeend
     * @param  string $dir
     * @return void
     */
    public static function groups_members_deleted($timest, $timeend, $dir) {
        $wherecond = self::range_where('timedeleted', null, $timest, $timeend, __FUNCTION__);

        $sql = "
            SELECT id,
                   groupid,
                   participantid,
                   timedeleted,
                   timedeleted AS traw
              FROM {local_xray_gruserdel} gd
             WHERE
                   EXISTS (SELECT u.id FROM {user} u WHERE u.id = gd.participantid AND u.deleted = 0)
                   AND
        ";

        self::dispatch_query($sql, $wherecond, __FUNCTION__, $dir);
    }

    /**
     * @return mixed|string
     */
    public static function get_dir() {
        $dir = get_config(self::PLUGIN, 'exportlocation');
        if (!(is_dir($dir) && is_writable($dir))) {
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
     * @param  string $sql       - SQL query to execute
     * @param  array  $params    - Query parameters
     * @param  string $filename  - Base filename and table for export
     * @param  string $dir       - Export location
     * @param  int    $prevcount - Since do_export can be called multiple times for the same table we need to preserve file counter
     * @return int
     * @throws \moodle_exception - in case of db error or export file error
     */
    public static function do_export($sql, $params = null, $filename, $dir, $prevcount = 0) {
        global $DB;

        if (!timer::within_time()) {
            return 0;
        }

        $newformat = get_config(self::PLUGIN, 'newformat');
        $count     = self::get_max_record_count();
        $pos       = 0;
        $counter   = 0;
        $fcount    = $prevcount;
        $recordset = null;
        $lastid    = null;
        $maxdate   = null;
        if (!is_array($params)) {
            $params = [];
        }

        $lastidstore = 0;
        if (!defined('DISABLE_EXPORT_COUNTERS')) {
            $lastidstore = get_config(self::PLUGIN, $filename);
            if (!empty($lastidstore)) {
                $lastidstore = (int)$lastidstore;
                $lastid = $lastidstore;
            }
        }

        if ($newformat) {
            $ndir = $dir.DIRECTORY_SEPARATOR.$filename;
            if (!file_exists($ndir)) {
                make_writable_directory($ndir, false);
            }
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

            $fcount   += 1;
            $filenamer = sprintf('%s_%08d.csv', self::exportpath($filename), $fcount);
            $exportf   = sprintf('%s%s%s', $dir, DIRECTORY_SEPARATOR, $filenamer);
            $file = new csv_file($exportf);
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

            // Store metadata for later.
            self::$meta[] = (object)[
                'name'      => $filenamer,
                'table'     => $filename,
                'delimiter' => $file->delimiter(),
                'enclosure' => $file->enclosure(),
                'escape'    => $file->escape_char(),
                'encoding'  => 'UTF8'
            ];

            $file->close();
            $recordset->close();

            $file      = null;
            $recordset = null;

            $pos    += $count;

        } while (($counter >= $pos) && timer::within_time());

        if (!empty($lastid) && ($lastid > $lastidstore)) {
            self::$counters[] = ['setting' => $filename, 'value' => $lastid];
        }
        if (!empty($maxdate)) {
            self::$counters[] = ['setting' => self::get_maxdate_setting($filename), 'value' => $maxdate];
        }

        return $fcount;
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
     * @throws \moodle_exception
     */
    public static function compress($dirbase, $dirname) {
        $newformat = get_config(self::PLUGIN, 'newformat');
        if ($newformat) {
            $result = self::compress_bzip2_native($dirbase, $dirname);
        } else {
            $usenative = get_config(self::PLUGIN, 'enablepacker');
            if ($usenative) {
                $result = self::compress_targz_native($dirbase, $dirname);
            } else {
                $result = self::compress_targz($dirbase, $dirname);
            }
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
            $clientid = get_config(self::PLUGIN, 'xrayclientid');
            $basefile = self::generate_filename($clientid);
            $archivefile = $dirbase . DIRECTORY_SEPARATOR . $basefile;
            $destfile = $clientid . '/' . $basefile;
            /** @var \tgz_packer $tgzpacker */
            $tgzpacker = get_file_packer('application/x-gzip');
            $tgzpacker->set_include_index(false);
            $result = $tgzpacker->archive_to_pathname($files, $archivefile);
            if (!$result) {
                print_error('error_compress', self::PLUGIN);
            }
        }

        return [$archivefile, $destfile];
    }

    /**
     * @param string $dirbase
     * @param string $dirname
     * @return string[]
     * @throws \moodle_exception
     */
    public static function compress_targz_native($dirbase, $dirname) {
        // Global CFG variable.
        global $CFG;

        $transdir = $dirbase . DIRECTORY_SEPARATOR . $dirname;

        $exportfiles = array_diff(scandir($transdir), ['..', '.']);
        $compfile = null;
        $destfile = null;

        if (!empty($exportfiles)) {
            $clientid = get_config(self::PLUGIN, 'xrayclientid');
            $tarpath = get_config(self::PLUGIN, 'packertar');
            $bintar = empty($tarpath) ? 'tar' : $tarpath;

            // Check if tar is an executable prior to executing.
            if (!is_executable($bintar)) {
                throw new \moodle_exception(get_string('error_compress_packertar_invalid', self::PLUGIN));
            }

            $escdir = escapeshellarg($transdir);
            // We have to use microseconds timestamp because of nodejs...
            $basefile = self::generate_filename($clientid);
            $compfile = $dirbase . DIRECTORY_SEPARATOR . $basefile;
            $escfile = escapeshellarg($compfile);
            $esctar = escapeshellarg($bintar);
            $destfile = $clientid . '/' . $basefile;
            $command = escapeshellcmd("{$esctar} -C {$escdir} -zcf {$escfile} .");
            $ret = 0;
            $lastmsg = system($command, $ret);
            if ($ret != 0) {
                // We have error code should not upload...
                $msg = empty($lastmsg) ? get_string('error_compress_packertar_invalid', self::PLUGIN) : $lastmsg;
                print_error('error_generic', self::PLUGIN, '', $msg);
            }
        }

        return [$compfile, $destfile];
    }

    /**
     * @param  string $dirbase
     * @param  string $dirname
     * @return bool
     * @throws \moodle_exception
     */
    public static function compress_bzip2_native($dirbase, $dirname) {
        $transdir = $dirbase . DIRECTORY_SEPARATOR . $dirname;

        $files = get_directory_list($transdir, ['..', '.'], true, false, true);
        foreach ($files as $file) {
            $ndir = $transdir . DIRECTORY_SEPARATOR . $file;
            $escdir = escapeshellarg($ndir);
            $command = escapeshellcmd("bzip2 -sq {$escdir}");
            $ret = 0;
            $lastmsg = system($command, $ret);
            if ($ret != 0) {
                // We have error code should not upload...
                print_error('error_generic', self::PLUGIN, '', $lastmsg);
            }
        }

        return !empty($files);
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
     * @param string $text
     */
    protected static function mtrace($text) {
        if (!PHPUNIT_TEST && !defined('DISABLE_MTRACE_DEBUG') ) {
            mtrace($text);
        }
    }

    /**
     * @return int
     */
    public static function executiontime() {
        $hours = get_config(self::PLUGIN, 'exporttime_hours');
        $minutes = get_config(self::PLUGIN, 'exporttime_minutes');
        $timeframe = ($hours * HOURSECS) + ($minutes * MINSECS);
        return (int)$timeframe;
    }

    /**
     * @param int    $timest
     * @param int    $timeend
     * @param string $dir
     * @param bool   $disabletimetrace
     */
    public static function export_csv($timest, $timeend, $dir, $disabletimetrace = false) {
        self::$meta = [];
        self::reset_counter_storage();

        $newformat = get_config(self::PLUGIN, 'newformat');

        /** @var array $plugins */
        $plugins = \core_plugin_manager::instance()->get_plugins_of_type('mod');
        /** @var array $logstores */
        $logstores = \core_plugin_manager::instance()->get_plugins_of_type('logstore');

        // In case timeframe is 0 - there would be no limit to the execution.
        timer::start(self::executiontime());

        // Assure we have accurate internal markers.
        self::internal_check_lastid();

        // Order of export matters. Do not change unless sure.
        self::coursecategories($timest, $timeend, $dir);
        self::courseinfo($timest, $timeend, $dir);
        if ($newformat) {
            self::userlistv2($timest, $timeend, $dir);
        } else {
            self::userlist($timest, $timeend, $dir);
        }
        self::groups($timest, $timeend, $dir);
        self::groups_members($timest, $timeend, $dir);
        self::enrolmentv2($timest, $timeend, $dir);
        self::roles($timest, $timeend, $dir);
        // Unfortunately log stores can be uninstalled so we check for that case.
        if (array_key_exists('legacy', $logstores)) {
            self::accesslog($timest, $timeend, $dir);
        } else {
            self::mtrace('Legacy logstore not installed. Skipping.');
        }
        if (array_key_exists('standard', $logstores)) {
            self::standardlog($timest, $timeend, $dir);
        } else {
            self::mtrace('Standard logstore not installed. Skipping.');
        }
        if (array_key_exists('forum', $plugins)) {
            self::forums($timest, $timeend, $dir);
            self::threads($timest, $timeend, $dir);
            self::posts($timest, $timeend, $dir);
        } else {
            self::mtrace('Forum activity not installed. Skipping.');
        }
        // Since Moodlerooms Forum is not core plugin we check for it's presence.
        if (array_key_exists('hsuforum', $plugins)) {
            self::hsuforums($timest, $timeend, $dir);
            self::hsuthreads($timest, $timeend, $dir);
            self::hsuposts($timest, $timeend, $dir);
        } else {
            self::mtrace('Moodlerooms forum activity not installed. Skipping.');
        }
        if (array_key_exists('quiz', $plugins)) {
            self::quiz($timest, $timeend, $dir);
        } else {
            self::mtrace('Quiz activity not installed. Skipping.');
        }
        self::grades($timest, $timeend, $dir);
        self::grades_history($timest, $timeend, $dir);

        // Deleted records go here.
        self::coursecategories_delete($timest, $timeend, $dir);
        self::courseinfo_delete($timest, $timeend, $dir);
        self::enrolment_deletev2($timest, $timeend, $dir);
        self::roles_delete($timest, $timeend, $dir);
        self::activity_delete($timest, $timeend, $dir);
        if (array_key_exists('forum', $plugins)) {
            self::threads_delete($timest, $timeend, $dir);
            self::posts_delete($timest, $timeend, $dir);
        }

        if (array_key_exists('hsuforum', $plugins)) {
            self::hsuthreads_delete($timest, $timeend, $dir);
            self::hsuposts_delete($timest, $timeend, $dir);
        }
        self::groups_deleted($timest, $timeend, $dir);
        self::groups_members_deleted($timest, $timeend, $dir);

        // Export meta.json only in case legacy format is used.
        if (!$newformat) {
            self::export_metadata($dir);
        }

        if (!$disabletimetrace) {
            self::mtrace("Export data execution time: ".timer::end()." sec.");
        }
    }

    /**
     * Get all available export methods
     * @return array[string]string
     */
    public static function elements() {

        /** @var array $plugins */
        $plugins = \core_plugin_manager::instance()->get_plugins_of_type('mod');
        /** @var array $logstores */
        $logstores = \core_plugin_manager::instance()->get_plugins_of_type('logstore');

        $items = [
            'coursecategories'        => 'course_categories'    ,
            'courseinfo'              => 'course'               ,
            'userlist'                => 'user'                 ,
            'enrolment'               => 'role_assignments'     ,
            'grades'                  => 'grade_grades'         ,
            'grades_history'          => 'grade_grades_history' ,
            'activity_delete'         => 'local_xray_cm'        ,
            'coursecategories_delete' => 'local_xray_coursecat' ,
            'courseinfo_delete'       => 'local_xray_course'    ,
            'enrolment_delete'        => 'local_xray_roleunas'  ,
            'threads_delete'          => 'local_xray_disc'      ,
            'posts_delete'            => 'local_xray_post'      ,
            'hsuthreads_delete'       => 'local_xray_hsudisc'   ,
            'hsuposts_delete'         => 'local_xray_hsupost'   ,
            'enrolmentv2'             => 'user_enrolments'      ,
            'enrolment_deletev2'      => 'local_xray_enroldel'  ,
            'userlistv2'              => 'user'                 ,
            'roles'                   => 'role_assignments'     ,
            'roles_delete'            => 'local_xray_roleunas'  ,
            'groups'                  => 'groups'               ,
            'groups_deleted'          => 'local_xray_groupdel'  ,
            'groups_members'          => 'groups_members'       ,
            'groups_members_deleted'  => 'local_xray_gruserdel' ,
        ];

        if (array_key_exists('legacy', $logstores)) {
            $items['accesslog'] = 'log';
        }

        if (array_key_exists('standard', $logstores)) {
            $items['standardlog'] = 'logstore_standard_log';
        }

        if (array_key_exists('forum', $plugins)) {
            $items['forums' ] = 'forum';
            $items['threads'] = 'forum_discussions';
            $items['posts'  ] = 'forum_posts';
        }

        if (array_key_exists('hsuforum', $plugins)) {
            $items['hsuforums' ] = 'hsuforum';
            $items['hsuthreads'] = 'hsuforum_discussions';
            $items['hsuposts'  ] = 'hsuforum_posts';
        }

        if (array_key_exists('quiz', $plugins)) {
            $items['quiz'] = 'quiz';
        }

        return $items;
    }

    /**
     * Method that confirms that last stored id in configuration table actually works for current table id
     * @return void
     */
    public static function internal_check_lastid() {
        global $DB;
        // Provide all table names and obtain max id.
        // In case there is wrong discrepancy ( config lastid > table max(id) ) delete the config value.
        foreach (self::elements() as $setting => $table) {
            $recordset = $DB->get_recordset_sql("SELECT id FROM {{$table}} ORDER BY id DESC", null, 0, 1);
            $recordset->rewind();
            if (!$recordset->valid()) {
                $recordset->close();
                $recordset = null;
                continue;
            }
            $lastid = (int)$recordset->current()->id;
            $recordset->close();
            $recordset = null;
            if ($lastid > 0) {
                $id = get_config(self::PLUGIN, $setting);
                if ($id > $lastid) {
                    // Inconsistency detected.
                    self::delete_setting($setting);
                }
            }
        }
    }

    /**
     * Resets all progress settings
     *
     * @return void
     */
    public static function delete_progress_settings() {
        foreach (self::elements() as $setting => $table) {
            self::delete_setting($setting);
        }
    }

    /**
     * @param string $setting
     * @return void
     */
    public static function delete_setting($setting) {
        unset_config($setting, self::PLUGIN);
        unset_config(self::get_maxdate_setting($setting), self::PLUGIN);
    }

}
