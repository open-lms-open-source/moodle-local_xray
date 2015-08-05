/**
 * Generic implementation for create table with Datatables Jquery.
 * More infomation: Check method standard_table on renderer.php and local_xray.utils.php
 * 
 * @author Pablo Pagnone
 * @param YUI
 * @param data
 */
function local_xray_show_on_table(YUI, data) {
	$(document).ready(function() {
		$("#"+data.id).dataTable( {
	        "bProcessing": true,
	        "bServerSide": true,
		    "retrieve": true,
		    "paging": data.paging,
		    "searching": data.search,
		    "lengthMenu": data.lengthMenu,
		    "oLanguage": {
		      "sProcessing": data.sProcessingMessage
		    },
		  //"sAjaxDataProp": "", // With this, you can change format of json.
		    "sAjaxSource": data.jsonurl,
		    "aoColumns": data.columns	
		} );
	});
}