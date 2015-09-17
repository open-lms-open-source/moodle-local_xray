YUI.add('moodle-local_xray-custmenu', function(Y) {

        M.local_xray = M.local_xray || {};

        M.local_xray.custmenu = {
            init: function(config) {
                if (M.cfg.developerdebug) {
                    Y.log("Entered local_xray.custmenu!");
                }

                // Header data
                if (config.header) {
                    var header = Y.one(config.hdrsearch);
                    if (header) {
                        header.append(config.header);
                    } else {
                        if (M.cfg.developerdebug) {
                            Y.log("Unable to locate element " + config.header);
                        }
                    }
                }

                // Menu
                if (config.items) {
                    var menu = Y.one(config.menusearch);
                    if (menu) {
                        menu.append(config.items);
                    } else {
                        if (M.cfg.developerdebug) {
                            Y.log("Unable to locate element " + config.menusearch);
                        }
                    }
                }
            } // end init
        }; // end custmenu

    }, '@VERSION@', {requires: ['node', 'console', 'escape']}
);
