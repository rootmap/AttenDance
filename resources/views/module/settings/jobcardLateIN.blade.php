<?php
$pageinfo = array("Late IN Job Card Report", "Late IN Job Card Report", "", "SUL", "Filter Late IN Jobcard", "Genarate Report");
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


                            <div class="row">
                                <!--Added For Leave User Data Filtering-->
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Select Company</label>
                                        <select name="company_id"  class="form-control">
											<option value="0">Select Company</option>
											@foreach($company as $com)
												<option value="{{$com->id}}">{{$com->name}}</option>
											@endforeach
										</select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Start date</label>
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
                                        <label for="l30">End date</label>
                                        <label class="input-group datepicker-only-init">
                                            <input type="text" id="end_date" name="end_date" class="form-control" placeholder="Type End Day"/>
                                            <span class="input-group-addon" style="">
                                                <i class="icmn-calendar"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                            </div>

                            <div class="form-actions">
                                <button type="button" name="filter"  class="btn btn-primary">{{$pageinfo[5]}}</button>
                            </div>


                            <!-- kendo table code end fro here-->
                            <!--Vertical Form Ends Here-->

                            <div class=" col-md-12 emp_details">

                            </div>

                        </div>
                    </div>
                </div>
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

                        <a href="javascript:void(0)"  id="export_pdf" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>
						<a href="javascript:void(0)"  id="export_excel" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
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

                                <div class="col-mb-12">
                                    <ul class="breadcrumb breadcrumb--custom" id="FooterJobCard">
                                        <li class="breadcrumb-item"><span></span></li>
                                    </ul>
                                    <ul class="breadcrumb breadcrumb--custom" id="FooterJobCardSum">
                                        <li class="breadcrumb-item"><span></span></li>
                                    </ul>
                                </div>
                            </div>
                           


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
                                                field: "emp_code",
                                                title: "Emp Code",
                                                width: "50px"
                                            },{
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

                                    $("button[name='filter']").click(function () {

                                        var company_id = $("select[name='company_id']").val();
                                        var s_date = $("input[name='start_date']").val();
                                        var e_date = $("input[name='end_date']").val();
                                        // alert(employee_code);

                                        var param = {'company_id': company_id,
                                            'start_date': s_date,
                                            'end_date': e_date,
                                            '_token': '<?= csrf_token() ?>'};
                                        var link = "Jobcard/LateINJson";
                                        KendoManualInitialized(link, param);
                                    });
                                    
                                    $("#export_pdf").click(function () {

                                        var company_id = $("select[name='company_id']").val();
                                        if (company_id == '')
                                        {
                                            company_id = 0;
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
                                        var param = {'company_id': company_id, 'start_date': start_date, 'end_date': end_date, '_token': '<?= csrf_token() ?>'};
                                        var link = "<?= url('/') ?>/Jobcard/Export/LateINPdf/" + company_id + "/" + start_date + "/" + end_date;
                                        window.location.href = link;
                                    });
									
									$("#export_excel").click(function () {

                                        var company_id = $("select[name='company_id']").val();
                                        if (company_id == '')
                                        {
                                            company_id = 0;
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
                                        var param = {'company_id': company_id, 'start_date': start_date, 'end_date': end_date, '_token': '<?= csrf_token() ?>'};
                                        var link = "<?= url('/') ?>/Jobcard/Export/LateINExcel/" + company_id + "/" + start_date + "/" + end_date;
                                        window.location.href = link;
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
<?= MenuPageController::genarateKendoDatePicker(array("start_date", "end_date")) ?>
@endsection
