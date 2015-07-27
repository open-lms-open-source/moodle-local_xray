/**
 * jQuery scripting for students activity
 * 
 * @author Pablo Pagnone
 */
$(document).ready(function() {
	
	$('#students_activity').dataTable( {
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
	    "sAjaxSource": 'view.php?controller="activity_report"&action="jsonstudentsactivity"',
	    // Sortable not implemented in webservice xray.
	    "aoColumns": [
	                  {"mData": "lastname","bSearchable":false, "bSortable": false},
	                  {"mData": "firstname","bSearchable":false, "bSortable": false},
	                  {"mData": "lastactivity","bSearchable":false, "bSortable": false},
	                  {"mData": "discussionposts","bSearchable":false, "bSortable": false},
	                  {"mData": "postslastweek","bSearchable":false, "bSortable": false},
	                  {"mData": "timespentincourse","bSearchable":false, "bSortable": false},	
	                  {"mData": "regularity","bSearchable":false, "bSortable": false},
	                 ]
	} );
	
});