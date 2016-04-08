/**
 * Generic implementation for create table with Datatables Jquery.
 * More infomation: Check method standard_table on renderer.php and local_xray.utils.php
 *
 * @author Pablo Pagnone
 * @param YUI
 * @param data
 */
function local_xray_show_on_table(YUI, data) {
    $(document).ready(function () {

        // Disable warning native.
        $.fn.dataTable.ext.errMode = 'none';

        // Themes with header overlayed.
        var themewithheaderoverlayed = ['bootstrapbase', 'charter', 'clean', 'more', 'snap'];

        // Error to load data in datatables. Show message error and hide table. xray-js-table-
        $("#xray-js-table-" + data.id).on('error.dt', function (e, settings, techNote, message) {
            $("#xray-js-table-" + data.id + "_wrapper").html("<p class='xray_error_datatables'>" + data.errorMessage + "</p>");
        });

        $("#xray-js-table-" + data.id).dataTable({
            "jQueryUI": true,
            "bProcessing": true,
            "bServerSide": true,
            "retrieve": true,
            "bSort": data.sort,
            "order": [[ data.default_field_sort, data.sort_order ]],
            "paging": data.paging,
            "searching": data.search,
            "dom": data.dom,
            "lengthMenu": data.lengthMenu,
            // We load language from lang of moodle.
            "language": {
                "sProcessing": data.sProcessingMessage,
                "sInfo": data.sInfo,
                "sInfoEmpty": data.sInfoEmpty,
                "sLengthMenu": data.sLengthMenu,
                "sLoadingRecords": data.sLoadingRecords,
                "sProcessing": data.sProcessing,
                "sZeroRecords": data.sZeroRecords,
                "paginate": {
                    "sFirst": data.sFirst,
                    "sLast": data.sLast,
                    "sNext": data.sNext,
                    "sPrevious": data.sPrevious,
                },
                "aria": {
                    "sSortAscending":  data.sSortAscending,
                    "sSortDescending": data.sSortDescending
                }
            },
            //"sAjaxDataProp": "", // With this, you can change format of json.
            "sAjaxSource": data.jsonurl,
            "aoColumns": data.columns,
            "fnServerData": function (sSource, aoData, fnCallback, oSettings) {
                oSettings.jqXHR = $.ajax({
                    "dataType": 'json',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": fnCallback
                })
            },
            "fnDrawCallback": function( oSettings ,aoData) {
                // INT-8289, not show paginate when pager is 1.
                if($("#xray-js-table-" + data.id).DataTable().rows().data().length == oSettings._iRecordsTotal) {
                    $("#xray-js-table-" + data.id +"_paginate").hide();
                } else {
                    $("#xray-js-table-" + data.id +"_paginate").show();
                }

                // To load table, show table on top(40 is for prevent problem with header of moodle).
                var targetOffset = $("#xray-js-table-" + data.id + "_wrapper").offset().top;
                if($.inArray(M.cfg.theme, themewithheaderoverlayed) != -1) {
                    targetOffset = targetOffset - 40;
                }
                $('html, body').scrollTop(targetOffset);
            }
        });
    });
}
