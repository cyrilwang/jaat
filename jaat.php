<?php
include_once('config.php');
include_once('function.php');
?>
<html>
    <head>
        <meta charset=utf-8" />
        <title>JAAT</title>
        <style type="text/css" title="currentStyle">
            @import "datatables/css/demo_page.css";
            @import "datatables/css/demo_table.css";
            @import "datatables/css/TableTools.css";
            @import "css/jquery.tzCheckbox.css";
            @import "css/jaat.css";
            </style>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
        <script src="datatables/js/jquery.dataTables.js"></script>
        <script src="datatables/js/ZeroClipboard.js"></script>
        <script src="datatables/js/TableTools.js"></script>
        <script src="datatables/js/dataTables.ipAddress.js"></script>
        <script src="js/jaat.js"></script>
        <script type="text/javascript" charset="utf-8">
            $.fn.dataTableExt.afnFiltering.push(
                function(oSettings, aData, iDataIndex) {
                    var iColumn = 1;
                    if (aData[iColumn]=='-') {
                        return false;
                    }
                    return true;
                }
            );
            
            jQuery.fn.dataTableExt.oApi.fnProcessingIndicator = function ( oSettings, onoff ) {
                if ( typeof( onoff ) == 'undefined' ) {
                    onoff = true;
                }
                this.oApi._fnProcessingDisplay( oSettings, onoff );
            };

            var url_base = '<?php echo get_fetcher_url(); ?>';
            var server_index = -1;
            var last_updated;
            var update_time_info_interval_id;
            var servers = {<?php for ($i=0; $i<count($server); $i++) { echo "'{$i}': '{$server[$i][0]}'"; if ($i!=count($server)-1) { echo ", ";} } ?>};
            var oTable;

            $(document).ready(function() {
                $('input[type=checkbox]').tzCheckbox({labels:['Enable','Disable']});
                $.each(servers, function (index, value) {
                    $('#server_select').append($('<option>', { 
                        value: value,
                        text : value
                    }));
                });
                $('#server_select option').eq(server_index+1).attr('selected', 'selected');
                $('#server_select').change(function() {
                    $('#refresh_button').click();
                });
                $('#refresh_button').click(function() {
                    server_index = $('#server_select option:selected').index();
                    if (server_index <= 0) {
                        return;
                    }
                    if (oTable) {
                        oTable.fnDestroy();
                    }
                    if (update_time_info_interval_id) {
                        clearInterval(update_time_info_interval_id);
                    }
                    $('#server_info_title').text('Server Information');
                    $('#refresh_button').hide();
                    $('#server_info').html('');
                    $('#cache_info').html('');
                    $('#last_updated').html('');
                    load_server_status(server_index-1);
                });
                $('#div_processes').click(function() {
                    if ($('#ch_processes').attr('checked')) {
                        $.fn.dataTableExt.afnFiltering = [];
                    } else {
                        $.fn.dataTableExt.afnFiltering.push (
                            function(oSettings, aData, iDataIndex) {
                                var iColumn = 1;
                                if (aData[iColumn]=='-') {
                                    return false;
                                }
                                return true;
                            }
                        );
                    }
                    if (oTable) {
                        oTable.fnDraw();
                    }
                });
            });

            function load_server_status(server_index) {
                var url = url_base + server_index;
                oTable = null;
                oTable = $('#process').dataTable( {
                    "bProcessing": true,
                    "bStateSave": true,
                    "bAutoWidth": false,
                    "sScrollX": "100%", 
                    "sScrollXInner": "200%",
                    "bScrollCollapse": true,
                    "sAjaxSource": url,
                                   "aoColumns": [
                                        null,
                                        { "sType": "numeric" },
                                        null,
                                        null,
                                        { "sType": "numeric" },
                                        { "sType": "numeric" },
                                        { "sType": "numeric" },
                                        { "sType": "numeric" },
                                        { "sType": "numeric" },
                                        { "sType": "numeric" },
                                        { "sType": "ip-address" },
                                        null,
                                        null
                                    ],
                                   "aaSorting": [[ 1, "desc" ]],
                                   "iDisplayLength": 10,
                                   "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "全部"]],
                    "sDom": 'T<"clear">lfrtip',
                    "oTableTools": {
                        "sSwfPath": "datatables/swf/copy_csv_xls_pdf.swf",
                        "aButtons": [
                            {
                                "sExtends": "csv",
                                "sButtonText": "<img src='images/export.png' width='24' valign='middle'></img>"
                            }
                        ]
                    },
                    "fnServerData": function ( sSource, aoData, fnCallback, oSettings ) {
                                        oSettings.jqXHR = $.ajax( {
                                            "dataType": 'json',
                                            "type": "GET",
                                            "url": sSource,
                                            "data": aoData,
                                            "success":  function (json) {
                                                            // start of default callback function in dataTables
                                                            if ( json.sError ) {
                                                                oSettings.oApi._fnLog( oSettings, 0, json.sError );
                                                            }

                                                            $(oSettings.oInstance).trigger('xhr', [oSettings, json]);
                                                            fnCallback( json );
                                                            // end of default callback function in dataTables
                                                            if (json.error) {
                                                                $('#server_info').html(json.error);
                                                            } else {
                                                                last_updated = new Date();
                                                                $('#last_updated').html('Last Updated: '+get_formatted_date(last_updated)+' (0 second since last upated)');
                                                                update_time_info_interval_id = setInterval("update_time_info()", 1000);
                                                                $(document).attr('title','JAAT - '+ servers[server_index] + ' - ' + get_formatted_date(last_updated));
                                                                $('#server_info_title').text('Server Information - ' + servers[server_index]);
                                                                $('#refresh_button').show();
                                                                for (var index=0; index<json.serverInfo.length; index++) {
                                                                    $('#server_info').append(json.serverInfo[index] + "<br />");
                                                                }
                                                                for (var index=0; index<json.cacheInfo.length; index++) {
                                                                    $('#cache_info').append(json.cacheInfo[index] + "<br />");
                                                                }
                                                            }
                                                        },
                                            "error": function(xhr, textStatus, error) {
                                                        oTable.fnProcessingIndicator(false);
                                                        $('#server_info').html("Failed to query information from " + url + " [error message: " + error +"]");
                                                     }
                                        } );
                                    }
                } );
            };
            
            function update_time_info() {
                var now = new Date();
                var age = parseInt((now.getTime()-last_updated.getTime())/1000);
                if (age == 1) {
                    $('#last_updated').html('Last Updated: '+get_formatted_date(last_updated)+" ("+ age +' second since last updated)');
                } else {
                    $('#last_updated').html('Last Updated: '+get_formatted_date(last_updated)+" ("+ age +' seconds since last updated)');
                }
            }
        </script>
    </head>
    <body id="dt_example">
        <div id="container">
            <div class="full_width big">
                JAAT - Just Another Apache Top
            </div>
            
            <h1></h1>
            <form method="get" action="./">
            <div id="form">
                <div class="styled-select">
                    <img src="images/server.png" class="tooltip" title="Select the Apache server to be monitored" height="32px" valign="middle"></img>
                    <select id="server_select"><option value="-1">Select the Apache Server</select>
                    <span id="last_updated"></span><span id="refresh_button" style="display:none;"><img src="images/refresh.png" class="tooltip" title="Reload now" valign="middle"></img></span>
                </div>
            </div>
            
            <h1><label for="ch_processes">Processes </label><span id="div_processes"><input type="checkbox" id="ch_processes" name="ch_processes" data-on="All" data-off="Active Only" /></span></h1>
            </form>

            <div id="dynamic">
                <table cellpadding="0" cellspacing="0" border="0" class="display" id="process">
                    <thead>
                        <tr>
                            <th><span class="tooltip" title="Child Server number - generation">Srv</span></th>
                            <th><span class="tooltip" title="OS process ID">PID</span></th>
                            <th><span class="tooltip" title="Number of accesses this connection / this child / this slot">Acc<span></th>
                            <th><span class="tooltip" title="Mode of operation">M</span></th>
                            <th><span class="tooltip" title="CPU usage, number of seconds">CPU</span></th>
                            <th><span class="tooltip" title="Seconds since beginning of most recent request">SS</span></th>
                            <th><span class="tooltip" title="Milliseconds required to process most recent request">Req</span></th>
                            <th><span class="tooltip" title="Kilobytes transferred this connection">Conn</span></th>
                            <th><span class="tooltip" title="Megabytes transferred this child">Child</span></th>
                            <th><span class="tooltip" title="Total megabytes transferred this slot">Slot</span></th>
                            <th><span class="tooltip" title="IP Address of client">Client</span></th>
                            <th><span class="tooltip" title="Name of virtual host">VHost</span></th>
                            <th><span class="tooltip" title="Request URI">Request</span></th>
                        </tr>
                    </thead>
                    <tbody>
        
                    </tbody>
                    <tfoot>
                        <tr>
                            <th><span class="tooltip" title="Child Server number - generation">Srv</span></th>
                            <th><span class="tooltip" title="OS process ID">PID</span></th>
                            <th><span class="tooltip" title="Number of accesses this connection / this child / this slot">Acc<span></th>
                            <th><span class="tooltip" title="Mode of operation">M</span></th>
                            <th><span class="tooltip" title="CPU usage, number of seconds">CPU</span></th>
                            <th><span class="tooltip" title="Seconds since beginning of most recent request">SS</span></th>
                            <th><span class="tooltip" title="Milliseconds required to process most recent request">Req</span></th>
                            <th><span class="tooltip" title="Kilobytes transferred this connection">Conn</span></th>
                            <th><span class="tooltip" title="Megabytes transferred this child">Child</span></th>
                            <th><span class="tooltip" title="Total megabytes transferred this slot">Slot</span></th>
                            <th><span class="tooltip" title="IP Address of client">Client</span></th>
                            <th><span class="tooltip" title="Name of virtual host">VHost</span></th>
                            <th><span class="tooltip" title="Request URI">Request</span></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="spacer"></div>

            <h1 id="server_info_title">Server Information</h1>
            <div id="server_info"></div>
            
            <h1>SSL/TLS Session Cache Status</h1>
            <div id="cache_info"></div>
        </div>
    <script src="js/jquery.tzCheckbox.js"></script>
    </body>
</html>     
