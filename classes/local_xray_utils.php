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
	public $mdata;
	public $bSearchable;
	public $bSortable;

	public function __construct($mdata) {
		$this->mData = $mdata;
		$this->bSearchable = false;
		$this->bSortable = false;
	}
}

/**
 * Create configuration for Datatable Jquery plugin integration.
 * @package local_xray
 * @author Pablo Pagnone
 */
class local_xray_datatable {
	
	public $id;
	public $jsonurl;
	public $paging = true;
	public $lengthMenu = array(5, 10, 50, 100);
	public $search = false;
	public $sProcessingMessage =  "Fetching Data, Please wait..."; // TODO:: Implement get_string()
	public $columns = array();

	public function __construct($id, $jsonurl, $columns) {
		$this->id = $id;
		$this->jsonurl = $jsonurl;
		$this->columns = $columns;
	}
}