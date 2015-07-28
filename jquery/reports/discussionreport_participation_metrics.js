/**
 * jQuery scripting for students activity
 * 
 * @author Pablo Pagnone
 */
$(document).ready(function() {
	
	$('#discussionreport_participation_metrics').dataTable( {
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
	    "sAjaxSource": 'view.php?controller="discussionreport"&action="jsonparticipationdiscussion"',
	    // Sortable not implemented in webservice xray.
	    "aoColumns": [
	                  {"mData": "lastname","bSearchable":false, "bSortable": false},
	                  {"mData": "firstname","bSearchable":false, "bSortable": false},
	                  {"mData": "posts","bSearchable":false, "bSortable": false},
	                  {"mData": "contribution","bSearchable":false, "bSortable": false},
	                  {"mData": "ctc","bSearchable":false, "bSortable": false},
	                  {"mData": "regularityofcontributions","bSearchable":false, "bSortable": false},	
	                  {"mData": "regularityofctc","bSearchable":false, "bSortable": false},
	                 ]
	} );
	
});