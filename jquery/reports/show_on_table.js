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

        // Error to load data in datatables. Show message error and hide table. xray-js-table- .
        $("#xray-js-table-" + data.id).on('error.dt', function (e, settings, techNote, message) {
            $("#xray-js-table-" + data.id + "_wrapper").html(data.errorMessage);
        });

        var table = $("#xray-js-table-" + data.id).dataTable({
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
                // A "sProcessing": data.sProcessingMessage, Disable.
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
            // C "sAjaxDataProp": "", // With this, you can change format of json.
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
                // Not show paginate when pager is 1.
                if (table.fnGetData().length == oSettings._iRecordsTotal) {
                    $("#xray-js-table-" + data.id + "_paginate").hide();
                } else {
                    $("#xray-js-table-" + data.id + "_paginate").show();
                }

                /* Add tabindex to tbody, scope to th elements and scope to first element in rows(tbody). */
                $("#xray-js-table-" + data.id + " tbody").attr("tabindex",0);
                $("#xray-js-table-" + data.id + " tr th").attr("scope", "col");
                $("#xray-js-table-" + data.id + " tbody tr td:first-child").attr("scope", "row");

                // To load table, show table on top.
                var targetOffset = $("#" + data.id).offset().top;
                $('html, body').scrollTop(targetOffset);
            }
        });

        // Close table.
        $("#" + data.id + " .xray-closetable").click(function() {
            var targetOffset = $("#" + data.id + "-toggle").offset().top;
            $('html, body').scrollTop(targetOffset);
        });
    });
}
