<?php
$pageinfo = array("Attendance Raw Log Manual Entry", "Attendance Log Manual Entry", "", "SUL", "Attendance Log Manual Entry", "Save Manual Entry");
?>

@extends('layout.master')
@section('content')
@include('include.coreBarcum')
<div class="row">
    <div class="col-lg-12">
        <div class="cat__core__sortable" id="left-col">
            <section class="card" order-id="card-1">


                <div class="card-header">
                    <h5 class="mb-0 text-black">
                        <strong>{{$pageinfo[4]}}</strong>
                        <!--<small class="text-muted">All cards are draggable and sortable!</small>-->
                    </h5>
                </div>





                <div class="card-block" style="">

                    <div class="row">
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
							
							<form method="post" action="{{url('Attendance/log-manual-entry')}}">
							{{csrf_field()}}
                            <div class="row">
                                <!--Added For Leave User Data Filtering-->
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Employee</label>
                                        <select class="form-control select-search" id="emp_code" name="emp_code">
                                            <option value="">Select Employee</option>
                                            @if(isset($employee))
                                            @foreach($employee as $erow)
                                            <option value="{{$erow['emp_code']}}">{{$erow['name']}}</option>
                                            @endforeach
                                            @endif

                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Log date</label>
                                        <label class="input-group datepicker-only-init">
                                            <input type="text" id="start_date" name="start_date" class="form-control" placeholder="Type Start Day"/>
                                            <span class="input-group-addon" style="">
                                                <i class="icmn-calendar"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Log Time</label>
                                        <label class="input-group datepicker-only-init">
                                            <input type="text" id="raw_log_time" name="raw_log_time" class="form-control" placeholder="Type Time "/>
                                            <span class="input-group-addon" style="">
                                                <i class="icmn-clock"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                            </div>
							
							
							<!-- block jobsummary start-->
							<div class="card-block">

                    <div class="row">
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">
                                <div id="grid" class="col-md-12"></div>

                                <div class="col-mb-12">
                                    <ul class="breadcrumb breadcrumb--custom" id="FooterJobCard">
                                        <li class="breadcrumb-item"><span></span></li>
                                    </ul>
                                    <ul class="breadcrumb breadcrumb--custom" id="FooterJobCardSum">
                                        <li class="breadcrumb-item"><span></span></li>
                                    </ul>
                                </div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Employee/Employeeinfo/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                            </script>



                            <script type="text/javascript">


                                var dayStatus = ["A"];

                                $.getJSON('/Jobcard/day_status', function (data) {
                                    $.each(data, function (index, val) {
                                        $.each(val, function (index, val2) {
                                            dayStatus.push(val2);
                                        });
                                    });
                                });

                                function KendoManualInitialized(link, data)
                                {
                                    var singleSelectEditor = function (container, options) {

                                        // console.log(dayStatus)
                                        $('<input data-bind="value:' + options.field + '"/>')
                                                .appendTo(container)
                                                .kendoDropDownList({
                                                    suggest: true,
                                                    dataSource: dayStatus
                                                });

                                    };

                                    var dataSource = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?= url('"+link+"') ?>",
                                                type: "POST",
                                                data: data,
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
                                                    $("#grid").data("kendoGrid").dataSource.read();
                                                    console.log(e.responseText);
                                                    var st = e.responseText;
                                                    if (st == 3) {
                                                        swal({
                                                            title: "No Leave Balance Found!",
                                                            text: "Please Contact With HR",
                                                            type: "warning"
                                                        });
                                                    } else if (st == 2) {
                                                        swal({
                                                            title: "No Leave Entry Found!",
                                                            text: "Please Contact With HR",
                                                            type: "warning"
                                                        });
                                                    } else if (st == 1) {
                                                        swal({
                                                            title: "Data Updated Successfully!",
                                                            text: "Re-Generating Your Jobcard, Please wait...",
                                                            type: "success"
                                                        });
                                                    }

                                                }
                                            }

                                        },
                                        batch: true,
                                        // autoSync: true,
                                        // serverPaging: true,
                                        schema: {
                                            data: "data",
                                            total: "total",
                                            model: {
                                                id: "id",
                                                fields: {
//                                                    id: {
//                                                        type: "number"
//                                                    },
                                                    emp_code: {
                                                        type: "string"
                                                    },
                                                    start_date: {
                                                        type: "string",
                                                        editable: false
                                                    },
                                                    in_time: {
                                                        type: "time"
                                                    },
                                                    end_date: {
                                                        type: "string",
                                                        editable: true
                                                    },
                                                    out_time: {
                                                        type: "time"
                                                    }
                                                    ,
                                                    total_time: {
                                                        type: "time"
                                                    }
                                                    ,
                                                    total_ot: {
                                                        type: "time"
                                                    }
                                                    ,
                                                    day_status: {
                                                        type: "string"
                                                    },
                                                }
                                            }
                                        },
                                        pageSize:31,
                                        height: 550,
                                        groupable: true,
                                        sortable: true
                                    });
                                    $("#grid").kendoGrid({
                                        dataBound: gridDataBoundJobcard,
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
                                            {
                                                field: "start_date",
                                                title: "Start Date",
                                                width: "50px",
                                                format: "{0:yyyy-mm-dd}"
//                                                template: "#= kendo.toString(date,'yyyy-mm-dd') #",
                                                        // editor: dateEditor
                                            },
                                            {
                                                field: "in_time",
                                                title: "In Time ",
                                                width: "50px",
                                                format: "{0:HH:mm:ss}",
                                                editor: timeEditor
                                            },
                                            {
                                                field: "end_date",
                                                title: "End Date",
                                                width: "50px",
                                                filterable: true,
                                                format: "{0:yyyy-mm-dd}",
                                                editor: dateEditor
                                            },
                                            {
                                                field: "out_time",
                                                title: "Out Time",
                                                width: "50px",
                                                format: "{0:HH:mm:ss}",
                                                editor: timeEditor
                                            },
//                                            {
//                                                field: "total_time",
//                                                title: "Total Hour",
//                                                width: "50px",
//                                                format: "{0:HH:mm:ss}",
//                                                editor: timeEditor
//                                            },
                                            {
                                                field: "total_ot",
                                                title: "Over Time",
                                                width: "50px",
                                                format: "{0:HH:mm:ss}",
                                                editor: timeEditor
                                            },
                                            {
                                                field: "day_status",
                                                title: "Status",
                                                width: "50px",
                                                editor: singleSelectEditor
                                            },
                                            {
                                                title: "Action",
                                                width: "60px",
                                                command: ['edit']
//                                                template: kendo.template($("#action_template").html())
                                            }
                                        ],
                                    });
                                }
                                function timeEditor(container, options) {
                                    $('<input data-text-field="' + options.field + '" data-value-field="' + options.field + '" data-bind="value:' + options.field + '" data-format="' + options.format + '"/>')
                                            .appendTo(container)
                                            .kendoTimePicker({});
                                }

                                function dateEditor(container, options) {
                                    console.log("options", options);
                                    $('<input data-text-field="' + options.field + '" data-value-field="' + options.field + '" data-bind="value:' + options.field + '" data-format="' + options.format + '"/>')
                                            .appendTo(container)
                                            .kendoDatePicker({});
                                }
                                $(document).ready(function () {

                                    $("input[name='start_date']").change(function () {
										var date=$(this).val();
                                        var employee_code = $("select[name='emp_code']").val();
                                        //var s_date = $("input[name='date']").val();
                                        //var e_date = $("input[name='end_date']").val();
                                        // alert(employee_code);

                                        var param = {'emp_code': employee_code,
                                            'start_date': date,
                                            'end_date': date,
                                            '_token': '<?= csrf_token() ?>'};
                                        var link = "Jobcard/AdminJson";
                                        KendoManualInitialized(link, param);
                                    });
									
									
									
                                    $("#export_excel").click(function () {

                                        var emp_code = $("input[name='emp_code']").val();
                                        if (emp_code == '')
                                        {
                                            emp_code = 0;
                                        }
                                        var start_date = $("input[name='start_date']").val();
                                        if (start_date == '')
                                        {
                                            start_date = 0;
                                        }
                                        var end_date = $("input[name='end_date']").val();
                                        if (end_date == '')
                                        {
                                            end_date = 0;
                                        }
                                        var param = {'emp_code': emp_code, 'start_date': start_date, 'end_date': end_date, '_token': '<?= csrf_token() ?>'};
                                        var link = "<?= url('/') ?>/Jobcard/Export/AdminExcel/" + emp_code + "/" + start_date + "/" + end_date;
                                        window.location.href = link;
                                    });
                                    $("#export_pdf").click(function () {

                                        var emp_code = $("input[name='emp_code']").val();
                                        if (emp_code == '')
                                        {
                                            emp_code = 0;
                                        }
                                        var start_date = $("input[name='start_date']").val();
                                        if (start_date == '')
                                        {
                                            start_date = 0;
                                        }
                                        var end_date = $("input[name='end_date']").val();
                                        if (end_date == '')
                                        {
                                            end_date = 0;
                                        }
                                        var param = {'emp_code': emp_code, 'start_date': start_date, 'end_date': end_date, '_token': '<?= csrf_token() ?>'};
                                        var link = "<?= url('/') ?>/Jobcard/Export/AdminPdf/" + emp_code + "/" + start_date + "/" + end_date;
                                        window.location.href = link;
                                    });

                                    
                                });



                            </script>
                            <!-- kendo table code end fro here-->
                            <!--Vertical Form Ends Here-->
                        </div>
                    </div>
                </div>
							<!--Block JobSummary End-->			
							
							
							
							
							
							
							
							
							
                            <div class="form-actions">
                                <button type="submit" name="submit" id="filter" class="btn btn-primary"> Save Manual Log </button>
                            </div>
							
							</form>

                            <!-- kendo table code end fro here-->
                            <!--Vertical Form Ends Here-->
                        </div>
						
						
						
						
						
						
						
						<div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">

                                <div class="col-xl-11 ">
                                    <strong>Manual Punch Log Entry Today</strong>
                                </div>
                                <div id="grid" class="col-md-12"></div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('ManualJobcard/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a style="display:none !important;" class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "ManualJobcard/Delete",
                                            data: {id: id, '_token': '<?= csrf_token() ?>'},
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
                                                url: "<?= url('ManualRawLog/Json') ?>",
                                                type: "GET",
                                                datatype: "json"

                                            }
                                        },
                                        schema: {
                                            data: "data",
                                            total: "total",
                                            model: {
                                                id: "id",
                                                fields: {
                                                    id: {type: "number"},
                                                    raw_emp_code: {type: "string"},
                                                    raw_date: {type: "string"},
                                                    raw_time: {type: "string"},
													is_read: {type: "boolean"},
                                                    date: {type: "string"},
                                                    created_at: {type: "string"}
                                                }
                                            }
                                        },
                                        pageSize: 20,
                                        height: 550,
                                        groupable: true,
                                        sortable: true
                                      
                                    });
									
                                    $("#grid").kendoGrid({
                                        dataBound: gridDataBound,
                                        dataSource: dataSource,
                                        filterable: true,
                                        pageable: {
                                            refresh: true,
                                            pageSizes: [5,50,100,500,1000],
                                            buttonCount: 5
                                        },
                                        sortable: true,
                                        groupable: true,
                                        columns: [
                                            {field: "id", title: "#", width: "100px", filterable: false},
                                            {field: "raw_emp_code", title: "Emp Code", width: "100px"},
                                            {field: "raw_date", title: "Raw Log Date", width: "100px"},
                                            {field: "raw_time", title: "Raw Log Time", width: "100px"},
											{field: "is_read", title: "Read Status", width: "100px"},
                                            {field: "created_at", title: "Created ", width: "70px", }
                                        ],
                                    });
                                });

                            </script>
                            <!-- kendo table code end fro here-->
                            <!--Vertical Form Ends Here-->
                        </div>
						
						</div>
						</div>
						
						
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
@section('extraHeader')
<link rel="stylesheet" type="text/css" href="{{url('vendors/select2/dist/css/select2.min.css')}}">
@endsection 
@section('extraFooter')
@include('include.coreKendo')
 <!--<script src="{{url('vendors/bootstrap-select/dist/js/bootstrap-select.min.js')}}"></script>-->
<script src="{{url('vendors/select2/dist/js/select2.full.min.js')}}"></script> 
<script type="text/javascript">
                                $(document).ready(function () {
                                    $(".select-search").select2();
									
									$('#raw_log_time').datetimepicker({
										widgetPositioning: {
											vertical: 'bottom'
										},
										icons: {
											time: "fa fa-clock-o",
											date: "fa fa-calendar",
											up: "fa fa-arrow-up",
											down: "fa fa-arrow-down",
											previous: 'fa fa-arrow-left',
											next: 'fa fa-arrow-right'
										},
										format: 'HH:mm:ss'
									});
									
								});
</script>
<?= MenuPageController::genarateKendoDatePicker(array("start_date")) ?>
@endsection
