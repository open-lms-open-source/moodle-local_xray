/**
 * jQuery scripting for show image on lightbox.
 * 
 * Generic implementation for create lightbox with fancybox2 Jquery.
 * More infomation: Check method show_on_lightbox on renderer.php
 * 
 * @author Pablo Pagnone
 * @param YUI
 * @param id - Id of element
 * @param data
 * 
 */
function local_xray_show_on_lightbox(YUI, id, data) {
	$(document).ready(function() {	
	    $("#"+id).fancybox({
			prevEffect		: 'none',
			nextEffect		: 'none',
			closeBtn		: true,
			title           : data.legend	
	    });	    
	});
}