<?php
$pageinfo=array("Kendo Test","Kendo Initiate Feature","Kendo Testing Table Here","SUL");
?>
@extends('layout.master')
@section('content')
@include('include.coreBarcum')

	<!-- kendo table code start from here-->
	<div id="example">
    	<div id="grid"></div>
    </div>
	<script id="action_template" type="text/x-kendo-template">
        <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
    </script>
    
    <script type="text/javascript">
        function deleteClick(id) {
            var c = confirm("Do you want to delete?");
            if (c === true) {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "kendoDelete",
                    data: {id: id},
                    success: function (result) {
                        $(".k-i-refresh").click();
                    }
                });
            }
        }

    </script>
    <script type="text/javascript">
        $(document).ready(function () {
            var dataSource = new kendo.data.DataSource({
                transport: {
                    read: {
                        url: "KendoTest",
                        type: "GET",
                        datatype: "json"

                    }
                },
                autoSync: false,
                schema: {
                    data: "data",
                    total: "total",
                    model: {
                        id: "id",
                        fields: {
                            id: {type: "number"},
                            name: {type: "string"},
                            date: {type: "string"}
                        }
                    }
                },
                pageSize: 10,
                serverPaging: true,
                serverFiltering: true,
                serverSorting: true
            });
            $("#grid").kendoGrid({
                dataSource: dataSource,
                filterable: true,
                pageable: {
                    refresh: true,
                    input: true,
                    numeric: false,
                    pageSizes: true,
                    pageSizes:[10, 20, 50, 100, 200, 400]
                },
                sortable: true,
                groupable: true,
                columns: [
                    {field: "id", title: "#", width: "40px", filterable: false},
                    {field: "name", title: "Country Name ", width: "80px"},
                    {field: "date", title: "Created ", width: "100px",},
                    {
                        title: "Action", width: "80px",
                        template: kendo.template($("#action_template").html())
                    }
                ],
            });
        });

    </script>
	<!-- kendo table code end fro here-->
@endsection

@section('extraFooter')
	@include('include.coreKendo')
@endsection