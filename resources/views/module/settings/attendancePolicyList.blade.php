<?php
$pageinfo = array("Attendance Policy Data List", "Attendance Policy Data List", "", "SUL", "Filter list", "Filtered Report");
?>

@extends('layout.master')
@section('content')
@include('include.coreBarcum')
<div class="row">
    <div class="col-lg-12">
        <div class="cat__core__sortable" id="left-col">
            <section class="card" order-id="card-1">


                <!--                <div class="card-header">
                                    <h5 class="mb-0 text-black">
                                        <strong>{{$pageinfo[4]}}</strong>
                                        <small class="text-muted">All cards are draggable and sortable!</small>
                                    </h5>
                                </div>-->






            </section>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-lg-12">
        <div class="cat__core__sortable" id="left-col">
            <section class="card" order-id="card-1">


                <div class="card-header">

                    <div class="pull-right cat__core__sortable__control">

                        <a href="javascript:void(0)" id="export_excel" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
                        <a href="javascript:void(0)"  id="export_pdf" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>
                    </div>

                    <h5 class="mb-0 text-black">
                        <strong>{{$pageinfo[0]}}</strong>
                        <!--<small class="text-muted">All cards are draggable and sortable!</small>-->
                    </h5>
                </div>





                <div class="card-block">

                    <div class="row">
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">
                                <div id="grid" class="col-md-12"></div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/AttendancePolicy/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);"><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "<?= url('Settings/ShiftAssign/Delete') ?>",
                                            data: {
                                                id: id,
                                                '_token': '<?= csrf_token() ?>'
                                            },
                                            success: function (result) {
                                                $(".k-i-refresh").click();
                                            }
                                        });
                                    }
                                }
                            </script>
                            <script type="text/javascript">

                                $(document).ready(function () {

                                    $("#export_excel").click(function () {
                                        var link = "<?= url('/') ?>/Export/Settings/AttendancePolicy/List/Excel";
                                        window.location.href = link;
                                    });

                                    $("#export_pdf").click(function () {
                                        var link = "<?= url('/') ?>/Export/Settings/AttendancePolicy/List/Pdf";
                                        window.location.href = link;
                                    });

                                    var dataSource = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?= url('Settings/AttendancePolicy/json') ?>",
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
                                                    policy_title: {type: "string"},
                                                    company: {type: "string"},
                                                    entry_buffer_time:{type: "string"},
                                                    total_hours:{type: "string"},
                                                    is_halfday_applicable:{type: "boolean"},
                                                    half_day_name:{type: "string"},
                                                    half_day_office_end_time:{type: "string"},
                                                    is_ot_applicable:{type: "boolean"},
                                                    is_ot_buffer_time:{type: "boolean"},
                                                    is_ot_max_active:{type: "boolean"},
                                                    start_date: {type: "string"},
                                                    end_date: {type: "string"}
                                                }
                                            }
                                        },
                                        pageSize: 10,
                                        serverPaging: true,
                                        serverFiltering: true,
                                        serverSorting: true
                                    });
                                    $("#grid").kendoGrid({
                                        dataBound: gridDataBound,
                                        dataSource: dataSource,
                                        filterable: false,
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
                                            {field: "id", title: "#NO.", width: "40px", filterable: false},
                                            {field: "policy_title", title: "Policy Title ", width: "50px"},
                                            {field: "company", title: "Company Name", width: "80px"},
                                            {field: "entry_buffer_time", title: "Entry buffer Time", width: "65px"},
                                            {field: "total_hours", title: "Total Hours", width: "55px"},
                                            {field: "is_halfday_applicable", title: "Is Half Day", width: "50px"},
                                            {field: "half_day_name", title: "Half Day Work", width: "65px"},
                                            {field: "half_day_office_end_time", title: "Half Day Office End Time", width: "60px"},
                                            {field: "is_ot_applicable", title: "Is OT", width: "30px"},
                                            {field: "is_ot_buffer_time", title: "Is OT Buffer Time", width: "50px"},
                                            {field: "is_ot_max_active", title: "Is OT Max active", width: "50px"},
                                            {field: "office_start_time", title: "Office start Time", width: "50px"},
                                            {field: "office_end_time", title: "Office End time", width: "50px"},
                                            {
                                                title: "Action", width: "70px",
                                                template: kendo.template($("#action_template").html())
                                            }
                                        ],
                                    });

                                });



                            </script>
                            <!-- kendo table code end fro here-->
                            <!--Vertical Form Ends Here-->
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
@section('extraFooter')
@include('include.coreKendo')
@include('ajax_include.company_wise_department')
@include('ajax_include.department_wise_section')
@include('ajax_include.section_wise_designation')
@endsection
