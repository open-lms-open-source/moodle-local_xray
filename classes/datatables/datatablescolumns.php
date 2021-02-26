<?php
// @codingStandardsIgnoreFile
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
 * Create column for Datatable Jquery plugin integration.
 *
 * @package local_xray
 * @author Pablo Pagnone
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xray\datatables;

defined('MOODLE_INTERNAL') || die();

/**
 * Class datatablescolumns
 * @package local_xray
 */
class datatablescolumns {

    /**
     * Id of column
     * @var string
     */
    public $mData;

    /**
     * Is searchable
     * @var boolean
     */
    public $bSearchable;

    /**
     * Text to show
     * @var string
     */
    public $text;

    /**
     * Is sortable
     * @var boolean
     */
    public $bSortable;

    /**
     * Show column
     * @var boolean
     */
    public $bVisible;

    /**
     * Width column
     * @var string
     */
    public $sWidth;

    /**
     * @param $mdata
     * @param string $text
     * @param bool|false $search
     * @param bool|true $sortable
     * @param bool|true $visible
     * @param string $width
     */
    public function __construct($mdata, $text = '', $search = false, $sortable = true, $visible = true, $width = '') {
        $this->mData = $mdata;
        $this->text = $text;
        $this->bSearchable = $search;
        $this->bSortable = $sortable;
        $this->bVisible = $visible;
        $this->sWidth = $width;
    }
}
