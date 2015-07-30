/**
 * jQuery scripting for students activity
 * 
 * @author Pablo Pagnone
 */
function local_xray_activityreport_students_activity(YUI, data) {

	$(document).ready(function() {
		
	$('#activityreport_students_activity').dataTable( {
        "bProcessing": true,
        "bServerSide": true,
	    "paging": true,
	    "searching": false, // Search no implemented in webservice xray.
	    "retrieve": true,
	    /*
	    "oLanguage": {
	      "sProcessing": "Fetching Data, Please wait..."
	    },*/
	    //"sAjaxDataProp": "", // With this, you can change format of json.
	    "sAjaxSource": 'view.php?controller="activityreport"&action="jsonstudentsactivity"&xraycourseid='+data.courseid,
	    // Sortable not implemented in webservice xray.
	    "aoColumns": [
	                  {"mData": "action","bSearchable":false, "bSortable": false},
	                  {"mData": "lastname","bSearchable":false, "bSortable": false},
	                  {"mData": "firstname","bSearchable":false, "bSortable": false},
	                  {"mData": "lastactivity","bSearchable":false, "bSortable": false},
	                  {"mData": "discussionposts","bSearchable":false, "bSortable": false},
	                  {"mData": "postslastweek","bSearchable":false, "bSortable": false},
	                  {"mData": "timespentincourse","bSearchable":false, "bSortable": false},	
	                  {"mData": "regularity","bSearchable":false, "bSortable": false},
	                 ]
	});
	});   	
}
