/*

CollapsibleLists.js

An object allowing lists to dynamically expand and collapse

Created by Stephen Morley - http://code.stephenmorley.org/ - and released under
the terms of the CC0 1.0 Universal legal code:

http://creativecommons.org/publicdomain/zero/1.0/legalcode

*/

// Create the CollapsibleLists object.
var CollapsibleLists = new function(){

       /* Makes all lists with the class 'xray-collapsible-list' collapsible. The
        * parameter is:
        *
        * doNotRecurse - true if sub-lists should not be made collapsible
        */
        this.apply = function(doNotRecurse){

            // Loop over the unordered lists.
            var uls = document.getElementsByTagName('ul');
            for (var index = 0; index < uls.length; index ++){

                // Check whether this list should be made collapsible.
                if (uls[index].className.match(/(^| )xray-collapsible-list( |$)/)){

                    // Make this list collapsible.
                    this.applyTo(uls[index], true);

                    // Check whether sub-lists should also be made collapsible.
                    if (!doNotRecurse){

                        // Add the xray-collapsible-list class to the sub-lists.
                        var subUls = uls[index].getElementsByTagName('ul');
                        for (var subIndex = 0; subIndex < subUls.length; subIndex ++){
                            subUls[subIndex].className += ' xray-collapsible-list';
                        }

                    }

                }

            }

        };

      /* Makes the specified list collapsible. The parameters are:
       *
       * node         - the list element
       * doNotRecurse - true if sub-lists should not be made collapsible
       */
      this.applyTo = function(node, doNotRecurse){

        // Loop over the list items within this node.
        var lis = node.getElementsByTagName('li');
        for (var index = 0; index < lis.length; index ++){

            // Check whether this list item should be collapsible.
            if (!doNotRecurse || node == lis[index].parentNode){

                // Prevent text from being selected unintentionally.
                if (lis[index].addEventListener){
                    lis[index].addEventListener(
                      'mousedown', function (e){ e.preventDefault(); }, false);
                }else{
                    lis[index].attachEvent(
                      'onselectstart', function(){ event.returnValue = false; });
                }

                // Add the click listener.
                if (lis[index].addEventListener){
                    lis[index].addEventListener(
                      'click', createClickListener(lis[index]), false);
                }else{
                    lis[index].attachEvent(
                      'onclick', createClickListener(lis[index]));
                }

                // Close the unordered lists within this list item.
                toggle(lis[index]);

            }

        }

      };

      /* Returns a function that toggles the display status of any unordered
       * list elements within the specified node. The parameter is:
       *
       * node - the node containing the unordered list elements
       */
    function createClickListener(node){

        // Return the function.
        return function(e){

            // Ensure the event object is defined.
            if (!e) { e = window.event; }

            // Find the list item containing the target of the event.
            var li = (e.target ? e.target : e.srcElement);
            while (li.tagName !== 'LI') { li = li.parentNode; }

            // Toggle the state of the node if it was the target of the event.
            if (li === node) { toggle(node); }

        };

    }

      /* Opens or closes the unordered list elements directly within the
       * specified node. The parameter is:
       *
       * node - the node containing the unordered list elements
       */
    function toggle(node){

        // Determine whether to open or close the unordered lists.
        var open = node.className.match(/(^| )xray-collapsible-list-closed( |$)/);

        // Loop over the unordered list elements with the node.
        var uls = node.getElementsByTagName('ul');
        for (var index = 0; index < uls.length; index ++){

            // Find the parent list item of this unordered list.
            var li = uls[index];
            while (li.tagName !== 'LI') { li = li.parentNode; }

            // Style the unordered list if it is directly within this node.
            if (li === node) { uls[index].style.display = (open ? 'block' : 'none'); }

        }

        // Remove the current class from the node.
        node.className = node.className.replace(
            /(^| )xray-collapsible-list(-open|-closed)( |$)/, '');

        // If the node contains unordered lists, set its class.
        if (uls.length > 0){
            node.className += ' xray-collapsible-list' + (open ? '-open' : '-closed');
        }

    }

}();
