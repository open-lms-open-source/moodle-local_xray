/**
 * jQuery scripting for discussion activity by week
 * 
 * @author Pablo Pagnone
 * @author Germán Vitale
 */

function local_xray_discussionreport_discussion_activity_by_week(YUI, data) {
    $(document).ready(function() {
        
        $('#discussionreport_discussion_activity_by_week').dataTable( {
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
            "sAjaxSource": 'view.php?controller="discussionreport"&action="jsonweekdiscussion"&courseid='+data.courseid,
            // Sortable not implemented in webservice xray.
            "aoColumns": [
                          {"mData": "1","bSearchable":false, "bSortable": false},
                          {"mData": "2","bSearchable":false, "bSortable": false},
                          {"mData": "3","bSearchable":false, "bSortable": false},
                          {"mData": "4","bSearchable":false, "bSortable": false},
                          {"mData": "5","bSearchable":false, "bSortable": false},
                          {"mData": "6","bSearchable":false, "bSortable": false},
                          {"mData": "7","bSearchable":false, "bSortable": false},	
                          {"mData": "8","bSearchable":false, "bSortable": false},
                          {"mData": "9","bSearchable":false, "bSortable": false},
                          {"mData": "10","bSearchable":false, "bSortable": false},
                          {"mData": "11","bSearchable":false, "bSortable": false},
                          {"mData": "12","bSearchable":false, "bSortable": false},
                          {"mData": "13","bSearchable":false, "bSortable": false},
                          {"mData": "14","bSearchable":false, "bSortable": false},
                          {"mData": "15","bSearchable":false, "bSortable": false},
                          ]
        });
    });
}
