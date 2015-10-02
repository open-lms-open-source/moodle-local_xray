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
 * Create column for Datatable Jquery plugin integration.
 * @package local_xray
 * @author Pablo Pagnone
 */
class local_xray_datatableColumn {

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
     * @param bool|false $sortable
     * @param bool|true $visible
     * @param string $width
     */
    public function __construct($mdata, $text = '', $search = false, $sortable = false, $visible = true, $width = '') {
        $this->mData = $mdata;
        $this->text = $text;
        $this->bSearchable = $search;
        $this->bSortable = $sortable;
        $this->bVisible = $visible;
        $this->sWidth = $width;
    }
}

/**
 * Create configuration for Datatable Jquery plugin integration.
 * @package local_xray
 * @author Pablo Pagnone
 */
class local_xray_datatable {

    /**
     * id for table
     * @var string
     */
    public $id;
    
    /**
     * title for table
     * @var string
     */
    public $title;
    
    /**
     * Url to get data with json format.
     * @var string
     */
    public $jsonurl;

    /**
     * Show pager
     * @var boolean
     */
    public $paging;

    /**
     * Change Dom
     * @var string
     */
    public $dom;

    /**
     * Length menu
     * @var array
     */
    public $lengthMenu;

    /**
     * Show search
     * @var boolean
     */
    public $search;

    /**
     * Message to process data
     * @var string
     */
    public $sProcessingMessage;

    /**
     * Message to show in error case.
     * @var string
     */
    public $errorMessage;

    /**
     * Array with objects local_xray_datatableColumn
     * @var array
     */
    public $columns;

    /**
     * Construct
     * 
     * @param integer $id
     * @param string $jsonurl
     * @param array $columns
     * @param bool $search
     * @param bool $paging
     * @param string $dom
     * @param array $lengthMenu
     */
    public function __construct($id, $title, $jsonurl, $columns, $search = false, $paging=true, $dom = 'lftipr',
                                $lengthMenu = array(10, 50, 100)) {
        $this->id = $id;
        $this->title = $title;
        $this->jsonurl = $jsonurl;
        $this->columns = $columns;
        $this->search = $search;
        $this->paging = $paging;
        $this->dom = $dom;
        $this->lengthMenu = $lengthMenu;
        $this->sProcessingMessage = get_string('table_fetchingdata', 'local_xray');
        $this->errorMessage = get_string('error_datatables','local_xray');
    }
}