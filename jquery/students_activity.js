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
	    "searching": true,
	    "retrieve": true,
	    "oLanguage": {
	      "sProcessing": "Fetching Data, Please wait..."
	    },
	    "sAjaxDataProp": "", // Very important, default format json. BY default this uses aaData.
	    "sAjaxSource": 'view.php?controller="activity_report"&action="jsonstudentsactivity"',
	    "aoColumns": [
	                  {"mData": "lastname"},
	                  {"mData": "firstname"},
	                  {"mData": "lastactivity"},
	                  {"mData": "discussionposts"},
	                  {"mData": "postslastweek"},
	                  {"mData": "timespentincourse"},	
	                  {"mData": "regularity"},
	                 ]
	    	
	} );
	
} );