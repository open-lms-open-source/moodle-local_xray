/*  ContentFlowAddOn_stack, version 1.0 
 *  (c) 2008 - 2010 Sebastian Kutsch
 *  <http://www.jacksasylum.eu/ContentFlow/>
 *
 *  This file is distributed under the terms of the MIT license.
 *  (see http://www.jacksasylum.eu/ContentFlow/LICENSE)
 */

new ContentFlowAddOn ('stack', {

    conf: {
        showArrows: true,
        scrollSpeed: 0.1 
    },

    init: function() {
    },
    
    onloadInit: function (flow) {
    },

    afterContentFlowInit: function (flow) {
        var conf = flow.getAddOnConf('stack');
        if (conf.showArrows) {
            var imgPath = ContentFlowGlobal.BaseDir+"img";
            var al = new Image();
            al.src = imgPath+"/arrow_forth.png";
            al.style.height = "10%";
            al.style.position = "absolute";
            al.style.left = "10%";
            al.style.top = "45%";
            al.style.opacity = 0.2;
            flow.Container.appendChild(al);
            flow.t1 = null;
            al.onmouseover = function () { 
                flow.t1 = window.setInterval(function () {flow.moveTo(flow._targetPosition-conf.scrollSpeed)}, 100);
                al.style.opacity = 1;
            }
            al.onclick = function () {
                flow.moveToPosition(--flow._targetPosition)
            }
            al.onmouseout = function () { 
                window.clearInterval(flow.t1);
                flow.moveToPosition(Math.floor(flow._targetPosition));
                al.style.opacity = 0.2;
            }

            var ar = new Image();
            ar.src = imgPath+"/arrow_back.png";
            ar.style.height = "10%";
            ar.style.position = "absolute";
            ar.style.right = "10%";
            ar.style.top = "45%";
            ar.style.opacity = 0.2;
            flow.Container.appendChild(ar);
            flow.t2 = null;
            ar.onmouseover = function () { 
                flow.t2 = window.setInterval(function () {flow.moveTo(flow._targetPosition+conf.scrollSpeed)}, 100);
                ar.style.opacity = 1; 
            }
            ar.onclick = function () {
                flow.moveToPosition(++flow._targetPosition)
            }
            ar.onmouseout = function () { 
                window.clearInterval(flow.t2);
                flow.moveToPosition(Math.ceil(flow._targetPosition));
                ar.style.opacity = 0.2;
            }
        }
    },
	
	ContentFlowConf: {
        scaleFactorLandscape: 1.0,      // scale factor of landscape images ('max' := height= maxItemHeight)
        relativeItemPosition: "below center", // align top/above, bottom/below, left, right, center of position coordinate
        visibleItems: 20,               // how man item are visible on each side (-1 := auto)
        endOpacity: 0,                  // opacity of last visible item on both sides
        
        calcSize: function (item) {
            var rP = item.relativePosition;
            var rPN = item.relativePositionNormed;
			var sfh = this.conf.scaleFactorHt?this.conf.scaleFactorHt:1;
			var sfw = this.conf.scaleFactorWd?this.conf.scaleFactorWd:1;
            var h = (rP <= -0.9) ? 0 : (rP < 0 ?  1 - rP : 1 - rPN )*sfh;
            var w = h*sfw;
            return {width: w, height: h};
        },

        calcCoordinates: function (item) {
            var rP = item.relativePosition;
            var rPN = item.relativePositionNormed;
            var x = 0;
            var y = -0.25- (rP >= 0 ? rPN/2 : -rP*0.75);
            return {x: x, y: y};
        },
        
        calcRelativeItemPosition: function (item) {
            var x = 0;
            var y = 1;
            return {x: x, y: y};
        },

        calcZIndex: function (item) {
            return -item.relativePositionNormed;
        },

        calcOpacity: function (item) {
            var rP = item.relativePosition;
            var rPN = item.relativePositionNormed;
            return rP <= -1 ? 0 : (rP < 0 ? 1 + rP : 1 - rPN*0.75);
        }
	
    }

});
