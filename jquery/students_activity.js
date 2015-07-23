/**
 * jQuery scripting for students activity
 * 
 * @author Pablo Pagnone
 */
$(document).ready(function() {
	
	$('#students_activity').dataTable( {
        "bProcessing": true,
        "bServerSide": true,
	    "paging": false,
	    "searching": false,
	    "retrieve": true,
	    "oLanguage": {
	      "sProcessing": "Fetching Data, Please wait..."
	    },
	    "sAjaxSource": 'view.php?controller="activity_report"&action="jsonstudentsactivity"'
	} );
	
} );