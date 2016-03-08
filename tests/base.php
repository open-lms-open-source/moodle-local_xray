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

defined('MOODLE_INTERNAL') || die();

/**
 * Class local_xray_base_testcase - base class defines reusable things
 * Must place abstract here to avoid CI warning for non existing tests.
 */
abstract class local_xray_base_testcase extends advanced_testcase {
    const PLUGIN = 'local_xray';

    /**
     * @return void
     */
    protected function reset_ws() {
        // Reset some internals.
        \local_xray\local\api\xrayws::instance()->resetcookie();
        \local_xray\local\api\xrayws::instance()->getopts();
    }

    /**
     * @return void
     */
    protected function config_set_ok() {
        set_config('xrayusername', 'someuser@domain.com'      , self::PLUGIN);
        set_config('xraypassword', 'somepass'                 , self::PLUGIN);
        set_config('xrayurl'     , 'http://xrayserver.foo.com', self::PLUGIN);
        set_config('xrayclientid', 'demo'                     , self::PLUGIN);
    }

    /**
     * @return void
     */
    protected function config_cleanup() {
        unset_config('xrayusername', self::PLUGIN);
        unset_config('xraypassword', self::PLUGIN);
        unset_config('xrayurl'     , self::PLUGIN);
        unset_config('xrayclientid', self::PLUGIN);
    }

}
