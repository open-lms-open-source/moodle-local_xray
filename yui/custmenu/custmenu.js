YUI.add('moodle-local_xray-custmenu', function(Y) {

        M.local_xray = M.local_xray || {};

        M.local_xray.custmenu = {
            module : 'local_xray',

            init: function(config) {
                if (M.cfg.developerdebug) {
                    Y.log("Entered local_xray.custmenu!", "info", this.module);
                }

                var location = 0;
                // Header data
                if (config.header && config.hdrsearch) {
                    var header = Y.one(config.hdrsearch);
                    if (header) {
                        if (config.hdrappend && Boolean(config.hdrappend)) {
                            location = null;
                        }
                        header.insert(config.header, location);
                    } else {
                        if (M.cfg.developerdebug) {
                            Y.log("Unable to locate element " + config.header, "error", this.module);
                        }
                    }
                }

                // Menu
                if (config.items && config.menusearch) {
                    var menu = Y.one(config.menusearch);
                    if (menu) {
                        location = 0;
                        if (config.menuappend && Boolean(config.menuappend)){
                            location = null;
                        }
                        menu.insert(config.items, location);
                    } else {
                        if (M.cfg.developerdebug) {
                            Y.log("Unable to locate element " + config.menusearch, "error", this.module);
                        }
                    }
                }
            } // end init
        }; // end custmenu

    }, '@VERSION@', {requires: ['node', 'console']}
);
