/**
 * Expand Recommended Actions.
 *
 * @author German Vitale
 * @param YUI
 * @param data
 */
function local_xray_recommendations_show() {
    $(document).ready(function () {
        icon = $("#xray-div-recommendations-icon");
        // Get element to display.
        content = $("#xray-div-recommendations-show");
        // Open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
        content.slideToggle(500, function () {
            // Execute this after slideToggle is done.
            var iconClass = icon.attr("class");
            if (iconClass == 'countrecommendedactions_icon_expand') {
                icon.addClass('countrecommendedactions_icon_collapse').removeClass('countrecommendedactions_icon_expand');
                icon.attr('aria-pressed', 'true');

            } else {
                icon.addClass('countrecommendedactions_icon_expand').removeClass('countrecommendedactions_icon_collapse');
                icon.attr('aria-pressed', 'false');
            }
        });
    });
}
