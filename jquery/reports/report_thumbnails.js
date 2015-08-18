/**
 * jQuery scripting for show thumbnails of reports.
 * @author Pablo Pagnone
 * 
 */
$(document).ready(function() {
	$('.thumb').jqthumb({
	    classname  : 'jqthumb',          // class name. DEFUALT IS jqthumb
	    width      : '100%',             // new image width after cropping. DEFAULT IS 100px.
	    height     : '100%',             // new image height after cropping. DEFAULT IS 100px.
	    position   : {
	        x : '50%',                   // x position of the image. DEFAULT is 50%. 50% also means centerize the image.
	        y : '50%'                    // y position of the image. DEFAULT is 50%. 50% also means centerize the image.
	    },
	    source     : 'src',              // to specify the image source attribute. DEFAULT IS src.
	    show       : false,              // TRUE = show immediately after processing. FALSE = do not show it. DEFAULT IS TRUE.
	    responsive : 20,                 // used by older browsers only. 0 to disable. DEFAULT IS 20
	    zoom       : 1,                  // zoom the output, 2 would double of the actual image size. DEFAULT IS 1
	    method     : 'auto',             // 3 methods available: "auto", "modern" and "native". DEFAULT IS auto
	    reinit     : true,               // TRUE = to re-init when images is re-initialized for the second time. FALSE = nothing would happen.
	    before     : function(oriImage){ // callback before each image starts processing.
	        //alert("I'm about to start processing now...");
	    },
	    after      : function(imgObj){   // callback when each image is cropped.
	        //console.log(imgObj);
	    },
	    done       : function(imgArray){ // callback when all images are cropped.
	        for(i in imgArray){
	            $(imgArray[i]).fadeIn();
	        }
	    }
	});
});