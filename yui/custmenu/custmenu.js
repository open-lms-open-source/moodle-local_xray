YUI.add('moodle-local_xray-custmenu', function(Y) {

        M.local_xray = M.local_xray || {};

        M.local_xray.custmenu = {
            init: function(config) {
                // TODO: Implement proper placement of custom menu items.
                if (M.cfg.developerdebug) {
                    Y.log(config.items);
                }
                //var menunode = Y.Node.create('ul');
            } // end init
        }; // end custmenu

    }, '@VERSION@', {requires: ['node', 'console']}
);
