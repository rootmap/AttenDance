<?php
if (isset($data)) {
    $pageinfo = array("Edit Leave Application Info", "Edit Leave Application Record", "", "SUL");
} else {
    $pageinfo = array("Leave Application Info", "Add Leave Application Record", "", "SUL");
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
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            @if(isset($data))
                            <form name="LeaveApplicationUpdate" id="LeaveApplicationUpdate" action="{{url('/Leave/LeaveApplication/Update/'.$data['id'])}}" method="post" enctype="multipart/form-data">

                                {{csrf_field()}}
                                <div class="row">
                                    <div class="col-lg-3"  style="display:none;">
                                        <div class="form-group">
                                            <label for="l30">Select Company</label>
                                            <select class="form-control"   id="company_id" name="company_id">
                                                <option value="">Select Company</option>
                                                @if(isset($company))
                                                @foreach($company as $crow)
                                                <option
                                                    @if($data['company_id']==$crow->id)
                                                    selected="selected"
                                                    @endif
                                                    value="{{$crow->id}}">{{$crow->name}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3"  style="display:none;">
                                        <div class="form-group">
                                            <label for="l30">Employee</label>
                                            <select class="form-control" id="emp_code" name="emp_code">
                                                <option value="">Select Employee</option>
                                                @if(isset($employee))
                                                @foreach($employee as $erow)
                                                <option
                                                    @if($data['emp_code']==$erow->emp_code)
                                                    selected="selected"
                                                    @endif
                                                    value="{{$erow->emp_code}}">{{$erow->first_name}} {{$erow->last_name}}</option>
                                                @endforeach
                                                @endif

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="130">Reason</label>
                                            <input id="leave_reason" name="leave_reason" type="text" class="form-control" value="{{$leave_comments->comment}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Leave Starts From</label>
                                            <label class="input-group datepicker-only-init">
                                                <input type="text" id="leave_starts" name="leave_starts" class="form-control required" value="{{$data->start_date}}"/>
                                                <span class="input-group-addon" style="">
                                                    <i class="icmn-calendar"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">End Day</label>
                                            <label class="input-group datepicker-only-init">
                                                <input type="text" id="leave_end" name="leave_end" class="form-control required" value="{{$data->end_date}}"/>
                                                <span class="input-group-addon" style="">
                                                    <i class="icmn-calendar"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-3" id="div_half_day" style="display:none;">
                                        <div class="form-group">
                                            <label for="130">&nbsp;</label><br>
                                            <label>
                                                <input onchange="calculateLeave();"
                                                       @if($data['is_half_day']==1)
                                                       checked="checked"
                                                       @endif
                                                       type="checkbox" value="1" id="half_day_leave" name="half_day_leave"> Apply For Half Day Leave</label>
                                        </div>
                                    </div>
                                    <div id="half_day" class="col-lg-3" style="display:none;">
                                        <div class="form-group">
                                            <label for="l30">Select Part of Day</label>
                                            <select class="form-control" name="day_part" id="day_part" onchange="calculateLeave();">
                                                <option value="">Select Half</option>
                                                <option
                                                    @if($data['half_day']=='1st Half')
                                                    selected="selected"
                                                    @endif value="1st Half">1st Half</option>
                                                <option
                                                    @if($data['half_day']=='2nd Half')
                                                    selected="selected"
                                                    @endif value="2nd Half">2nd Half</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="pb-2 pt-2" style="border-top: 1px solid #e4e9f0; display:block; visibility:visible; width:100%;"></div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Leave Type</label>
                                            <select class="form-control" id="leave_policy_id" name="leave_policy_id" onchange="calculateLeaveBalance();">
                                                <option value="">Select Leave Type</option>
                                                @if(isset($leave_policies))
                                                @foreach($leave_policies as $lrow)
                                                <option
                                                    @if($data['leave_policy_id']==$lrow->id)
                                                    selected="selected"
                                                    @endif
                                                    value="{{$lrow->id}}">{{$lrow->leave_title}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3" id="div_docs"  style="display:none;">
                                        <div class="form-group">
                                            <label for="l30">Upload Attatchment/Document</label>
                                            <br/>
                                            <input type="file" class="" id="leave_docs" name="leave_docs">
                                            <input type="hidden" name="doc_req_days" id="doc_req_days" value="">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Remaining Days</label>
                                            <input id="remaining_days" name="remaining_days" type="text" class="form-control" value="" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Total Days</label>
                                            <input type="hidden" id="ttldays" name="ttldays" value="">
                                            <input id="total_leave_days" name="total_leave_days" type="text" class="form-control" value="{{$data->total_days_applied}}" disabled="disabled">
                                        </div>
                                    </div>

                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
                            <form name="LeaveApplication" id="LeaveApplication" action="{{url('/Leave/LeaveApplication/Add')}}" method="post" enctype="multipart/form-data">
                                {{csrf_field()}}
                                <div class="row">
                                    @if(empty($logged_emp_com) || !isset($logged_emp_com) || $logged_emp_com =="Undefined")
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Company</label>
                                            <select class="form-control" id="company_id" name="company_id">
                                                <option selected="selected" value="">Select Company</option>
                                                @foreach($com as $row)
                                                <option value="{{$row->id}}">{{$row->name}}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    <script type="text/javascript">
                                        $(document).ready(function () {
                                            $("select[name='company_id']").change(function () {
                                                if ($(this).val() != '')
                                                {
                                                    $.post("<?= url('Settings/LeaveUserData/Get/Employees/Json') ?>", {'company_id': $(this).val(), '_token': '<?= csrf_token() ?>'}, function (data) {
                                                        var total = data.length;
                                                        if (total != 0)
                                                        {
                                                            var str = '';
                                                            str += '<option selected="selected" value="">Select Employee</option>';
                                                            $.each(data, function (index, val) {
                                                                //console.log(index,val);
                                                                //console.log(val.year);
                                                                str += '<option value="' + val.emp_code + '">' + val.emp_name + '</option>';
                                                            });
                                                            //console.log("Data Found");
                                                            $("select[name='emp_code']").html(str);
                                                        }
                                                        else
                                                        {
                                                            var str = '';
                                                            str += '<option selected="selected" value="">0 Record Found</option>';
                                                            $("select[name='emp_code']").html(str);
                                                            //console.log(data);
                                                        }
                                                        //console.log(data);
                                                    });
                                                }
                                            });
                                        });
                                    </script>
                                    @else
                                    <input type="hidden"  id="company_id" name="company_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                    <script type="text/javascript">
                                        $(document).ready(function () {
                                            var company_id = $("input[name='company_id']").val();

                                            $.post("<?= url('Settings/LeaveUserData/Get/Employees/Json') ?>", {'company_id': company_id, '_token': '<?= csrf_token() ?>'}, function (data) {
                                                var total = data.length;
                                                if (total != 0)
                                                {
                                                    var str = '';
                                                    str += '<option selected="selected" value="">Select Employee</option>';
                                                    $.each(data, function (index, val) {
                                                        //console.log(index,val);
                                                        //console.log(val.year);
                                                        str += '<option value="' + val.emp_code + '">' + val.emp_name + '</option>';
                                                    });
                                                    //console.log("Data Found");
                                                    $("select[name='emp_code']").html(str);
                                                }
                                                else
                                                {
                                                    var str = '';
                                                    str += '<option selected="selected" value="">0 Record Found</option>';
                                                    $("select[name='emp_code']").html(str);
                                                    //console.log(data);
                                                }
                                                //console.log(data);
                                            });
                                        });
                                    </script>
                                    @endif
                                    @if(empty($logged_emp_code) || !isset($logged_emp_code) || $logged_emp_code =="Undefined")
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Employee</label>
                                            <!-- <input type="hidden" id="employee_code" name="employee_code" value="{{$logged_emp_code}}"/> -->
                                            <select class="form-control" id="emp_code" name="emp_code" data-validation="[NOTEMPTY]">
                                                <option value="">Select Employee</option>
                                            </select>
                                        </div>
                                    </div>
                                    @else
                                    <input type="hidden"  id="emp_code" name="emp_code" value="{{$logged_emp_code}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                    @endif
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="130">Reason</label>
                                            <input id="leave_reason" name="leave_reason" type="text" class="form-control" placeholder="Type Reason For Leave Here" data-validation="[NOTEMPTY]">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <!-- <div class="form-group">
                                            <label for="l30">Leave Starts From</label><br>
                                            <input onchange="calculateLeave();" id="leave_starts" name="leave_starts" style="width:100%" type="text" class="form-control" value="" data-validation="[NOTEMPTY]">
                                        </div> -->
                                        <div class="form-group">
                                            <label for="l30">Leave Starts From</label>
                                            <label class="input-group datepicker-only-init">
                                                <input type="text" id="leave_starts" name="leave_starts" class="form-control required" placeholder="Type Start Day"/>
                                                <span class="input-group-addon" style="">
                                                    <i class="icmn-calendar"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <!-- <div class="form-group">
                                            <label for="l30">Leave Ends At</label><br>
                                            <input onchange="calculateLeave();" id="leave_end" name="leave_end" style="width:100%" type="text" class="form-control" value="" data-validation="[NOTEMPTY]">
                                        </div> -->
                                        <div class="form-group">
                                            <label for="l30">End Day</label>
                                            <label class="input-group datepicker-only-init">
                                                <input type="text" id="leave_end" name="leave_end" class="form-control required" placeholder="Type End Day"/>
                                                <span class="input-group-addon" style="">
                                                    <i class="icmn-calendar"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <!-- <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="130">&nbsp;</label><br>
                                            <label>
                                            <input type="checkbox" value="1" name="multiple_leave"> Apply For Multiple Leave Types</label>
                                        </div>
                                    </div> -->
                                    <div class="col-lg-3" id="div_half_day" style="display:none;">
                                        <div class="form-group">
                                            <label for="130">&nbsp;</label><br>
                                            <label>
                                                <input onchange="calculateLeave();" type="checkbox" value="1" id="half_day_leave" name="half_day_leave"> Apply For Half Day Leave</label>
                                        </div>
                                    </div>
                                    <div id="half_day" class="col-lg-3" style="display:none;">
                                        <div class="form-group">
                                            <label for="l30">Select Part of Day</label>
                                            <select class="form-control" name="day_part" id="day_part" onchange="calculateLeave();">
                                                <option value="">Select Half</option>
                                                <option value="1st Half">1st Half</option>
                                                <option value="2nd Half">2nd Half</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="pb-2 pt-2" style="border-top: 1px solid #e4e9f0; display:block; visibility:visible; width:100%;"></div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Leave Type</label>
                                            <select class="form-control" id="leave_policy_id" name="leave_policy_id" onchange="calculateLeaveBalance();" data-validation="[NOTEMPTY]"><!--onchange="javascript:calculateLeave();"-->
                                                <option value="">Select Leave Type</option>
                                                @if(isset($leave_policies))
                                                @foreach($leave_policies as $row)
                                                <option value="{{$row->id}}">{{$row->leave_title}}</option>
                                                @endforeach
                                                @endif

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3" id="div_docs"  style="display:none;">
                                        <div class="form-group">
                                            <label for="l30">Upload Attatchment/Document</label>
                                            <br/>
                                            <input type="file" class="" id="leave_docs" name="leave_docs">
                                            <input type="hidden" name="doc_req_days" id="doc_req_days" value="">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Total Applied Leave Days</label>
                                            <input type="hidden" id="ttldays" name="ttldays" value="">
                                            <input id="total_leave_days" name="total_leave_days" type="text" class="form-control" value="" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Remaining Days</label>
                                            <input id="remaining_days" name="remaining_days" type="text" class="form-control" value="" disabled="disabled">
                                        </div>
                                    </div>
                                </div>


                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Apply</button>
                                    <!-- <button type="button" id="apply-btn" class="btn btn-primary">Apply</button> -->
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @endif
                            <!--Vertical Form Ends Here-->
                        </div>
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <section class="card">
                                <div class="card-header bg-faded">
                                    <span class="cat__core__title">
                                        <h5 class="font-weight-bold"><i class="fa fa-info-circle text-primary" aria-hidden="true"></i> This Applicant's Leave Summary And History:</h5>
                                    </span>
                                </div>
                                <div class="card-block">
                                    <!-- kendo table code start from here-->
                                    <div class="row">
                                        <!-- <div id="grid" class="col-md-12"></div> -->
                                        <div id="example" class="k-content">
                                            <div id="tabstrip">
                                                <ul>
                                                    <li class="k-state-active">
                                                        Summary
                                                    </li>
                                                    <li>
                                                        History
                                                    </li>
                                                </ul>

                                                <div>
                                                    <div class="weather">
                                                        <div id="example">
                                                            <div id="grid_summary"></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div>
                                                    <div class="weather">
                                                        <div id="example">
                                                            <div id="grid_history"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--Application History and Summery-->
                                    </div>
                                </div>
                            </section>
                            <!-- <div class="row">
                                <div id="grid" class="col-md-12"></div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/LeaveUserData/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script> -->
                            <script type="text/javascript">
                                //Company and employee on change reset all fields
                                $("select[name='company_id']").change(function () {
                                    resetAllFields();
                                });
                                $("select[name='emp_code']").change(function () {
                                    resetAllFields();
                                });
                                function resetAllFields() {
                                    $('#leave_policy_id').val("");
                                    $('#remaining_days').val("");
                                    $('#leave_reason').val("");
                                    $('#leave_starts').val("");
                                    $('#leave_end').val("");
                                    $('#day_part').val("");
                                    $('#ttldays').val("");
                                    $('#total_leave_days').val("");
                                    $('#doc_req_days').val("");
                                }


                                //For Calculating Leave With Two Dates
                                function calculateLeave() {
                                    $('#leave_policy_id').val("");
                                    $('#remaining_days').val("");
                                    //setInterval(function () {

                                    var leave_starts = $('#leave_starts').val();
                                    var leave_end = $('#leave_end').val();

                                    if (leave_starts != "" && leave_end != "") {
                                        var defday = 1;
                                        //calculate diffrence between two Dates
                                        var date1 = new Date(leave_starts);
                                        var date2 = new Date(leave_end);
                                        var timeDiff = Math.abs(date2.getTime() - date1.getTime());
                                        var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));

                                        if (diffDays >= 1) {
                                            //alert(diffDays);
                                            defday = diffDays + 1;
                                            $("#total_leave_days").val(defday); //visible field
                                            $("#ttldays").val(defday); //hidden field

                                            $('#half_day_leave').prop('checked', false);
                                            $("#div_half_day").hide();
                                            $("#day_part option:first").attr('selected', 'selected');
                                            $("#half_day").hide();
                                        } else {

                                            $("#div_half_day").show();
                                            if ($('#half_day_leave').is(':checked')) {
                                                defday = diffDays + 0.5;

                                                $("#half_day").show(500);
                                                $("#total_leave_days").val(defday); //visible field
                                                $("#ttldays").val(defday); //hidden field

                                            } else {
                                                defday = diffDays + 1;
                                                $("#half_day").hide(500);
                                                $("#total_leave_days").val(defday); //visible field
                                                $("#ttldays").val(defday); //hidden field

                                            }
                                        }
                                        //alert(defday);
                                    }
                                    //}, 1000);

                                }

                                //Ends
                                //For Calculating Leave Balance with Leave Type and Employee
                                function calculateLeaveBalance() {
                                    var company_id = $("select[name='company_id']").val();
                                    var employee_id = $('#emp_code').val();
                                    var leave_policy_id = $("select[name='leave_policy_id']").val();
                                    var leave_starts = $('#leave_starts').val();
                                    var leave_end = $('#leave_end').val();
                                    var day_part = $('#day_part').val();
                                    var totalLeaveDays = $('#ttldays').val();
                                    var leaveReason = $('#leave_reason').val();
                                    var doc_req_days = $('#doc_req_days').val();

                                    if (company_id == "") {
                                        //alert('Please Select Company');
                                        swal({
                                            title: "Field(s) Empty!",
                                            text: "Please Select Company",
                                            type: "warning"
                                        },
                                        function () {
                                            $('#company_id').focus();
                                        });
                                    } else if (employee_id == "") {
                                        //alert('Please Select Employee');
                                        swal({
                                            title: "Field(s) Empty!",
                                            text: "Please Select Employee",
                                            type: "warning"
                                        },
                                        function () {
                                            $('#emp_code').focus();
                                        });
                                    } else if (leaveReason == "") {
                                        //alert('Please Input Leave Reason');
                                        swal({
                                            title: "Field(s) Empty!",
                                            text: "Please Input Leave Reason",
                                            type: "warning"
                                        },
                                        function () {
                                            $('#leave_reason').focus();
                                        });
                                        $('#leave_policy_id').val("");
                                    } else if (leave_starts == "") {
                                        //alert('Please Select Leave Start Date');
                                        swal({
                                            title: "Field(s) Empty!",
                                            text: "Please Select Leave Start Date",
                                            type: "warning"
                                        },
                                        function () {
                                            $('#company_id').focus();
                                        });
                                    } else if (leave_end == "") {
                                        //alert('Please Select Leave End Date');
                                        swal({
                                            title: "Field(s) Empty!",
                                            text: "Please Select Leave End Date",
                                            type: "warning"
                                        },
                                        function () {
                                            $('#company_id').focus();
                                        });
                                    } else if ($('#half_day_leave').is(':checked') && day_part == "") {
                                        //alert('Please Select Day Part');
                                        swal({
                                            title: "Field(s) Empty!",
                                            text: "Please Select Day Part",
                                            type: "warning"
                                        },
                                        function () {
                                            $('#company_id').focus();
                                        });
                                    } else {
                                        $.post("<?= url('/Leave/LeaveApplication/Get/LeaveBalance') ?>", {'company_id': company_id, 'employee_code': employee_id, 'leave_policy_id': leave_policy_id, 'leave_starts': leave_starts, 'leave_end': leave_end, '_token': '<?= csrf_token() ?>'}, function (data) {
                                            var total = data.length;
                                            if (total != 0)
                                            {
                                                //alert('not zero');
                                                var remaining_days = '';
                                                var is_document_upload = '';

//                                                ttl_appl_days = data.ttl_appl_days;
//                                                ttl_remaining_days = data.remaining_days;
//                                                is_document_upload = data.is_document_upload;
//                                                document_upload_after_days = data.document_upload_after_days;

                                                if ($('#half_day_leave').is(':checked')) {
                                                    ttl_appl_days = data.ttl_appl_days;
                                                    ttl_remaining_days = (data.remaining_days - 0) + 1;
                                                    is_document_upload = data.is_document_upload;
                                                    document_upload_after_days = data.document_upload_after_days;
                                                    if (totalLeaveDays > ttl_remaining_days) {
                                                        swal({
                                                            title: "Sorry!!!",
                                                            text: "You Have Insufficient Leave Balance For This Application. Please Contact With HR",
                                                            type: "warning"
                                                        });
                                                        //setTimeout(function(){ window.location.reload(); }, 5000);
                                                    } else {
                                                        var remaining_now = (ttl_remaining_days - 0) - (totalLeaveDays - 0)
                                                        //alert(remaining_now);
                                                        //alert(is_document_upload);
                                                        $('#remaining_days').val(remaining_now);
                                                        if (is_document_upload == 1 && totalLeaveDays > document_upload_after_days) {
                                                            $("#div_docs").show(500);

                                                            swal({
                                                                title: "Leave Document Required!",
                                                                text: "Please Upload Document For Your Leave Application",
                                                                type: "warning"
                                                            });
                                                            //$("#doc_req_days").val(document_upload_after_days);

                                                            //$("#leave_docs").addClass("form-control");
                                                            //$("#leave_docs").addClass("required");
                                                        } else {
                                                            $("#div_docs").hide(500);
                                                            $("#leave_docs").removeClass("required");
                                                        }
                                                    }
                                                } else {
                                                    ttl_appl_days = data.ttl_appl_days;
                                                    ttl_remaining_days = data.remaining_days;
                                                    is_document_upload = data.is_document_upload;
                                                    document_upload_after_days = data.document_upload_after_days;
                                                    
                                                    if (is_document_upload == 1 && totalLeaveDays > document_upload_after_days) {
                                                        $("#div_docs").show(500);

                                                        swal({
                                                            title: "Leave Document Required!",
                                                            text: "Please Upload Document For Your Leave Application",
                                                            type: "warning"
                                                        });
                                                        //$("#doc_req_days").val(document_upload_after_days);

                                                        //$("#leave_docs").addClass("form-control");
                                                        //$("#leave_docs").addClass("required");
                                                    } else {
                                                        $("#div_docs").hide(500);
                                                        $("#leave_docs").removeClass("required");
                                                    }
                                                    $("#total_leave_days").val(ttl_appl_days);
                                                    $("#ttldays").val(ttl_appl_days);
                                                    $('#remaining_days').val(ttl_remaining_days);
                                                }
                                            }
                                            else
                                            {
                                                //alert('zero');
                                                swal({
                                                    title: "Sorry!!!",
                                                    text: "You Have No Leave Balance Remaining. Please Contact With HR",
                                                    type: "warning"
                                                });
                                                //setTimeout(function(){ window.location.reload(); }, 5000);
                                                resetAllFields();
                                            }
                                        });
                                    } //end else
                                }
                                //Ends

                                //For Leave Summary and History

                                $(document).ready(function () {
                                    var emp_code = $("#emp_code").val();
                                    //for summary kendo grid
                                    var dataSource = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?= url('/') ?>/Leave/LeaveApplication/LeaveApplicationSummary/Json/" + emp_code,
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
                                                    leave_title: {type: "string"},
                                                    total_days: {type: "string"},
                                                    availed_days: {type: "string"},
                                                    remaining_days: {type: "string"},
                                                    incash_balance: {type: "string"}
                                                }
                                            }
                                        },
                                        pageSize: 10,
                                        serverPaging: false,
                                        serverFiltering: false,
                                        serverSorting: false
                                    });
                                    $("#grid_summary").kendoGrid({
                                        dataBound: gridDataBound,
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
                                            {field: "leave_title", title: "Leave Title", width: "80px"},
                                            {field: "total_days", title: "Total Days", width: "80px", filterable: false},
                                            {field: "availed_days", title: "Availed Days", width: "80px", filterable: false},
                                            {field: "remaining_days", title: "Remaining Days", width: "80px", filterable: false},
                                            {field: "incash_balance", title: "Incash Balance", width: "80px", filterable: false}
                                        ],
                                    });

                                    //For History Kendo grid
                                    var dataSource2 = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?= url('/') ?>/Leave/LeaveApplication/LeaveApplicationHistory/Json/" + emp_code,
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
                                                    leave_title: {type: "string"},
                                                    start_date: {type: "string"},
                                                    end_date: {type: "string"},
                                                    total_days_applied: {type: "string"},
                                                    is_half_day: {type: "boolean"},
                                                    half_day: {type: "string"},
                                                    leave_status: {type: "string"}
                                                }
                                            }
                                        },
                                        pageSize: 10,
                                        serverPaging: false,
                                        serverFiltering: false,
                                        serverSorting: false
                                    });
                                    $("#grid_history").kendoGrid({
                                        dataBound: gridDataBound,
                                        dataSource: dataSource2,
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
                                            {field: "leave_title", title: "Leave Title", width: "80px"},
                                            {field: "start_date", title: "Start Date", width: "80px"},
                                            {field: "end_date", title: "End Date", width: "80px"},
                                            {field: "total_days_applied", title: "Total Days", width: "80px", filterable: false},
                                            {field: "is_half_day", title: "Is Half Day Leave", width: "80px", filterable: false},
                                            {field: "half_day", title: "Day Part", width: "80px", filterable: false},
                                            {field: "leave_status", title: "Leave Status", width: "80px", filterable: false}
                                        ],
                                    });
                                });

                                //Ends For Leave Summary and History

                                //data inserts for leave
                                // $('#apply-btn').click(function () {
                                //   var company_id=$("select[name='company_id']").val();
                                //   var employee_id=$("select[name='emp_code']").val();
                                //   var leaveReason = $('#leave_reason').val();
                                //   var leave_policy_id=$("select[name='leave_policy_id']").val();
                                //   var leave_starts=$('#leave_starts').val();
                                //   var leave_end=$('#leave_end').val();
                                //   var half_day_leave=$('#half_day_leave').val();
                                //   var day_part=$('#day_part').val();
                                //   var totalLeaveDays=$('#ttldays').val();
                                //
                                //
                                //   if (company_id=="") {
                                //     swal({
                                //          title: "Field(s) Empty!",
                                //          text: "Please Select Company",
                                //          type: "warning"
                                //          });
                                //   } else if (employee_id==""){
                                //     swal({
                                //         title: "Field(s) Empty!",
                                //         text: "Please Select Employee",
                                //         type: "warning"
                                //     });
                                //   } else if (leaveReason==""){
                                //     swal({
                                //         title: "Field(s) Empty!",
                                //         text: "Please Input Leave Reason",
                                //         type: "warning"
                                //     });
                                //   } else if (leave_policy_id==""){
                                //     swal({
                                //         title: "Field(s) Empty!",
                                //         text: "Please Input Leave Policy",
                                //         type: "warning"
                                //     });
                                //   } else if (leave_starts==""){
                                //     swal({
                                //         title: "Field(s) Empty!",
                                //         text: "Please Select Leave Start Date",
                                //         type: "warning"
                                //     });
                                //   } else if (leave_end==""){
                                //     swal({
                                //         title: "Field(s) Empty!",
                                //         text: "Please Select Leave End Date",
                                //         type: "warning"
                                //     });
                                //   } else if ($('#half_day_leave').is(':checked') && day_part==""){
                                //     swal({
                                //         title: "Field(s) Empty!",
                                //         text: "Please Select Day Part",
                                //         type: "warning"
                                //     });
                                //   } else {
                                //     // $.post("<? //=url('/Leave/LeaveApplication/Get/LeaveBalance') ?>",
                                //     // {'company_id':company_id,
                                //     // 'employee_code':employee_id,
                                //     // 'leave_policy_id':leave_policy_id,
                                //     // 'leave_starts':leave_starts,
                                //     // 'leave_end':leave_end,
                                //     // '_token':'<?= csrf_token() ?>'},function(data){
                                //     //
                                //     // });
                                //     NProgress.start();
                                //     $.ajax({
                                //         type: "POST",
                                //         dataType: "json",
                                //         url: "/Leave/LeaveApplication/Approve",
                                //         data: {'company_id':company_id,
                                //                 'emp_code':employee_id,
                                //                 'leave_reason':leaveReason,
                                //                 'leave_policy_id':leave_policy_id,
                                //                 'leave_starts':leave_starts,
                                //                 'leave_end':leave_end,
                                //                 'half_day_leave':half_day_leave,
                                //                 'day_part':day_part,
                                //                 'ttldays':totalLeaveDays,
                                //                 '_token':'<?= csrf_token() ?>'},
                                //         success: function (result) {
                                //           NProgress.done();
                                //           swal({
                                //               title: "Submitted!",
                                //               text: "You Have Successfully Submitted This Leave Application",
                                //               type: "success",
                                //               confirmButtonClass: "btn-success"
                                //           });
                                //           //setTimeout(function(){ window.location.reload(); }, 1000);
                                //         }
                                //       });
                                //   }
                                //
                                // });

                                // function finalValidate(){
                                //   if(doc_req_days!=""){
                                //     swal({
                                //         title: "Field(s) Empty!",
                                //         text: "Please Upload Document For Your Leave Application",
                                //         type: "warning"
                                //     });
                                //   } else {
                                //     $("#LeaveApplication").submit();
                                //   }
                                // }
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

