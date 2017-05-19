/**
 * Javascript for validating Xray API, AWS and Compression
 *
 * @author David Castro
 * @param YUI
 * @param data
 */
function validate_api_aws_compress(YUI, data) {
    var self = this;

    var json_data = JSON.parse(data);

    // Get values from backend.
    self.lang_strs = json_data.lang_strs;
    self.www_root = json_data.www_root;
    self.watch_fields = json_data.watch_fields;
    self.api_msg_keys = json_data.api_msg_keys;

    // Special case for breaking the error list.
    self.strbreak = 'break';

    // Initialize.
    self.init = function() {
        for(var key in self.watch_fields) {
            self.watch_fields[key] = '#id_s_local_xray_' + self.watch_fields[key];
        }

        self.applySettingChangeCheck();
        self.applyClickApiTest();

        self.dialog = $('#api_diag').dialog({
            modal: true,
            autoOpen: false,
            width: '90%',
            draggable: false
        });
    };

    /**
     * Render new api status message.
     *
     * @param string msg_diag_key
     * @param string stringkey
     * @param string alertclass
     * @param string extraclasses
     * @param string reasons
     */
    self.api_msg = function (msg_diag_key, stringkey, alertclass, extraclasses, reasons) {
        var msg = self.lang_strs[stringkey];
        var test_api_title = self.lang_strs['test_api_' + msg_diag_key];
        var reasonsMsg = '';

        if(reasons) {
            reasonsMsg += '<ul>';
            for(var i = 0; i < reasons.length; i++) {
                if (self.strbreak !== reasons[i]) {
                    reasonsMsg += '<li>' + reasons[i] + '</li>';
                } else {
                    reasonsMsg += '</ul><br><ul>';
                }
            }
            reasonsMsg += '</ul>';
        }

        var msgcontainer = $('#api_diag .noticetemplate_' + alertclass).children().first().clone();

        $(msgcontainer).addClass(extraclasses);
        $(msgcontainer).html('<span class="api-connection-msg">' + '<strong>' + test_api_title + '</strong>\n' + msg + '</span>' + reasonsMsg);

        // Wipe out existing connection status msg container.
        $('#api_diag #' + msg_diag_key + '-status').empty();

        // Put in new msg container.
        $('#api_diag #' + msg_diag_key + '-status').append($(msgcontainer));

    };

    /**
     * Test api.
     * @param string check_key
     * @param function callback
     */
    self.testApi = function(check_key, callback) {
        $.ajax({
            url: self.www_root + '/local/xray/view.php?controller=validatesettings&action=check&check=' + check_key,
            dataType: 'json',
            success: function (data, status, xhr) {
                var generalFail = false;
                if(data.error || data.debuginfo || data.errorcode || data.stacktrace) {
                    generalFail = true;

                    var mappedData = $.map(data, function(value, index) {
                        return [self.strong(index) + '&nbsp;' + value];
                    });
                    var htmlData = self.htmlList(mappedData);

                    data.reasons = [self.serverTechMessage(check_key, htmlData)];
                }

                if (data && data.success) {
                    self.api_msg(check_key, 'connectionverified', 'success');
                } else {
                    var str_key = generalFail ? 'connectionstatusunknown' : 'connectionfailed';
                    var alert_class = generalFail ? 'message' : 'problem';
                    self.api_msg(check_key, str_key, alert_class, null, data ? data.reasons : null);
                }

                self.applyServerInfoToggle(check_key);
                callback();
            },
            error: function (xhr, status, err) {
                self.api_msg(check_key, 'connectionstatusunknown', 'message', null, [
                    'Status: ' + status,
                    'Error: ' + err,
                    self.strbreak,
                    self.serverTechMessage(check_key, xhr.responseText)
                ]);

                self.applyServerInfoToggle(check_key);
                callback();
            }
        });
    };

    /**
     * Apply listener for api test button.
     * @author David Castro
     */
    self.applyClickApiTest = function() {
        var enableValidateButton = function(){
            $('.api_diag_btn').removeAttr('disabled');
        };

        $('.api_diag_btn').click(function(e){
            e.preventDefault();

            self.dialog.dialog('open');

            $('.api_diag_btn').attr('disabled','disabled');

            for (var i = 0; i < self.api_msg_keys.length; i++) {
                self.api_msg(self.api_msg_keys[i], 'verifyingapi', 'message', '');
            }
            self.executeApiTest(0, enableValidateButton);
        });
    };

    /**
     * Recursively execute api tests from idx onwards.
     * @param {int} idx
     * @param {function} callback
     */
    self.executeApiTest = function(idx, callback) {
        if(idx < self.api_msg_keys.length) {
            self.testApi(self.api_msg_keys[idx], function(){
                self.executeApiTest(idx + 1, callback);
            });
        } else {
            callback();
        }
    };

    /**
     * Apply listener for when settings changed.
     *
     * @author David Castro
     */
    self.applySettingChangeCheck = function() {
        for (var s in self.watch_fields) {
            $(self.watch_fields[s]).change(function(e) {
                $('.api_diag_btn').attr('disabled','disabled');
            });

            $(self.watch_fields[s]).keypress(function(e) {
                $('.api_diag_btn').attr('disabled','disabled');
            });
        }
    };

    /**
     * Apply listener for toggling of server info
     *
     * @author David Castro
     */
    self.applyServerInfoToggle = function(check_key) {
        $('#' + check_key).click(function(e){
            e.preventDefault();
            $('#' + check_key + '_txt').toggle();
        });

        $('#' + check_key).css('color', '#FFF');
    };

    /**
     * Generates an html list from an array
     *
     * @param {Array} items
     * @returns {Strin}g
     */
    self.htmlList = function(items) {
        var itemsTxt = '';
        if(items) {
            itemsTxt += '<ul>';
            for(var i = 0; i < items.length; i++) {
                itemsTxt += '<li>' + items[i] + '</li>'
            }
            itemsTxt += '</ul>';
        }
        return itemsTxt;
    };

    /**
     *
     * @param {String} str
     * @returns {String}
     */
    self.strong = function(str) {
        return '<strong>' + str + '</strong>';
    };

    /**
     * Generates a html div and button to show a tech message from the server
     * @param {String} key
     * @param {String} message
     * @param {Array} items
     * @returns {String}
     */
    self.serverTechMessage = function(key, message) {
        return '<a id="' + key + '" class="xray_service_info_btn" href>' + self.lang_strs['validate_service_response'] + '</a><br /><br />' + '<div id="' + key + '_txt"class="xray_service_info">' + message + '</div>'
    };

    $(document).ready(function () {
        self.init();
    });
}
