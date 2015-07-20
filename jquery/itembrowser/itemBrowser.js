/************************************************************** 
 * xray js-interface for listing sociogram items.
 * Date: 2011-10-24
 * version: 1.0
 * programmer: Shani Mahadeva
 * Description:   
 * Javascript interfaces for xray interactive reports
 * Dependencies:
 * contentflow_src.js and jquery.js
 * History:
 * 	2011-10-24: First version
 * Usage Notes:
 *     To start : itembrowser.init(<json object>[,<confin object>])
 *     Configurable Attributes : listHeight : <value>px, iconHeight : <value>px,
 *                             : callback : <function>
 *                             : links : <'ver'|'hor'|'icons'>
 *                             : numIcons : number of icons in a row
 *                             : wrapper : <jquery selector of the container div, default -'#xrayItemWrapper'>
 *                                         Set the height and width of container otherwise Fancybox will not be able to calculate sizes
 * ****************************************************************/

var itblinkclick = function(e){
	$("div#itbopenlinkopts").css({"left" : e.clientX,"top" :e.clientY}).fadeIn(100);
	var url = $(this).attr("href");
	$("div#itbopenlinkopts a#itbnewtab").unbind('click').click(function(){
		var win = window.open(url, '_blank');
		win.focus();
		$("div#itbopenlinkopts").hide();
	});
	$("div#itbopenlinkopts a#itbthistab").unbind('click').click(function(){
		window.location = url;
		$("div#itbopenlinkopts").hide();
	});
};

