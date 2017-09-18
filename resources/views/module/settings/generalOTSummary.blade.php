<?php
$pageinfo = array("General OT Report Summary", "General OT Report Summary", "", "SUL", "Filter General OT Report Summary", "Genarate Report");
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
                                @if(empty($logged_emp_com))
                                <div class="col-lg-3">

                                    <div class="form-group">
                                        <label for="l30" class="col-md-12">Company Name</label>
                                        <select class="form-control" name="company_id">
                                            <option value="">Select Company</option>
                                            @if(isset($company))
                                            @foreach($company as $row)
                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                            @endforeach
                                            @endif

                                        </select>
                                    </div>
                                </div>
                                @else
                                <input type="hidden" name="company_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                @endif

                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="l30">Department</label>
                                        <select class="form-control" name="department_id">
                                            <option value="">Select Department</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-3">
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
                                <div class="col-lg-3">
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
                            <div class="table-responsive mb-5">
                                <table class="table table-hover" style="">
                                    <thead>

                                    </thead>


                                    <tbody>


                                    </tbody>
                                </table>
                            </div>
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">
                                <div id="grid" class="col-md-12"></div>
                            </div><!--
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Employee/Employeeinfo/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                            </script>-->



                            <script type="text/javascript">

//                                function KendoManualInitialized(link, data)
//                                {
//                                   
//                                    
//                                }

                                $(document).ready(function () {

                                    $("button[name='filter']").click(function () {

                                        var company = $("select[name='company_id']").val();
                                        if (company == '' || $("select[name='company_id']").length == 0) {
                                            company = 0;
                                        }
                                        var department_id = $("select[name='department_id']").val();
                                        var s_date = $("input[name='start_date']").val();
                                        var e_date = $("input[name='end_date']").val();

                                        $.post("<?= url('AdditionalOTSummary/Report') ?>", {'company': company, 'department': department_id, 'start_date': s_date, 'end_date': e_date, '_token': '<?= csrf_token() ?>'}, function (data) {
                                            var total = data.length;
                                            if (total != 0)
                                            {
                                                var strHead = '';
                                                strHead += '<tr>' +
                                                        ' <th>' + data[0].emp_codeH + '</th>';

                                                $.each(data, function (index, val) {
                                                    $.each(val.ddataH, function (index, val2) {
                                                        // console.log(val2)
                                                        $.each(val2, function (index, val3) {
                                                            // console.log(index + '->' + val3)
                                                            if (index == 'date') {
                                                                strHead += ' <th>' + val3 + '</th>';
                                                                // console.log(index + '->' + val3)
                                                                //console.log(data[0].emp_codeH);
                                                            }
                                                        })
                                                    })
                                                    

                                                });
                                                
                                                strHead += ' <th>' + data[0].totalH + '</th>';
                                                strHead += '</tr>';

                                                $("thead").html(strHead);
                                                
                                                
                                                

                                                var strtbody = '';
                                                var row_i = 1;
                                                $.each(data, function (index, val) {
//                                                    console.log(val.ddata[0].date);

                                                    if (val.emp_code)
                                                    {
                                                        strtbody += '<tr>' +
                                                                ' <td>' + val.emp_code + '</td>';
                                                        $.each(val.ddata, function (index, val2) {

                                                            $.each(val2, function (index, val3) {

                                                                if (index == 'total_ot') {
                                                                    strtbody += ' <td>' + val3 + '</td>';
                                                                    //  console.log(index + '->' + val3)
                                                                }
                                                            })
                                                        });
                                                        strtbody += ' <td>' + val.totalot + '</td>';
                                                        strtbody += '</tr>';

                                                        row_i += 1;
                                                    }

                                                });

                                                // console.log("Data Found");
                                                $("tbody").html(strtbody);
//                                               
                                            }
                                            else
                                            {
                                                $(".tbody").html('0 Record Found');
                                            }

                                        });

                                    });

                                    $("#export_excel").click(function () {

                                        var company = $("select[name='company_id']").val();
                                        if (company == '' || $("select[name='company_id']").length == 0) {
                                            company = 0;
                                        }
                                        var department = $("select[name='department_id']").val();
                                        var start_date = $("input[name='start_date']").val();
                                        var end_date = $("input[name='end_date']").val();
                                        // alert(company);


                                        var link = "<?= url('/') ?>/AdditionalOTSummary/Export/Excel/" + company + "/" + department + "/" + start_date + "/" + end_date;
                                        window.location.href = link;

                                    });

                                    $("#export_pdf").click(function () {

                                        var company = $("select[name='company_id']").val();
                                        if (company == '' || $("select[name='company_id']").length == 0) {
                                            company = 0;
                                        }
                                        var department = $("select[name='department_id']").val();
                                        var start_date = $("input[name='start_date']").val();
                                        var end_date = $("input[name='end_date']").val();
                                        var link = "<?= url('/') ?>/AdditionalOTSummary/Export/Pdf/" + company + "/" + department + "/" + start_date + "/" + end_date;
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
@include('ajax_include.company_wise_department')
@endsection
