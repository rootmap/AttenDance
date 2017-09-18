<?php
if (isset($data)) {
    $pageinfo = array("Edit Shift Swap Settings", "Edit Shift Swap  Record", "", "SUL");
} else {
    $pageinfo = array("Add New Shift Swap Settings", "Add New Shift Swap Record", "", "SUL");
}
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
                        <strong>{{$pageinfo[0]}}</strong>
                        <!--<small class="text-muted">All cards are draggable and sortable!</small>-->
                    </h5>
                    <div class="error"></div>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                           

                            <form name="ShiftSwap" action="{{url('Settings/ShiftSwap/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">

                                    <div class=" col-lg-5">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">Select Company</label>
                                            <select class="form-control" id="ExCompany" name="company_id">
                                                <option value="">Select Company</option>
                                                @foreach($company as $row)
                                                <option value="{{$row->id}}">{{$row->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-2"></div>
                                    <div class="col-lg-5"></div>
                                    <div class="card col-lg-5">
                                        <h5 class="text-center mt-4">Select Properties to Swap</h5>
                                        <div class="card-block form-actions">

                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <label for="l30">Start Day<span class="after" style="color:#EF5F5F">*</span></label>
                                                    <label class="input-group datepicker-only-init">
                                                        <input type="text" name="fstart_date" class="form-control required"   placeholder="Type Start Day"/>
                                                        <span class="input-group-addon" style="">
                                                            <i class="icmn-calendar"></i>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <label for="l30">End Day<span class="after" style="color:#EF5F5F">*</span></label>
                                                    <label class="input-group datepicker-only-init">
                                                        <input type="text" name="fend_date" class="form-control required"  placeholder="Type End Day"/>
                                                        <span class="input-group-addon" style="">
                                                            <i class="icmn-calendar"></i>
                                                        </span>
                                                    </label>
                                                </div>

                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <label for="l30">Shift</label>
                                                        <select class="form-control" id="shift_id" name="shift_id">
                                                            <option value="">Select Shift</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-4 col-lg-12">
                                                    <button type="reset"  class="btn btn-primary pull-left">Clear All</button>
                                                    <a href="javascript:void(0)" class="btn btn-default pull-right view_emp">View Employee</a>
                                                </div>
                                                <div class="form-check col-lg-12">
                                                    <div class="form-group">
                                                        <label>
                                                            <input type="checkbox" value="1" id="AddselectAll" name="is_ot_applicable"> Select All</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="emp form-group">

                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>


                                    <div class="col-lg-2 text-center" style="margin-top: 12%;">
                                        <i class="fa fa-arrow-right fa-5x" aria-hidden="true"></i>

                                    </div>


                                    <div class="card col-lg-5">
                                        <h5 class="text-center mt-4">Select Destination Properties</h5>
                                        <div class="card-block form-actions">
                                            <!--<form name="ShiftSwap" action="{{url('Settings/ShiftSwap/Add')}}" method="post">-->
                                            <!--{{csrf_field()}}-->
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <label for="l30">Start Day<span class="after" style="color:#EF5F5F">*</span></label>
                                                    <label class="input-group datepicker-only-init">
                                                        <input type="text" name="start_date" class="form-control required"   placeholder="Type Start Day"/>
                                                        <span class="input-group-addon" style="">
                                                            <i class="icmn-calendar"></i>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="col-lg-6">
                                                    <label for="l30">End Day<span class="after" style="color:#EF5F5F">*</span></label>
                                                    <label class="input-group datepicker-only-init">
                                                        <input type="text" name="end_date" class="form-control required" id="end_date"  placeholder="Type End Day"/>
                                                        <span class="input-group-addon" style="">
                                                            <i class="icmn-calendar"></i>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <label for="l30">Shift</label>
                                                        <select class="form-control" id="Sshift_id" name="swapshift_id">
                                                            <option value="">Select Shift</option>
                                                        </select>
                                                    </div>
                                                </div>


                                                <div class="mb-4 col-lg-12">
                                                    <button  type="submit" class="btn btn-primary pull-left swapEmp">Swap Employee</button>
                                                    <a href="javascript:void(0)" class="btn btn-default pull-right view_emp2">View Employee</a>
                                                </div>
                                                <div class="col-lg-12 select_exist">
                                                </div>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                
                                <!--Vertical Form Ends Here-->


                        </div>
                    </div>
            </section>


        </div>
    </div>

</div>

@endsection
@section('extraFooter')

@include('include.coreKendo')
<?= MenuPageController::genarateKendoDatePicker(array("fstart_date", "start_date", "fend_date", "end_date")) ?>
@include('ajax_include.company_wise_shift')
@include('ajax_include.company_wise_shiftSwap')



<script>

    $(document).ready(function () {

        var Error;
        $(".view_emp").click(function () {

            var company_id = $("#ExCompany").val();

            var shift_id = $("#shift_id").val();
            var start_date = $("input[name='fstart_date']").val();
            var end_date = $("input[name='fend_date']").val();


            if (end_date == '') {
                var error = '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span>' +
                        '</button>' +
                        '<strong>Warning! </strong> Please Select End Date' +
                        '</div>';
                $(".error").html(error);
                $(".error").html(error);
            }
            if (start_date == '') {
                var error = '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span>' +
                        '</button>' +
                        '<strong>Warning! </strong> Please Select Start Date' +
                        '</div>';
                $(".error").html(error);
                $(".error").html(error);
            }
            if (shift_id == '') {
                var error = '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span>' +
                        '</button>' +
                        '<strong>Warning! </strong> Please Select Shift' +
                        '</div>';
                $(".error").html(error);

            }

            if (shift_id != '') {
                $.post("<?= url('Settings/ShiftSwap/FilterShiftWiseEmployee') ?>", {'company_id': company_id, 'shift_id': shift_id, 'start_date': start_date, 'end_date': end_date, '_token': '<?= csrf_token() ?>'}, function (data) {
                    var total = data.length;
                    if (total != 0)
                    {
                        var str = '';
						var i=1;
                        $.each(data, function (index, val) {

                            str += '<label><input type="checkbox" value="' + val.emp_code + '" id="' + val.emp_code + '" name="shiftassign[]">'+i+'. ' + val.name + ' --' + val.emp_code + '<br><label>';
							i++;
                        });
						
                        // console.log("Data Found");
                        $(".emp").html(str);
                    } else
                    {
                        $(".emp").html('0 Record Found');
                    }
                    //console.log(data);
                });
            }
        });

    });

    $(document).ready(function () {


        $(".view_emp2").click(function () {

            var company_id = $("#ExCompany").val();
            var shift_id = $("select[name='swapshift_id']").val();
            var start_date = $("input[name='start_date']").val();
            var end_date = $("input[name='end_date']").val();
            if (end_date == '') {
                var error = '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span>' +
                        '</button>' +
                        '<strong>Warning! </strong> Please Select End Date' +
                        '</div>';
                $(".error").html(error);
                $(".error").html(error);
            }
            if (start_date == '') {
                var error = '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span>' +
                        '</button>' +
                        '<strong>Warning! </strong> Please Select Start Date' +
                        '</div>';
                $(".error").html(error);
                $(".error").html(error);
            }
            if (shift_id == '') {
                var error = '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span>' +
                        '</button>' +
                        '<strong>Warning! </strong> Please Select Shift' +
                        '</div>';
                $(".error").html(error);

            }

            if (shift_id != '') {
                $.post("<?= url('Settings/ShiftSwap/FilterShiftWiseEmployee') ?>", {'company_id': company_id, 'shift_id': shift_id, 'start_date': start_date, 'end_date': end_date, '_token': '<?= csrf_token() ?>'}, function (data) {
                    var total = data.length;
                    var count = 1;
                    if (total != 0)
                    {

                        var str = '';
                        $.each(data, function (index, val) {
                            str += '<p  id="' + val.emp_code + '">' + count + '. ' + val.name + ' --' + val.emp_code + '</p>';
                            count++;
                        });

                        // console.log("Data Found");
                        $(".select_exist").html(str);
                    } else
                    {
                        $(".select_exist").html('0 Record Found');
                    }
                    //console.log(data);
                });
            }
        });

    });

//    $(document).ready(function () {
//
//
//        $(".swapEmp").click(function () {
//            $.post("<? //= url('Settings/Shift/Update')  ?>", function (data) {
//                var total = data.length;
//                if (total != 0)
//                {
//                    console.log(data);
////                        var str = '';
////                        $.each(data, function (index, val) {
////
////                            str += '<label><input type="checkbox" value="' + val.emp_code + '" id="' + val.emp_code + '" name="shiftassign[]">' + ' ' + val.name + ' --' + val.emp_code + '<br><label>';
////                        });
////                        // console.log("Data Found");
////                        $(".emp").html(str);
//                }
//                //console.log(data);
//            });
//
//        });
//
//    });

</script>

<script>
    $('#AddselectAll').click(function () {
        if (this.checked) {
            $(':checkbox').each(function () {
                this.checked = true;
            });
        } else {
            $(':checkbox').each(function () {
                this.checked = false;
            });
        }
    });
</script>
@endsection