var itemBrowser  = {
    
	/**
	 * List of items having image, id and caption.
	 */
	listObj: [],
	/**
	 * Div with id itemBrowser inside main wrapper, containing everything
	 */
	ibrowser : {},
	/**
	 * Div containing main content, #itemBrowserContent
	 */
	content : {},
	/**
	 * Self instance pointer, instead of using 'this' which might poing to some other object
	 */
	o       : {},
	/**
	 * div#flowWrap, containing jQuery object of this item.
	 */
	views: {
		list: {},coverflow: {},icon: {}, gallery: {}
	},
	/**
	 * Actual item browser options that will be used.
	 */
	opts	 : {},
	/**
	 * number of items in listObj
	 */
	len      : 0,
	/**
	 * Number of expand icons, default 2
	 */
	expands  : 0,
	/**
	 * Content flow plugin options default
	 */
	cfopts   : {
			circularFlow: false,
			maxItemHeight: 0,
			useAddOns : 'fancyScrollbar'
	},
	/**
	 * Default item browser options, dimensions in px
	 */
	defaults : {
		listHeight: 150,
		iconHeight: 200,
		nameListH : 40,
		background: "#000",
		callback : "itblinkclick",
		wrapper  : '#xrayItemBrowser',
		links	 : 'hor',
		numIcons : 3,
		imagelist   : true,
		icons       : true,
		cover       : true,
		gallery    : true,
		customstyle : false,
		boxType     : 'fancybox',//needed to call close method on expand event
		expandable  : true,
		defaultView : 'coverflow'
	},
    
    /**
     *
     * @param {object} obj list of items for item browser having 'id','caption'
     * and 'image' properties for each.
     * @param {object} options For item browser
     * @param {object} cfopts Options for content flow plugin.
     */
    init: function (obj,options,cfopts) {
                o = this;
                o.listObj = obj;
                o.len = o.listObj.length;
		for(var i in o.listObj){
			if(typeof o.listObj[i].url == 'undefined')
				o.listObj[i]["url"] = "#";
		}
		var contentFlowOpts= {};
		o.cfopts.onclickActiveItem = function(){};
		$.extend(contentFlowOpts,o.cfopts, cfopts)
		$.extend(o.opts,o.defaults,options);
		if(o.opts.callback!="" && typeof o.opts.callback == "string") {
			o.opts.callback = eval(o.opts.callback);
		}
		o.createHtml();
		o.expands=0;
		//Based on number of items, wait for a while to images to load, then start content flow
		//if content flow is being used, otherwise it's going to be blank.
		var t = o.listObj.length*2;
		o.loadImages(function(){
			o.views.coverflow.show();//if content flow items are hidden, contentFlow won't start
			new ContentFlow('FlowWrap',contentFlowOpts).init();
			//after another timeout, check which item is to be displayed by default
			window.setTimeout(o.startShow, t);
		});
    },
	
	loadImages: function(callback){
		var loaded = 0,failed=0;
		var progress = $("<div id='loadProgress'></div>");
		$(itemBrowser.defaults.wrapper).append(progress);
		progress.progressbar({value:false});
		var onComplete = function(imgloaded){
			if(imgloaded) loaded++;
			else failed++;
			progress.progressbar("option",{value:(loaded+failed)/itemBrowser.len*100});
			if((loaded+failed)===itemBrowser.len){
				progress.hide();
				if(failed===itemBrowser.len){
					var msg = $("<div id='loadProgress'><p>Oops!! All the items failed to load.</p></div>");
					$(o.defaults.wrapper).append(msg);
				}else
					callback();
			}
		};
		itemBrowser.listObj.forEach(function(item){
			$("<img/>").load(function(){
				onComplete(true);
			}).error(function(){
				onComplete(false);
			}).attr({src : item.image});
		});
	},
    
    /**
     * Show the default view to be shown
     */
    startShow : function (){
            $('[id$="Wrap"]',o.content).hide();
            if(o.opts.defaultView=='coverflow'){
                if(o.opts.cover)
                    o.views.coverflow.show();
                else
                    $('[id$="Wrap"]',o.content).eq(0).show();
            }
            else{
                if(o.opts.cover)
                    o.asCoverFlow();
                o.views[o.opts.defaultView].show();
            }        
    },
    
    //Show as<viewtype> functions
	show: function(){
		$('[id$="Wrap"]',o.content).hide();
		o.views[$(this).attr('id')].fadeIn();
	},
    
    /**
     * Create view functions: Create the html, apply desired heights and orientations
     * and append the view  to main content div.
     */
    createImgList: function (){
		//list View content
		var i=0;
		o.views.list = $('<ul></ul>').attr({"id" : "listWrap"});
		var list;
		for(i=0;i<o.len;i++){
			if(o.listObj[i].id==null) o.listObj[i].id = 0;
			if($.trim(o.listObj[i].caption)=='') o.listObj[i].caption = '';
			list = $("<li class='listitem'><a href='"+o.listObj[i].url+"' data-id='"+ o.listObj[i].id +"'><img src='"+o.listObj[i].image+
			"' /><a class='name'  href='"+o.listObj[i].url+"' data-id='"+o.listObj[i].id+"'>"+o.listObj[i].caption+
			"</a></a></li>");
			list.css({"height" :  o.opts.listHeight});
			$('.name',list).css({"margin-top" : (o.opts.listHeight/2-10)});
			o.views.list.append(list);
		}
		o.content.append(o.views.list.hide());
    },
   
    
    createIcons: function(){
		//iconview content
		var i=0;
		o.views.icon = $('<div></div>').attr({"id" : "iconWrap"});
		var icon;
		for(i=0;i<o.len;i++){
			if(o.listObj[i].id==null) o.listObj[i].id = 0;
			if($.trim(o.listObj[i].caption)=='') o.listObj[i].caption = '';
			icon = $("<div class='icon' height="+o.opts.iconHeight+"><a href='"+o.listObj[i].url+"' data-id='"+ o.listObj[i].id +"'><img src='"+o.listObj[i].image+
			"'/><a class='name' href='"+o.listObj[i].url+"' data='"+o.listObj[i].id+"'>"+o.listObj[i].caption+
			"</a></a></div>");
					if(o.listObj[i].highlight) $('img',icon).addClass('highlight');
			icon.css({"height": o.opts.iconHeight, "width" : (100/o.opts.numIcons)+"%"});
			o.views.icon.append(icon);
		}
		o.content.append(o.views.icon.hide());
    },
    
    createCover: function(){
		//FlowView content
		var i=0;
		o.views.coverflow = $("<div id='FlowWrap'><div class='loadIndicator'><div class='indicator'></div></div><div class='flow'></div><div class='globalCaption'></div><div class='scrollbar'><div class='slider'></div></div></div>");
		var img;
		for(i=0;i<o.len;i++){
			if(o.listObj[i].id==null) o.listObj[i].id = 0;
			if($.trim(o.listObj[i].caption)=='') o.listObj[i].caption = '';
			img = "<a class='item'  href='"+o.listObj[i].url+"' data-id='"+o.listObj[i].id+"'><img class='content' src='"+o.listObj[i].image+"' /><div class='caption name'>"+o.listObj[i].caption+"</div></a>";
			$('.flow',o.views.coverflow).append(img);
			if(o.listObj[i].highlight) $('img',img).addClass('highlight');
		}
		o.content.append(o.views.coverflow.hide());
    },
	
	createGallery: function(){
		//list View content
		var i=0;
		o.views.gallery = $('<div id="galleryWrap"></div>');
		var list;
		for(i=0;i<o.len;i++){
			if(o.listObj[i].id===null) o.listObj[i].id = 0;
			if($.trim(o.listObj[i].caption)=='') o.listObj[i].caption = '';
			list = $("<div class='item'><a href='"+o.listObj[i].url+"' data-id='"+ o.listObj[i].id +"'><img src='"+o.listObj[i].image+
			"' /></a><div class='title'><a class='name'  href='"+o.listObj[i].url+"' data-id='"+o.listObj[i].id+"'>"+o.listObj[i].caption+
			"</a></div>");
			o.views.gallery.append(list);
		}
		o.content.append(o.views.gallery.hide());
		o.views.gallery.find("div.item").on("mouseover",function(){
			var title = $(this).find("div.title");
			title.animate({"height":"30px"},100).css({"padding-top":"1px"});
		}).on("mouseleave",function(){
			var title = $(this).find("div.title");
			title.animate({"height":"0px"},100).css({"padding-top":"0px"});
		});
	},

    /**
     * Expand icons for discussonViews.
     */
    expand :    function(by){
                var icon = $('<a class="fg-button ui-state-default fg-button-icon-solo ui-corner-all expand" id="expander'+o.expands+'" href="#" title="View by '+by+'"><span class="ui-icon ui-icon-arrow-4-diag"></span></a>');
                icon.click(function(){
                    if(o.opts.boxType=='fancybox')
                        $('#fancybox-close').click();
                    else fancy.dialogBox.dialog('destroy');
                    xray.loadReport('discussionReportBy'+by);                
                })
                icon.css({'right': (o.expands*20+2)});
                o.ibrowser.append(icon);
                o.expands++;
    },
    
    /**
     * Create complete html with icons and events.
     */
    createHtml: function () {
		o.ibrowser = $('<div></div>').attr({"id" : "itemBrowser"}).css("background-color",itemBrowser.opts.background);
		o.content = $('<div></div>').attr({"id" : "itemBrowserContent"});
		if(o.opts.links){
			//links objects that change view
			var viewlist = $('<ul id="itemBrowserNav" class="btn-group'+(o.opts.links=='ver'?'-vertical':'')+'"></ul>');
			var views = [];
			if(o.opts.icons) 
				views.push({id: "icon", "name" : "Icon", "create": o.createIcons, "iconclass" : "glyphicon glyphicon-th" });
			if(o.opts.imagelist) 
				views.push({id: "list","name" : "List", "create": o.createImgList, "iconclass" : "glyphicon glyphicon-th-list" });
			if(o.opts.icons) 
				views.push({id: "coverflow","name" : "Cover", "create": o.createCover, "iconclass" : "glyphicon glyphicon-film" });
			if(o.opts.gallery) 
				views.push({id: "gallery","name" : "Gallery", "create": o.createGallery, "iconclass" : "glyphicon glyphicon-th-large" });
			views.forEach(function(v){
				v.create();
				var icon = $('<a href="#" class="btn btn-default btn-xs" id="'+v.id+'"></a>')
						.append($('<span class="'+v.iconclass+'" title="'+v.name+'"></span>'));
				if(o.opts.defaultView === v.id) icon.addClass("active");
				icon.click(o.show);
				viewlist.append(icon);
			});

			$('a',viewlist).click(function(e){
				$('a',viewlist).removeClass('active');
				$(this).toggleClass('active');
				e.preventDefault();
			}).attr({"href" : '#'});
		}
		if(o.opts.expandable){
			o.expand('User');
			o.expand('Forum');
		}
                
		//adding all the above to container and container to body
		o.ibrowser.append(o.content,viewlist);
		$(o.opts.wrapper).append(o.ibrowser);
		
		
		//callback setting
		$('a[data-id]',o.content).click(function(e){
			if(typeof o.opts.callback === "function"){
				e.preventDefault();
				o.opts.callback.call(this,e);
			}
			else{
				var url = $(this).attr('href')|"";
				if(url==""||url=="#"){
					e.preventDefault();
				}
				else{
				  if(o.opts.boxType=='fancybox')
						$('#fancybox-close').click();
				  else fancy.dialogBox.dialog('destroy');
				}
			}
		});
    }
};