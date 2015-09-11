YUI.add('moodle-local_xray-custmenu', function(Y) {

        M.local_xray = M.local_xray || {};

        M.local_xray.custmenu = {
            init: function(config) {
                if (M.cfg.developerdebug) {
                    Y.log("Entered local_xray.custmenu!");
                }
                var menu = Y.one(config.menusearch);
                if (menu) {
                    menu.prepend(Y.Escape.html(config.header));
                } else {
                    if (M.cfg.developerdebug) {
                        Y.log("Unable to locate element " + config.menusearch);
                    }
                }
                var header = Y.one(config.hdrsearch);
                if (header) {
                    header.prepend(Y.Escape.html(config.header));
                } else {
                    if (M.cfg.developerdebug) {
                        Y.log("Unable to locate element " + config.header);
                    }
                }
            } // end init
        }; // end custmenu

    }, '@VERSION@', {requires: ['node', 'console', 'escape']}
);
