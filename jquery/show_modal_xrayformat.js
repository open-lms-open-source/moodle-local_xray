/**
 * Example for show modal with format of x-ray
 * This uses plugin worked by Shani on x-ray site.
 * 
 * TODO:: In future, we will get json from webservice.
 */
$(document).ready(function(){
        itemBrowser.init([{"id":"R5_social_structure.png_3",
				        	"image":"http://www.techjournal.org/wp-content/uploads/2011/06/Moodlerooms-01.jpg",
				        	"url":"xxxxx",
				        	"caption":"Test1"},
        	               {"id":"R5_social_structure.png_4",
				        	"image":"http://fineprintnyc.com/images/blog/history-of-logos/google/google-logo.png",
				        	"url":"xxxxxxxx",
				        	"caption":"Test2"},
	        	           {"id":"R5_social_structure.png_5",
					        "image":"http://img4.wikia.nocookie.net/__cb20150211093302/logopedia/images/5/5d/FIFA_Logo.svg",
					        "url":"xxxxxxxx",
					        "caption":"Test3"}],{"expandable":false},{"circularFlow":false,"maxItemHeight":400,"visibleItems":3,"scaleFactorPortrait":0.8,"scaleFactorLandscape":1});
		$("a.btn").click(function(){
			$("div#itbopenlinkopts").hide();
		});
})