<?php
$pageinfo = array("Assign Employee Shift Data List", "Assign Employee Shift Data List", "", "SUL", "Filter list", "Filtered Report");
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

                        <a href="#" id="export_excel" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
                        <a href="#"  id="export_pdf" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>
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
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Employee/Employeeinfo/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>

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


                                        //var param = {'company_id': company_id, 'department_id': department_id, 'section_id': section_id, 'designation_id': designation_id, '_token': '<?= csrf_token() ?>'};
                                        var link = "<?= url('/') ?>/Export/Settings/ShiftAssign/List/Excel";

                                        window.location.href = link;

                                    });

                                    $("#export_pdf").click(function () {

                                        // var param = {'company_id': company_id, 'department_id': department_id, 'section_id': section_id, 'designation_id': designation_id, '_token': '<?= csrf_token() ?>'};
                                        var link = "<?= url('/') ?>/Export/Settings/ShiftAssign/List/Pdf";

                                        window.location.href = link;

                                    });

                                    var dataSource = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?= url('Settings/ShiftAssign/json') ?>",
                                                type: "GET",
                                                datatype: "json"

                                            },
                                            update: {
                                                url: "<?= url('/Jobcard/AdminUpdate') ?>",
                                                type: "POST",
                                                datatype: "json",
                                                data: {
                                                    '_token': "<?= csrf_token() ?>",
                                                },
                                                complete: function (e) {
//                                                    $("#grid").data("kendoGrid").dataSource.read();
//                                                    console.log(e.responseText);
//                                                    var st = e.responseText;
//                                                    if (st == 3) {
//                                                        swal({
//                                                            title: "No Leave Balance Found!",
//                                                            text: "Please Contact With HR",
//                                                            type: "warning"
//                                                        });
//                                                    } else if (st == 2) {
//                                                        swal({
//                                                            title: "No Leave Entry Found!",
//                                                            text: "Please Contact With HR",
//                                                            type: "warning"
//                                                        });
//                                                    } else if (st == 1) {
//                                                        swal({
//                                                            title: "Data Updated Successfully!",
//                                                            text: "Please Contact With HR",
//                                                            type: "success"
//                                                        });
//                                                    }

                                                }
                                            }
                                        },
                                        batch: true,
                                        autoSync: false,
                                        schema: {
                                            data: "data",
                                            total: "total",
                                            model: {
                                                id: "id",
                                                fields: {
                                                    id: {type: "number", editable: false},
                                                    emp_code: {type: "string", editable: false,filterable:true},
                                                    name: {type: "string", editable: false},
                                                    shift_name: {type: "string"},
                                                    company: {type: "string", editable: false},
                                                    night: {type: "boolean"},
                                                    roster: {type: "boolean"},
                                                    start_date: {type: "string"},
                                                    end_date: {type: "string"}
                                                }
                                            }
                                        },
                                        pageSize: 10,
                                        serverPaging: false,
                                        serverFiltering: false,
                                        serverSorting: false
                                    });
                                    $("#grid").kendoGrid({
                                        dataBound: gridDataBound,
                                        dataSource: dataSource,
                                        filterable: true,
                                        editable: "inline",
                                        pageable: {
                                            refresh: true,
                                            pageSizes: [5, 50, 100, 500, 1000],
                                            buttonCount: 5
                                        },
                                        sortable: true,
                                        groupable: true,
                                        columns: [
                                            {field: "id", title: "#NO.", width: "30px", filterable: false},
                                            {field: "emp_code", title: "Employee Code", width: "100px", filterable: false},
                                            {field: "name", title: "Name ", width: "80px"},
                                            {field: "company", title: "Company Name", width: "80px"},
                                            {field: "shift_name", title: "Shift Name ", width: "80px"},
                                            {field: "night", title: "Is Night Shift ", width: "50px"},
                                            {field: "roster", title: "Is Roster Shift ", width: "50px"},
//                                            {title: "Shift Start Date", width: "80px", template: "#= kendo.toString(kendo.parseDate(start_date, 'yyyy-MM-dd'), 'dd/MM/yyyy') #"},
                                            {
                                                field: "start_date",
                                                title: "Shift Start Date",
                                                width: "80px",
                                                format: "{0:yyyy-mm-dd}",
                                                editor: dateEditor
                                            },
//                                            {title: "Shift End Date", width: "80px", template: "#= kendo.toString(kendo.parseDate(end_date, 'yyyy-MM-dd'), 'dd/MM/yyyy') #"},
                                            {
                                                field: "end_date",
                                                title: "Shift End Date",
                                                width: "80px",
                                                format: "{0:yyyy-mm-dd}",
                                                editor: dateEditor
                                            },
                                            {
                                                title: "Action",
                                                width: "60px",
                                                command: ['edit']
//                                                template: kendo.template($("#action_template").html())
                                            }
//                                            {
//                                                title: "Action", width: "60px",
//                                                template: kendo.template($("#action_template").html())                                              
//                                            }
                                        ],
                                    });
                                    function timeEditor(container, options) {
                                        $('<input data-text-field="' + options.field + '" data-value-field="' + options.field + '" data-bind="value:' + options.field + '" data-format="' + options.format + '"/>')
                                                .appendTo(container)
                                                .kendoTimePicker({});
                                    }

                                    function dateEditor(container, options) {
                                  
                                        $('<input id="' + options.field + '"  data-text-field="' + options.field + '" data-value-field="' + options.field + '" data-bind="value:' + options.field + '" data-format="' + options.format + '"/>')
                                                .appendTo(container)
                                                .kendoDatePicker({});
                                        $(".k-grid-edit").hide();
                                        // console.log(options.model.end_date)
                                        $("#" + options.field).kendoDatePicker({
//                                            value: new Date(2014, 10, 20),
                                            min: new Date(options.model.end_date),
                                            month: {
                                                empty: '<span class="k-state-disabled">#= data.value #</span>'
                                            }
                                        });
                                    }

                                });



                            </script>

<!--                            <script>
                            $(document).ready(function () {
                                $("#datepicker").kendoDatePicker({
                                    value: new Date(2014, 10, 20),
                                    min: new Date(2014, 10, 10),
                                    max: new Date(2014, 11, 10),
                                    month: {
                                        empty: '<span class="k-state-disabled">#= data.value #</span>'
                                    }
                                });
                            });
                        </script>-->
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
