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
 * @copyright Copyright (c) 2017 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\local\api;

defined('MOODLE_INTERNAL') || die();

/**
 * Class validationresponse
 * @package local_xray
 */
class validationresponse {

    /**
     * Holds the result if any errors are registered.
     * @var array Error results.
     */
    private $result;

    /**
     * Describes the last reason for an error.
     * @var string Last reason for error.
     */
    private $reason;

    /**
     * Holds the fields that may have caused an error.
     * @var array Error causing fields.
     */
    private $reasonfields;

    /**
     * Identifies the calling check.
     * @var string Key that identifies the check corresponding to this response.
     */
    private $checkkey;

    /**
     * Number of steps for this validation.
     * @var int Number of steps for this validation.
     */
    private $steps;

    /**
     * Current step in validation.
     * @var int Current step.
     */
    private $currstep;

    /**
     * Constructor method.
     * @param string $checkkey Key that identifies the check corresponding to this response.
     * @param int $steps Number of steps for this validation.
     */
    public function __construct($checkkey, $steps) {
        $this->result = array();
        $this->reason = '';
        $this->reasonfields = array();
        $this->checkkey = $checkkey;
        $this->steps = $steps;
        $this->currstep = 0;
    }

    /**
     * Adds a result to the response.
     * @param string $res New result.
     */
    public function add_result($res) {
        $this->result[] = $res;
    }

    /**
     * Returns the result of the response.
     * @return array Array of strings with result.
     */
    public function get_result() {
        return $this->result;
    }

    /**
     * Checks if the validation is successful so far.
     * @return bool true if there were no results, false otherwise.
     */
    public function is_successful() {
        return empty($this->result);
    }

    /**
     * Array of string identifiers for the fields that may have caused an error.
     * @param array $reasonfields Array of field identifiers.
     */
    public function set_reason_fields($reasonfields) {
        $this->reasonfields = $reasonfields;
    }

    /**
     * Set the reason for which this validation may have failed in the current step.
     * @param string $reason Reason message.
     */
    public function set_reason($reason) {
        $this->reason = $reason;
    }

    /**
     * Increase the current step.
     */
    public function step() {
        $this->currstep ++;
    }

    /**
     * Checks if the validation is finished.
     * @return bool true if all steps have been accounted for, false otherwise.
     */
    public function is_finished() {
        return $this->currstep === $this->steps;
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
     * @param string $str String to wrap.
     */
    private function strong($str) {
        return '<strong>'.$str.'</strong>';
    }

    /**
     * Wraps string with a div html tag with a serviceinfo class
     * @param string $service Service identifier.
     * @param string $str Message string.
     */
    private function service_info($service, $str) {
        $responsetitle = get_string('validate_service_response', wsapi::PLUGIN);

        return '<br><a id="'.$service.'" class="xray_service_info_btn" href>'.$responsetitle.'</a><br /><br />'
               .'<div id="'.$service.'_txt"class="xray_service_info">'.$str.'</div>';
    }

    /**
     * Registers an error based on step and optional message
     * @param string $exkey Service identifier.
     * @param string $message Message string.
     */
    public function register_error($exkey, $message = null) {
        $break = '<br />';
        $colon = ': ';
        $numstep = $this->currstep + 1;
        $stepsstr = "($numstep/$this->steps)";
        $htmlfields = $this->list_fields($this->reasonfields);
        $whenstr = !empty($this->reason) ? get_string('validate_when', wsapi::PLUGIN).' ' : '';
        $fieldstitle = get_string('validate_check_fields', wsapi::PLUGIN).$colon.$break;
        $msgstr = $this->service_info($this->checkkey, isset($message) ? $message : $stepsstr);

        $this->add_result(get_string("error_".$exkey."_exception",
                wsapi::PLUGIN, $whenstr.$this->strong($this->reason).$break
                .$fieldstitle.$htmlfields.$msgstr));
    }
}
