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
                    if (config.hdrappend && Boolean(config.hdrappend)) {
                        location = null;
                    }
                    this.embed(config.hdrsearch, config.header, location);
                }

                // Menu
                if (config.items && config.menusearch) {
                    location = 0;
                    if (config.menuappend && Boolean(config.menuappend)){
                        location = null;
                    }
                    this.embed(config.menusearch, config.items, location);
                }
            }, // end init

            embed: function (search, content, location) {
                var node = Y.one(search);
                if (node) {
                    node.insert(content, location);
                } else {
                    if (M.cfg.developerdebug) {
                        Y.log("Unable to locate element " + search, "error", this.module);
                    }
                }
            } // end embed

        }; // end custmenu

    }, '@VERSION@', {requires: ['node', 'console']}
);
