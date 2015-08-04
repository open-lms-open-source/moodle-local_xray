/**
 * jQuery scripting for students activity
 * 
 * @author Pablo Pagnone
 */
function local_xray_risk_risk_measures(YUI, data) {

	$(document).ready(function() {
		
	$('#risk_risk_measures').dataTable( {
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
	    "sAjaxSource": 'view.php?controller="risk"&action="jsonriskmeasures"&courseid='+data.courseid,
	    // Sortable not implemented in webservice xray.
	    "aoColumns": [
	                  {"mData": "lastname","bSearchable":false, "bSortable": false},
	                  {"mData": "firstname","bSearchable":false, "bSortable": false},
	                  {"mData": "timespentincourse","bSearchable":false, "bSortable": false},
	                  {"mData": "academicrisk","bSearchable":false, "bSortable": false},
	                  {"mData": "socialrisk","bSearchable":false, "bSortable": false},
	                  {"mData": "totalrisk","bSearchable":false, "bSortable": false}
	                 ]
	});
	});   	
}