@section('extraHeader')
<link rel="stylesheet" type="text/css" href="{{url('vendors/nprogress/nprogress.css')}}">
@endsection
@section('extraFooter')
<script src="{{url('vendors/nprogress/nprogress.js')}}"></script>
<script src="{{url('vendors/jquery-validation/dist/jquery.validate.js')}}"></script>
@include('include.coreKendo')
<style type="text/css" media="screen">
    span.k-edit,span.k-delete{
        margin-top: -3px !important;
    }

    span.k-i-arrow-s{
        margin-top: -5px !important;
    }
</style>
<?= MenuPageController::genarateKendoDatePicker(array("leave_starts","leave_end")) ?>

<script> $(document).ready(function () {

                                    $('input[name=leave_starts]').datetimepicker({
                                        format: 'YYYY-MM-DD',
                                        icons: {
                                            time: 'fa fa-clock-o',
                                            date: 'fa fa-calendar',
                                            up: 'fa fa-chevron-up',
                                            down: 'fa fa-chevron-down',
                                            previous: 'fa fa-chevron-left',
                                            next: 'fa fa-chevron-right',
                                            today: 'fa fa-screenshot',
                                            clear: 'fa fa-trash',
                                            close: 'fa fa-remove'
                                        }
                                    }).on('dp.change', function () {
//console.log($(this).val());
                                        calculateLeave();
                                    });

                                });</script>  <script> $(document).ready(function () {

                                        $('input[name=leave_end]').datetimepicker({
                                            format: 'YYYY-MM-DD',
                                            icons: {
                                                time: 'fa fa-clock-o',
                                                date: 'fa fa-calendar',
                                                up: 'fa fa-chevron-up',
                                                down: 'fa fa-chevron-down',
                                                previous: 'fa fa-chevron-left',
                                                next: 'fa fa-chevron-right',
                                                today: 'fa fa-screenshot',
                                                clear: 'fa fa-trash',
                                                close: 'fa fa-remove'
                                            }
                                        }).on('dp.change', function () {
//console.log($(this).val());
                                            calculateLeave();
                                        });

                                    });</script>
@endsection
