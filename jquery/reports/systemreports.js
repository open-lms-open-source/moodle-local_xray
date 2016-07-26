/**
 * Resize iframe dynamically using iframe-resize library.
 *
 * @author German Vitale
 * @param YUI
 * @param data
 */
function localXrayLoadUrl(YUI, data) {

    $(document).ready(function () {
        $('#local-xray-systemreports').iFrameResize( [{log:false, autoResize:true}] );
    })
}