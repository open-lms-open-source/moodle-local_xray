/**
 * Expand Recommended Actions.
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
                icon.addClass('countrecommendedactions_icon_collapse').removeClass('countrecommendedactions_icon_expand')
                    .attr('aria-pressed', 'true');
            } else {
                icon.addClass('countrecommendedactions_icon_expand').removeClass('countrecommendedactions_icon_collapse')
                    .attr('aria-pressed', 'false');
            }
        });
    });
}

/**
 * Expand X-Ray menu.
 */
function local_xray_headline_show() {
    $(document).ready(function () {
        icon = $("#xray-div-headline-icon");
        // Get element to display.
        content = $("#xray-div-headline-show");
        // Open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
        content.slideToggle(500, function () {
            // Execute this after slideToggle is done.
            var iconClass = icon.attr("class");
            if (iconClass == 'headline_icon_expand') {
                icon.addClass('headline_icon_collapse').removeClass('headline_icon_expand')
                    .attr('aria-pressed', 'true');
            } else {
                icon.addClass('headline_icon_expand').removeClass('headline_icon_collapse')
                    .attr('aria-pressed', 'false');
            }
        });
    });
}