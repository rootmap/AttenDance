<?php
$pageinfo = array("Yearly Leave Register Report Summary", "Yearly Leave Report Summary", "", "SUL", "Filter Yearly Leave Register Report Summary", "Genarate Report");
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
                                            <option <?php if ($data['company_id'] == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
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
                                        <label for="l30">Year</label>
                                        <select class="form-control" name="year">
                                            <option value="">Select Year</option>
                                            @if(isset($year))
                                                @foreach($year as $row)
                                                <option value="{{$row->year}}">{{$row->year}}</option>
                                                @endforeach
                                            @endif

                                        </select>
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
                        <a style="display:none !important;" href="javascript:void(0)"  id="export_pdf" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>
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
                                <table class="table table-hover table-bordered" style="">
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
//                                    $.ajax({
//                                        method: "POST",
//                                        url: "<?= url('"+link+"') ?>",
//                                        data: data
//                                    })
//                                            .done(function (obj) {
//                                                console.log(obj);
//                                            });
//
//
//                                }

                                $(document).ready(function () {

                                    $("button[name='filter']").click(function () {

                                        var company = $("select[name='company_id']").val();
                                        if (company == '' || $("select[name='company_id']").length == 0) {
                                            company = $('#company_id').val();
                                        }
                                        var year = $("select[name='year']").val();


                                        $.post("<?= url('/LeaveRegister/Report') ?>", {'company': company, 'year': year, '_token': '<?= csrf_token() ?>'}, function (data) {
                                            var total = data.length;
                                            if (total != 0)
                                            {
                                                var strHead = '';
                                                strHead += '<tr class="table-info">' +
                                                        ' <th rowspan="2" class="text-center align-middle">Employee Code</th>'+
                                                        ' <th rowspan="2" class="text-center align-middle">Name</th>'+
                                                        ' <th rowspan="2" class="text-center align-middle">Designation</th>'+
                                                        ' <th rowspan="2" class="text-center align-middle">Department</th>'+
                                                        ' <th rowspan="2" class="text-center align-middle">Staff Grade</th>';

                                                $.each(data, function (index, val) {
                                                  var ldata = val.leave_data;
                                                  for(var i=0;i<ldata.length;i++){
                                                     if(i>0){
                                                        return false;
                                                     } else {
                                                       $.each(val.leave_data, function (index, val2) {
                                                         //console.log(val2.leave_title);
                                                           strHead += '<th colspan="3"  class="text-center align-middle">' + val2.leave_title + '</th>';
                                                       })
                                                     }
                                                  }


                                                    strHead += '</tr>';

                                                });

                                                strHead += '<tr class="table-info">';
                                                $.each(data, function (index, vals) {
                                                  var ldatas = vals.leave_data;
                                                  for(var i=0;i<ldatas.length;i++){
                                                     if(i>0){
                                                        return false;
                                                     } else {
                                                       $.each(vals.leave_data, function (index, vals2) {
                                                         //console.log(val2.leave_title);
                                                           strHead += '<td class="font-weight-bold">Total</td><td class="font-weight-bold">Availed</td><td class="font-weight-bold">Balance</td>';
                                                       })
                                                     }
                                                  }


                                                    strHead += '</tr>';

                                                });




                                                $("thead").html(strHead);

                                                var strtbody = '';
                                                var row_i = 1;
                                                $.each(data, function (index, val) {
                                                    // console.log(val.ddata[0].date);
                                                    //<td>Total</td><td>Availed</td><td>Balance</td>
                                                    if(val.emp_code)
                                                    {
                                                    strtbody += '<tr>' +
                                                            '<td>' + val.emp_code + '</td>' +
                                                            '<td>' + val.emp_name + '</td>' +
                                                            '<td>' + val.emp_designation + '</td>' +
                                                            '<td>' + val.emp_department + '</td>' +
                                                            '<td>' + val.emp_staff_grade + '</td>';


                                                    $.each(val.leave_data, function (index, val2) {
                                                      strtbody += '<td>' + val2.total_days + '</td>' +
                                                                  '<td>' + val2.availed_days + '</td>' +
                                                                  '<td>' + val2.remaining_days + '</td>';
                                                    })
                                                    strtbody += '</tr>';
                                                    //console.log(val.emp_code);
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

                                        var company = $('#company_id').val();
                                        if (company == '' || $('#company_id').length == 0) {
                                            company = 0;
                                        }
                                        var year = $("select[name='year']").val();

                                        // alert(company);


                                        var link = "<?= url('/') ?>/LeaveRegister/Export/Excel/" + company + "/" + year;
                                        window.location.href = link;

                                    });

                                    $("#export_pdf").click(function () {

                                      var company = $('#company_id').val();
                                      if (company == '' || $('#company_id').length == 0) {
                                          company = 0;
                                      }
                                      var year = $("select[name='year']").val();

                                      var link = "<?= url('/') ?>/LeaveRegister/Export/Pdf/" + company + "/" + year;
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
