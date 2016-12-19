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
    
    // Get values from backend
    self.lang_strs = json_data.lang_strs;
    self.www_root = json_data.www_root;
    self.watch_fields = json_data.watch_fields;
    self.api_msg_keys = json_data.api_msg_keys;
    
    // Initialize
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
                reasonsMsg = '<li>' + reasons[i] + '</li>';
            }
            reasonsMsg += '</ul>';
        }

        var msgcontainer = $('#api_diag .noticetemplate_' + alertclass).children().first().clone();

        $(msgcontainer).addClass(extraclasses);
        $(msgcontainer).html('<span class="api-connection-msg">'+
                '<strong>' + test_api_title + '</strong>\n' + 
                msg + '</span>' + reasonsMsg);

        // Wipe out existing connection status msg container.
        $('#api_diag #'+ msg_diag_key + '-status').empty();

        // Put in new msg container.
        $('#api_diag #'+ msg_diag_key + '-status').append($(msgcontainer));

    };

    /**
     * Test api.
     */
    self.testApi = function() {       
        $('.api_diag_btn').attr('disabled','disabled');

        for (var key in self.api_msg_keys) {
            self.api_msg(self.api_msg_keys[key], 'verifyingapi', 'message', '');
        }

        $.ajax({
            url: self.www_root + '/local/xray/testapi.php',
            success: function (data, status, xhr) {
                
                for (var key in self.api_msg_keys) {
                    var msg_key = self.api_msg_keys[key], msg_data = data[msg_key];
                    if (msg_data && msg_data.success) {
                        self.api_msg(msg_key, 'connectionverified', 'success');
                    } else {
                        self.api_msg(msg_key, 'connectionfailed', 'problem', null, msg_data ? msg_data.reasons : null);
                    }
                }

                $('.api_diag_btn').removeAttr('disabled');
                self.applyServerInfoToggle();
            },
            error: function (xhr, status, err) {
                
                self.api_msg('connectionfailed', 'problem');

                $('.api_diag_btn').removeAttr('disabled');
            }
        });
    };

    /**
     * Apply listener for api test button.
     *
     * @author David Castro
     */
    self.applyClickApiTest = function() {
        $('.api_diag_btn').click(function(e){
            e.preventDefault();
            
            self.dialog.dialog('open');
            
            self.testApi();
        });
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
        }
    };
    
    /**
     * Apply listener for toggling of server info
     *
     * @author David Castro
     */
    self.applyServerInfoToggle = function() {
        $('.xray_service_info_btn').click(function(e){
            e.preventDefault();
            $('#'+ this.id + '_txt').toggle();
        });
        
        $('.xray_service_info_btn').css('color', '#FFF');
    };
    
    $(document).ready(function () {
        self.init();
    });
}
