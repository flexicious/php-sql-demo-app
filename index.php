<?php
    session_start();

    $_SESSION["CONTEXT_PATH"] = "php-sql-demo-app";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grid</title>

    <!--script imports-->

    <!--These are jquery and plugins that we use from jquery-->
    <script type="text/javascript"
            src="http://htmltreegrid.com/demo/external/js/adapters/jquery/jquery-1.8.2.js"></script>
    <script type="text/javascript"
            src="http://htmltreegrid.com/demo/external/js/adapters/jquery/jquery-ui-1.9.1.custom.min.js"></script>
    <script type="text/javascript"
            src="http://htmltreegrid.com/demo/external/js/adapters/jquery/jquery.maskedinput-1.3.js"></script>
    <script type="text/javascript"
            src="http://htmltreegrid.com/demo/external/js/adapters/jquery/jquery.watermarkinput.js"></script>
    <script type="text/javascript"
            src="http://htmltreegrid.com/demo/external/js/adapters/jquery/jquery.ui.menu.js"></script>
    <script type="text/javascript"
            src="http://htmltreegrid.com/demo/external/js/adapters/jquery/jquery.toaster.js"></script>

    <script type="text/javascript"
            src="js/lib/html2canvas.js"></script>
    <script type="text/javascript"
            src="js/lib/jspdf.source.js"></script>


    <!--End-->
    <script src="http://htmltreegrid.com/demo/minified-compiled-jquery.js"></script>
    <script src="js/lib/grid/Configuration.js"></script>
    <script src="js/lib/grid/themes.js"></script>
    <script src="js/app/Spinner.js"></script>

    <script src="js/app/GridConfig.js"></script>
    <script src="js/app/CustomHeaderRender.js"></script>
    <script src="js/app/DeleteRenderer.js"></script>
    <script src="js/app/MultiColumnSortPopup.js"></script>
    <script src="js/app/CustomPagerRenderer.js"></script>

    <!--css imports -->

    <!--css imports-->
    <link rel="stylesheet" href="http://htmltreegrid.com/demo/flexicious/css/flexicious.css" type="text/css"/>
    <link rel="stylesheet"
          href="http://htmltreegrid.com/demo/external/css/adapter/jquery/jquery-ui-1.9.1.custom.min.css"
          type="text/css"/>
    <!--End-->
    <link rel="stylesheet" href="css/app.css"/>
    <script type="application/javascript">
        flexiciousNmsp.Constants.HTML_EXPORT_CSS = "<style>" +
                "table {font-family: Arial, Verdana, sans-serif;border-collapse: collapse; border-spacing: 0; }		" +
                "td {border: 1px solid #CCC; text-align: center;width: 10em;padding: 1em;}		" +
                "th {border: 1px solid #CCC; text-align: center;padding: 1em;background-color: #0000ff;}" +
                "tr {height: 1em;}" +
                "table tr.even {background-color: #ff0000;}" +
                "table tr.footer {border: 1px solid #0000ff; text-align: center;padding: 1em;background-color: #0000ff;}" +
                "table tr.odd {background-color:#00ff00;}</style>";

        flexiciousNmsp.Constants.WORD_EXPORT_CSS = "<style>" +
                "table {font-family: Arial, Verdana, sans-serif;border-collapse: collapse; border-spacing: 0; }		" +
                "td {border: 1px solid #CCC; text-align: center;width: 10em;padding: 1em;}		" +
                "th {border: 1px solid #CCC; text-align: center;padding: 1em;background-color: #0000ff;}" +
                "tr {height: 1em;}" +
                "table tr.even {background-color: #ff0000;}" +
                "table tr.footer {border: 1px solid #0000ff; text-align: center;padding: 1em;background-color: #0000ff;}" +
                "table tr.odd {background-color:#00ff00;}</style>";

    </script>

    <script>

        $(document).ready(function () {
            // initialize the grid.

            var grid = new flexiciousNmsp.FlexDataGrid(document.getElementById("grid-container"), {
                configuration: GridConFig.XmlConfig.sampleGrid
            });


            grid.addEventListener(this, "Share", function () {
                var filterPageSort = {};
                filterPageSort.pageIndex = grid.getColumnLevel()._pageIndex;
                filterPageSort.pageSize = grid.getPageSize();
                var sorts = grid.getCurrentSorts();
                if(sorts && sorts.length){
                    filterPageSort.sorts = [];
                    for(var i = 0; i <  sorts.length; i++){
                        filterPageSort.sorts.push({
                            sortColumn : sorts[i].sortColumn,
                            isAscending : sorts[i].isAscending,
                            sortNumeric : sorts[i].sortNumeric
                        });
                    }
                }
                var filter = grid.getRootFilter();
                if(filter.filterExpressions && filter.filterExpressions.length){
                    filterPageSort.filters = [];
                    for(var i = 0; i <  filter.filterExpressions.length; i++){
                        filterPageSort.filters.push({
                            columnName : filter.filterExpressions[i].columnName,
                            expression : filter.filterExpressions[i].expression,
                            filterOperation : filter.filterExpressions[i].filterOperation,
                            filterComparisonType : filter.filterExpressions[i].filterComparisionType
                        });
                    }
                }
                var url = window.location.origin+window.location.pathname+"?filterPageSort="+JSON.stringify(filterPageSort);
                window.prompt("Copy to clipboard: Ctrl+C, Enter", url);

            });

            var filterPageSort = {pageSize: grid.getPageSize(), pageIndex: 0};
            var params = QueryString();
            if(params.filterPageSort){
                filterPageSort = JSON.parse(params.filterPageSort);
            }


            $.ajax({
                type: "GET",
                url: GridConFig.ApiCallBaseUrl,
                data: {name: "top_data", filterPageSort: JSON.stringify(filterPageSort)},
                success: function (res) {
                    if(res.indexOf("<") == 0){
                        grid.hideSpinner();
                        alert("Error occur while loading the data.");
                        return;
                    }
                    var response = JSON.parse(res);
                    if (response.success) {
                        grid.setDataProvider(response.data);
                        grid.setTotalRecords(response.details.totalRecords);

                        if(filterPageSort.pageIndex > 0){
                            grid.setPageIndex(filterPageSort.pageIndex);
                            grid.setPageSize(filterPageSort.pageSize);
                        }
                        if(filterPageSort.filters){
                            for(var i = 0; i < filterPageSort.filters.length; i++){
                                grid.setFilterValue(filterPageSort.filters[i].columnName, filterPageSort.filters[i].expression,false);
                            }
                        }
                        if(filterPageSort.sorts){
                            grid.processSort(filterPageSort.sorts);
                        }

                        grid.rebuildHeader();

                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        var QueryString = function () {
            // This function is anonymous, is executed immediately and
            // the return value is assigned to QueryString!
            var query_string = {};
            var query = window.location.search.substring(1);
            var vars = query.split("&");
            for (var i=0;i<vars.length;i++) {
                var pair = vars[i].split("=");
                // If first entry with this name
                if (typeof query_string[pair[0]] === "undefined") {
                    query_string[pair[0]] = decodeURIComponent(pair[1]);
                    // If second entry with this name
                } else if (typeof query_string[pair[0]] === "string") {
                    var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
                    query_string[pair[0]] = arr;
                    // If third or later entry with this name
                } else {
                    query_string[pair[0]].push(decodeURIComponent(pair[1]));
                }
            }
            return query_string;
        };


        var padZero = function(args){
            if((args+"").length>1)
                return args;
            return "0"+args;
        };

        // to resolve the timezone conflict while convert to json string
        // return mysql datetime type string
        Date.prototype.toJSON = function(){
            var formatted = this.getFullYear();
            formatted += "-"+padZero(this.getMonth()+1);
            formatted += "-"+padZero(this.getDate());
            formatted += " "+padZero(this.getHours());
            formatted += ":"+padZero(this.getMinutes());
            formatted += ":"+padZero(this.getSeconds());
            return formatted;
        };


        function refreshChildren(parent, level){
            if(!parent || !level)
                return;
            $.ajax({
                url : GridConFig.ApiCallBaseUrl,
                data : {name : "child_data", parent_id : parent.id},
                type : "GET",
                success : function(res){
                    var response = JSON.parse(res);
                    if(response.success){
                        level.grid.setChildData(parent, response.data, level);
                    } else {
                        alert(response.message);
                    }
                }
            });
        }

        function onRefreshClick(){
            var grid = document.getElementById("grid-container").component;
            if(!grid || !grid.getDataProvider())
                return;
            refreshChildren(grid.getDataProvider()[0], grid.getColumnLevel())
        }

    </script>

</head>
<body>

<div class="header"><span>Grid App</span></div>
<div class="toolbar">
    <button onclick="onRefreshClick()">Refresh Children of First Parent</button>
</div>
<div class="wrapper">
    <div id="grid-container"></div>
</div>
</body>
</html>
