/**
 * Scripting for table activity report - first login
 * 
 * @author Pablo Pagnone
 */
function local_xray_activityreport_first_login_non_starters(YUI, data) {
	
	$(document).ready(function() {
		$('#activityreport_first_login_non_starters').dataTable( {
	        "bProcessing": true,
	        "bServerSide": true,
		    "paging": true,
		    "searching": false, // Search no implemented in webservice xray.
		    "retrieve": true,
		    "lengthMenu": [ 5, 10, 50, 100 ],
		    /*
		    "oLanguage": {
		      "sProcessing": "Fetching Data, Please wait..."
		    },*/
		  //"sAjaxDataProp": "", // With this, you can change format of json.
		    "sAjaxSource": 'view.php?controller="activityreport"&action="jsonfirstloginnonstarters"&courseid='+data.courseid,
		 // Sortable not implemented in webservice xray.
		    "aoColumns": [
		                  {"mData": "lastname","bSearchable":false, "bSortable": false},
		                  {"mData": "firstname","bSearchable":false, "bSortable": false}
		                 ]    	
		} );
	});
}
