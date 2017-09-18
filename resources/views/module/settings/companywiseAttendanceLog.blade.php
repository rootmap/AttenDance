<?php
$pageinfo = array("Company Wise Attendance Report", "Company Wise Attendance Report", "", "SUL", "Filter Company Wise Attendance ", "Genarate Report");
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
                                        <select name="company_id" id="company_id"  class="form-control">
                                        	<option value="0">Select Company</option>
                                        	@foreach($company as $row)
                                        		<option value="{{$row->id}}">{{$row->name}}</option>
                                        	@endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Pick A Date</label>
                                        <label class="input-group datepicker-only-init">
                                            <input type="text" id="date" name="date" class="form-control" placeholder="Type Date"/>
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
                                                field: "emp_code",
                                                title: "Emp Code",
                                                width: "50px"
                                            },
                                            {
                                                field: "start_date",
                                                title: "Start Date",
                                                width: "50px",
                                                format: "{0:yyyy-mm-dd}"
                                            },
                                            {
                                                field: "in_time",
                                                title: "In Time ",
                                                width: "50px",
                                                format: "{0:HH:mm:ss}"
                                            },
                                            {
                                                field: "end_date",
                                                title: "End Date",
                                                width: "50px",
                                                filterable: true,
                                                format: "{0:yyyy-mm-dd}"
                                            },
                                            {
                                                field: "out_time",
                                                title: "Out Time",
                                                width: "50px",
                                                format: "{0:HH:mm:ss}"
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
                                                format: "{0:HH:mm:ss}"
                                            },
                                            {
                                                field: "day_status",
                                                title: "Status",
                                                width: "50px"
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
                                        var date = $("input[name='date']").val();
                                        // alert(employee_code);

                                        var param = {'company_id': company_id,
                                            'date': date,
                                            '_token': '<?= csrf_token() ?>'};
                                        var link = "Jobcard/CompanyJson";
                                        KendoManualInitialized(link, param);
                                    });
                                    $("#export_excel").click(function () {

                                    	var company_id = $("select[name='company_id']").val();
                                        var date = $("input[name='date']").val();

                                        
                                        if (company_id == '')
                                        {
                                            company_id = 0;
                                        }
                                        
                                        if (date == '')
                                        {
                                            date = 0;
                                        }
                                       
                                        var param = {'company_id': company_id, 'date': date,'_token': '<?= csrf_token() ?>'};
                                        var link = "<?= url('/') ?>/Jobcard/Company/Export/" + company_id + "/" + date;
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

                                    $("button[name='filter']").click(function () {

                                        var emp_code = $("input[name='emp_code']").val();
                                        if (emp_code == '')
                                        {
                                            emp_code = 0;
                                        }


                                        $.post("<?= url('Jobcard/GetEpmloyeedetails') ?>", {'emp_code': emp_code, '_token': '<?= csrf_token() ?>'}, function (data) {
                                            var total = data.length;

                                            if (total != 0)
                                            {
                                                var str = '';

                                                $.each(data, function (index, val) {
                                                    // console.log(val.emp_email);
                                                    var name = '';
                                                    if (typeof (val.emp_name) == "undefined" || val.emp_name == null) {
                                                        name = 'Not Defined';
                                                    } else {
                                                        name = val.emp_name;
                                                    }
                                                    var phone = '';
                                                    if (typeof (val.emp_phone) == "undefined" || val.emp_phone == null) {
                                                        phone = 'xxxxxxxxxxx';
                                                    } else {
                                                        phone = val.emp_phone;
                                                    }
                                                    var job_location = '';
                                                    if (typeof (val.emp_job_location) == "undefined" || val.emp_job_location == null) {
                                                        job_location = 'Not Defined';
                                                    } else {
                                                        job_location = val.emp_phone;
                                                    }
                                                    var designation = '';
                                                    if (typeof (val.emp_designation) == "undefined" || val.emp_designation == null) {
                                                        designation = 'Not Defined';
                                                    } else {
                                                        designation = val.emp_phone;
                                                    }
                                                    var section = '';
                                                    if (typeof (val.emp_section) == "undefined" || val.emp_section == null) {
                                                        section = 'Not Defined';
                                                    } else {
                                                        section = val.emp_section;
                                                    }
                                                    var department = '';
                                                    if (typeof (val.emp_designation) == "undefined" || val.emp_designation == null) {
                                                        department = 'Not Defined';
                                                    } else {
                                                        department = val.emp_designation;
                                                    }
                                                    var supervisor = '';
                                                    if (typeof (val.emp_supervisor) == "undefined" || val.emp_supervisor == null) {
                                                        supervisor = 'Not Defined';
                                                    } else {
                                                        supervisor = val.emp_supervisor;
                                                    }


                                                    str += '<section class="card">' +
                                                            '<div class="card-header bg-faded">' +
                                                            '<span class="cat__core__title">' +
                                                            '  <h5 class="font-weight-bold"><i class="fa fa-briefcase text-primary" aria-hidden="true"></i> Job Detail</h5>' +
                                                            ' </span>' +
                                                            ' </div>' +
                                                            '  <div class="card-block">' +
                                                            ' <div class="row">' +
                                                            ' <div class="col-xs-center col-lg-12 col-md-12 col-xs-12">' +
                                                            ' <table class="table">' +
                                                            '  <tbody>' +
                                                            ' <tr>' +
                                                            '  <td>' +
                                                            '  <i class="fa fa-address-book text-primary" aria-hidden="true"></i>' +
                                                            ' Name:' +
                                                            ' </td>' +
                                                            ' <td>' +
                                                            ' <span class="font-weight-bold">' + name + '</span>' +
                                                            ' </td>' +
                                                            ' <td>' +
                                                            '    <i class="fa fa-cubes text-primary" aria-hidden="true"></i>' +
                                                            '   Email:' +
                                                            ' </td>' +
                                                            ' <td>' +
                                                            '     <span class="font-weight-bold">' + val.emp_email + '</span>' +
                                                            ' </td>' +
                                                            ' <td>' +
                                                            '     <i class="fa fa-cubes text-primary" aria-hidden="true"></i>' +
                                                            '    Phone:' +
                                                            ' </td>' +
                                                            '  <td>' +
                                                            '   <span class="font-weight-bold">' + phone + '</span>' +
                                                            ' </td>' +
                                                            ' </tr>' +
                                                            ' <tr>' +
                                                            '   <td>' +
                                                            '    <i class="fa fa-building text-primary" aria-hidden="true"></i>' +
                                                            '    Company:' +
                                                            ' </td>' +
                                                            ' <td>' +
                                                            '    <span class="font-weight-bold">' + val.emp_company + '</span>' +
                                                            ' </td>' +
                                                            '  <td>' +
                                                            '      <i class="fa fa-address-book text-primary" aria-hidden="true"></i>' +
                                                            '     Designation:' +
                                                            ' </td>' +
                                                            ' <td>' +
                                                            '     <span class="font-weight-bold">' + department + '</span>' +
                                                            ' </td>' +
                                                            '  <td>' +
                                                            '      <i class="fa fa-cubes text-primary" aria-hidden="true"></i>' +
                                                            '     Department:' +
                                                            ' </td>' +
                                                            '  <td>' +
                                                            '       <span class="font-weight-bold">' + val.emp_department + '</span>' +
                                                            '   </td>' +
                                                            ' </tr>' +
                                                            ' <tr>' +
                                                            '     <td>' +
                                                            '         <i class="fa fa-cube text-primary" aria-hidden="true"></i>' +
                                                            '         Section:' +
                                                            '    </td>' +
                                                            '    <td>' +
                                                            '        <span class="font-weight-bold">' + section + '</span>' +
                                                            '     </td>' +
                                                            '    <td>' +
                                                            '       <i class="fa fa-map-marker text-primary" aria-hidden="true"></i>' +
                                                            '      Job Location:' +
                                                            '  </td>' +
                                                            '   <td>' +
                                                            '       <span class="font-weight-bold">' + job_location + '</span>' +
                                                            '   </td>' +
                                                            '   <td>' +
                                                            '      <i class="fa fa-user text-primary" aria-hidden="true"></i>' +
                                                            '      Supervisor:' +
                                                            '  </td>' +
                                                            '   <td>' +
                                                            '     <span class="font-weight-bold">' + supervisor + '</span>' +
                                                            '  </td>' +
                                                            ' </tr>' +
                                                            '</tbody>' +
                                                            '</table>' +
                                                            '</div>' +
                                                            '</div>' +
                                                            '</div>' +
                                                            '</section>';
//                                                    str += '<p  id="' + val.emp_code + '">' + i + '. ' + val.name + ' -' + val.emp_code + '</p>';
                                                });

                                                $(".emp_details").html(str);
                                            }
                                            else
                                            {
                                                $(".emp_details").html('0 Record Found');
                                            }
                                            //console.log(data);
                                        });
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
<?= MenuPageController::genarateKendoDatePicker(array("date")) ?>
@endsection
