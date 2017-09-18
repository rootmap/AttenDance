<?php
if (isset($data)) {
    $pageinfo = array("Edit Shift Assign Settings", "Edit Shift Assign  Record", "", "SUL");
} else {
    $pageinfo = array("Add New Shift Assign Settings", "Add New Shift Assign Record", "", "SUL");
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

                <div class="row">
                    <div class="col-xl-12">
                        <!--Vertical Form Starts Here-->
                        @if(isset($data))
                        <form name="Role" action="{{url('Settings/Shift/Update/'.$data['id'])}}" method="post">
                            {{csrf_field()}}
                            <div class="row">


                            </div>


                            <div class="form-actions">
                                <button type="submit"  class="btn btn-primary">Update</button>
                                <button type="reset" class="btn btn-default">Cancel</button>
                            </div>
                        </form>
                        @else


                        <div class="row">
                            <div class="col-lg-12">
                                <form name="ShiftAssign" action="{{url('Settings/ShiftAssign/Add')}}" method="post">
                                    {{csrf_field()}}
                                    <div class="card">
                                        <div class="card-header">
                                            Add Employee Information
                                        </div>
                                        <div class="card-block">
                                            <div class="row">
                                                
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Company Name</label>
                                                        <select class="form-control" id="company" name="company_id">
                                                            <option selected="selected" value="">Select Company</option>
                                                            @if(isset($company))
																@foreach($company as $row)
																<option value="{{$row->id}}">{{$row->name}}</option>
																@endforeach
                                                            @endif

                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Department</label>
                                                        <select class="form-control" name="department_id">
                                                            <option value="">Select Department</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Section</label>
                                                        <select class="form-control" name="section_id">
                                                            <option value="">Select Section</option>
                                                        </select>
                                                    </div>
                                                </div>
												
                                                <?php /*<div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Designation</label>
                                                        <select class="form-control" name="designation_id">
                                                            <option value="">Select Designation</option>
                                                        </select>
                                                    </div>
                                                </div>*/ ?>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Shift</label>
                                                        <select class="form-control" name="shift_id">
                                                            <option value="">Select Shift</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 form-group">
                                                    <label for="l30">Start Day</label>
                                                    <label class="input-group datepicker-only-init">
                                                        <input type="text" name="start_date" class="form-control required" id="start_time"  placeholder="Type Start Day"/>
                                                        <span class="input-group-addon" style="">
                                                            <i class="icmn-calendar"></i>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="col-lg-4 form-group">
                                                    <label for="l30">End Day</label>
                                                    <label class="input-group datepicker-only-init">
                                                        <input type="text" name="end_date" class="form-control required" id="end_time"  placeholder="Type End Day"/>
                                                        <span class="input-group-addon" style="">
                                                            <i class="icmn-calendar"></i>
                                                        </span>
                                                    </label>
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
                                                <div class="form-actions col-lg-12">
                                                    <button type="submit"  name="assignToshift"  class="btn btn-primary">Add</button>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="text-warning">Existing Employee Information 
											<span class="pull-right">
												<a href="javascript:void(0)" id="viewExEmp_excel" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
												<a href="javascript:void(0)"  id="viewExEmp_pdf" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>
											</span>
										</h4>
                                    </div>
                                    <div class="card-block">
                                        <div class="row">
                                            <div class="col-lg-2">
                                                <div class="form-group">
                                                    <label for="l30">Company Name</label>
                                                    <select class="form-control" id="ExCompany" name="company_id">
                                                        <option selected="selected" value="">Select Company</option>
                                                        @if(isset($company))
                                                        @foreach($company as $row)
                                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                                        @endforeach
                                                        @endif

                                                    </select>
                                                </div>
                                            </div>
                                            
											<div class="col-lg-2 form-group">
                                                <label for="l30">Emp Code</label>
                                                <label class="input-group">
                                                    <input type="text" name="emp_code_check" class="form-control required" id="emp_code_check"  placeholder="Type Card No./ Emp Code"/>
                                                    <span class="input-group-addon" style="">
                                                        <i class="icmn-user"></i>
                                                    </span>
                                                </label>
                                            </div>
											

                                            <div class="col-lg-2 form-group">
                                                <label for="l30">Start Day</label>
                                                <label class="input-group datepicker-only-init">
                                                    <input type="text" name="ex_start_date" class="form-control required" id="start_time"  placeholder="Type Start Day"/>
                                                    <span class="input-group-addon" style="">
                                                        <i class="icmn-calendar"></i>
                                                    </span>
                                                </label>
                                            </div>
											
                                            <div class="col-lg-2 form-group">
                                                <label for="l30">End Day</label>
                                                <label class="input-group datepicker-only-init">
                                                    <input type="text" name="ex_end_date" class="form-control required" id="end_time"  placeholder="Type End Day"/>
                                                    <span class="input-group-addon" style="">
                                                        <i class="icmn-calendar"></i>
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="col-lg-2">
                                                <div class="form-group">
                                                    <label for="l30">Shift</label>
                                                    <select class="form-control" id="shift_id" name="shift_id">
                                                        <option value="">Select Shift</option>
                                                    </select>
                                                </div>
                                            </div>
											<div class="col-lg-2">
                                                <div class="form-group">
                                                    <label for="l30">&nbsp;</label>
                                                    <button style="margin-top:25px;" type="button" id="viewExEmp"  class="btn btn-primary">View Employees</button>
                                                </div>
                                            </div>

                                            <div class="col-lg-12">
                                                <div class="emp2 form-group">

                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        <!--Vertical Form Ends Here-->


                    </div>
                </div>
            </section>


        </div>
    </div>

</div>

@endsection
@section('extraFooter')


<?= MenuPageController::genarateKendoDatePicker(array("start_date", "end_date", "ex_start_date", "ex_end_date")) ?>

<!--Added For Loading Company Wise Leave Type and Year-->
<script type="text/javascript">
    $(document).ready(function () {

    });
</script>
<!--Ends Loading Company Wise Leave Type and Year-->
<script>
    $(document).ready(function () {


        $("#company,select[name='department_id'],select[name='section_id']").change(function () {

            var company_id = $("#company").val();
            var department_id = $("select[name='department_id']").val();
            var section_id = $("select[name='section_id']").val();
            if (company_id != '') {
                $.post("<?= url('Settings/ShiftAssign/FilterDataList') ?>", {'company_id': company_id, 'department_id': department_id, 'section_id': section_id,'_token': '<?= csrf_token() ?>'}, function (data) {
                    var total = data.length;
                    if (total != 0)
                    {
                        var str = '';
                        $.each(data, function (index, val) {

                            str += '<label><input type="checkbox" value="' + val.emp_code + '" id="' + val.emp_code + '" name="shiftassign[]">' + ' ' + val.name + ' -' + val.emp_code + '<br><label>';
                        });
                        // console.log("Data Found");
                        $(".emp").html(str);
                    }

                });
            }

        });

    });



    $(document).ready(function () {


        $("#viewExEmp").click(function () {

            var company_id = $("#ExCompany").val();

            var emp_code = $("#emp_code_check").val();
			var shift_id = $("#shift_id").val();
            var start_date = $("input[name='ex_start_date']").val();
            var end_date = $("input[name='ex_end_date']").val();

            $.post("<?= url('Settings/ShiftAssign/FilterShiftWiseEmployee') ?>", {'company_id': company_id, 'shift_id': shift_id, 'start_date': start_date, 'end_date': end_date,'emp_code': emp_code, '_token': '<?= csrf_token() ?>'}, function (data) {
                var total = data.length;
                if (total != 0)
                {
                    var str = '';
                    var i=1;
					str +='<table border="1" class="table table-bordered">';
					str +='<thead>';
					str +='<tr>';
					str +='<th>#</th><th>Emp Code</th><th>Shift Name</th><th>Start Date</th><th>End Date</th><th>Created Date Time</th>';
					str +='</tr>';
					str +='</thead>';
					str +='<tbody>';
                    $.each(data, function (index, val) {

                        //str += '<p  id="' + val.emp_code + '">' + i + '. ' + val.name + ' -' + val.emp_code + '</p>';
						str +='<tr>';
						str +='<th>'+i+'</th><th>' + val.emp_code + '</th><th>' + val.shift_name + '</th><th>' + val.start_date + '</th><th>' + val.end_date + '</th><th>' + val.created_at + '</th>';
						str +='</tr>';
					
                        i+=1;
                    });
					str +='</tbody>';
					str +='</table>';
                    // console.log("Data Found");
                    $(".emp2").html(str);
                }
                else
                {
                    $(".emp2").html('0 Record Found');
                }
                //console.log(data);
            });

        });
		
		$("#viewExEmp_excel").click(function () {

            var company_id = $("#ExCompany").val();

            var emp_code = $("#emp_code_check").val();
			var shift_id = $("#shift_id").val();
            var start_date = $("input[name='ex_start_date']").val();
            var end_date = $("input[name='ex_end_date']").val();
			if(company_id!='' && start_date!='' && end_date!='')
			{
				if(emp_code=='')
				{
					emp_code=0;
				}
				
				if(shift_id=='')
				{
					shift_id=0;
				}
				
				var url="<?= url('Settings/ShiftAssign/FilterShiftWiseEmployee/Excel') ?>/"+company_id+"/"+start_date+"/"+end_date+"/"+shift_id+"/"+emp_code;
				
				window.location.href=url;
			}
			else
			{
				alert("Please Fillup Company, Start Date, End Date");
			}
        });

    });
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




@include('include.coreKendo')

@include('ajax_include.company_wise_department')
@include('ajax_include.company_wise_shift')
@include('ajax_include.department_wise_section')
@include('ajax_include.section_wise_designation')
@include('ajax_include.company_department_section_designation_wise_employee')

@endsection