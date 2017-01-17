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
 * Validation response manager class
 *
 * @package   local_xray
 * @author    David Castro
 * @copyright Copyright (c) 2017 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class validationresponse
 * @package local_xray
 */
class validationresponse {

    private $result;
    private $reason;
    private $reasonfields;
    private $checkkey;

    /**
     * Constructor method
     */
    public function __construct($checkkey) {
        $this->result = array();
        $this->reason = '';
        $this->reasonfields = array();
        $this->checkkey = $checkkey;
    }

    public function add_result($res) {
        $this->result[] = $res;
    }

    public function get_result() {
        return $this->result;
    }

    public function is_successful() {
        return empty($this->result);
    }

    public function set_reason_fields($reasonfields) {
        $this->reasonfields = $reasonfields;
    }

    public function set_reason($reason) {
        $this->reason = $reason;
    }

    /**
     * Creates and an unordered HTML list of xray fields
     * @param string[] $fields String identifies of the fields to be listed
     */
    private function list_fields($fields) {
        if (empty($fields)) {
            return '';
        }

        $res = '<ul>';

        foreach ($fields as $field) {
            $res .= '<li>'.$this->strong(get_string($field, wsapi::PLUGIN)).'</li>';
        }

        $res .= '</ul>';

        return $res;
    }

    /**
     * Wraps string with a strong html tag
     * @param string $str
     */
    private function strong($str) {
        return '<strong>'.$str.'</strong>';
    }

    /**
     * Wraps string with a div html tag with a serviceinfo class
     * @param string $str
     */
    private function service_info($service, $str) {
        $responsetitle = get_string('validate_service_response', wsapi::PLUGIN);

        return '<br><a id="'.$service.'" class="xray_service_info_btn" href>'.$responsetitle.'</a><br /><br />'
               .'<div id="'.$service.'_txt"class="xray_service_info">'.$str.'</div>';
    }

    public function register_error($exkey, $message) {
        $break = '<br />';
        $colon = ': ';
        $htmlfields = $this->list_fields($this->reasonfields);
        $whenstr = get_string('validate_when', wsapi::PLUGIN).' ';
        $fieldstitle = get_string('validate_check_fields', wsapi::PLUGIN).$colon.$break;

        $this->add_result(get_string("error_".$exkey."_exception",
            wsapi::PLUGIN, $whenstr.$this->strong($this->reason).$break.$fieldstitle.$htmlfields.
            $this->service_info($this->checkkey, $message)));
    }
}