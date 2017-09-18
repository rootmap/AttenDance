<?php
$pageinfo = array("Jobcard Edit Mode", "Jobcard Edit Mode", "", "SUL", "Filter Jobcard", "Genarate Jobcard");
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
							
							<form actions="{{url('Jobcard/EditMode')}}" method="post" name="jobedit">

                            <div class="row">
                                <!--Added For Leave User Data Filtering-->
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Employee Code</label>
                                        <input type="text" name="emp_code"  class="form-control employee_filter" placeholder="Type Employee Code" 
										@if(isset($emp_code))
											value="{{$emp_code}}"  
										@endif
										>
                                    </div>
                                </div>
								{{csrf_field()}}
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="l30">Start date</label>
                                        <label class="input-group datepicker-only-init">
                                      <input type="text" id="start_date" name="start_date" class="form-control" placeholder="Type Start Day" 
									  @if(isset($start_date))
										  value="{{$start_date}}" 
									  @endif
									  />
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
                                            <input type="text" id="end_date" name="end_date" class="form-control" placeholder="Type End Day" 
											@if(isset($end_date))
												value="{{$end_date}}" 
											@endif
											/>
                                            <span class="input-group-addon" style="">
                                                <i class="icmn-calendar"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                            </div>

                            <div class="form-actions">
                                <button type="submit" name="filter"  class="btn btn-success">{{$pageinfo[5]}}</button>
                            </div>
							
							</form>
							
							<div class=" col-md-12 emp_details">

                            </div>


                            <!-- kendo table code end fro here-->
                            <!--Vertical Form Ends Here-->
							@if(isset($count))
							<form method="post" action="{{url('Jobcard/Row/Save/Multiple')}}" name="batchSave">
								{{csrf_field()}}	
                            <div class=" col-md-12">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th>Start Date</th>
											<th>In Time</th>
											<th>End Date</th>
											<th>Out Time</th>
											<th>Day Type</th>
											<th>LL Ref</th>
											<th>Admin OT</th>
											<th>Standard Out</th>
											<th>Standard OT</th>
										</tr>
									</thead>
									<tbody>
										@if($count==0)
										<tr>
											<td colspan="8">No Record Found</td>
										</tr>
										@else
										
										
										@foreach($jobData as $row)
											<tr id="{{$row['start_date']}}">
												<td>
												{{$row['start_date']}}
												<input type="hidden" name="id[]" id="id_{{$row['start_date']}}" value="{{$row['id']}}">
												<input type="hidden" name="emp_code[]" id="emp_code_{{$row['start_date']}}" value="{{$row['emp_code']}}">
												<input type="hidden"  name="start_date[]" value="{{$row['start_date']}}">
												<input type="hidden"  name="user_end_date[]"  id="user_end_date_{{$row['start_date']}}">
												</td>
												<td>
													<input class="form-control ot_count_flag" id="in_time_{{$row['start_date']}}" size="8" type="text"  name="in_time[]" id="in_time" value="{{$row['in_time']}}">
												</td>
												<td>
													<input class="form-control ot_count_flag" size="10"  id="end_date_{{$row['start_date']}}" type="text"  name="jobcard_end_date[]" value="{{$row['end_date']}}">
												</td>
												
												<td>
													<input class="form-control ot_count_flag"  size="8" id="out_time_{{$row['start_date']}}" type="text"  name="out_time[]" id="out_time_{{$row['start_date']}}" value="{{$row['out_time']}}">
												</td>
												<td>
													<select class="form-control ot_count_flag"  id="day_status_{{$row['start_date']}}" name="day_status[]">
														<option 
														@if($row['day_status']=="A")
															selected="selected" 
														@endif 
														value="A">A</option>
														@foreach($day_status as $dt)
														<option 
														@if($row['day_status']==$dt->day_short_code)
															selected="selected" 
														@endif 
														value="{{$dt->day_short_code}}">{{$dt->day_short_code}}</option>
														
														@endforeach
														<option 
														@if($row['day_status']=="LL")
															selected="selected" 
														@endif 
														value="LL">LL</option>
													</select>
												</td>
												<td>
													<input class="form-control"  size="10" id="ll_ref_{{$row['start_date']}}" type="text" value="{{$row['ll_ref']}}" name="ll_ref[]">
												</td>
												<td>
												<input id="edit_flag_{{$row['start_date']}}" type="hidden"  name="edit_flag[]" value="{{$row['edit_jobcard_flag']}}">
												<input class="form-control" size="8" readonly id="over_time_{{$row['start_date']}}" type="text"  name="admin_ot[]" value="{{$row['total_ot']}}">
												</td>
												<td>
												<input class="form-control" readonly size="8" id="user_out_time_{{$row['start_date']}}" type="text"  name="user_out_time[]" value="{{$row['user_out_time']}}">
												</td>
												<td>
												<input class="form-control" readonly size="8" id="user_total_ot_{{$row['start_date']}}" type="text"  name="user_total_ot[]" value="{{$row['user_total_ot']}}">
												</td>
												
											</tr>
											
										@endforeach	
										
											<tr>
												<td colspan="6" align="right">Admin Total OT=</td>
												<td align="center">
												<input class="form-control text-success" readonly size="8"  value="{{$admin_time_total_tray}}">
												</td>
												<td align="right">User Total OT=</td>
												<td align="center"><input class="form-control text-success" readonly size="8"  value="{{$user_time_total_tray}}"></td>
											</tr>
										
											<tr>
												<td colspan="9">
													<button type="submit" name="editjob" class="btn btn-danger pull-right">
														<i class="fa fa-check"></i> Confirm All
													</button>
												</td>
											</tr>
										
										@endif
									</tbody>
								</table>
                            </div>
							</form>
							@endif

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
                            </div>
                            
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
@section('extraFooter')
<script>
$(document).ready(function(){
	$(".employee_filter").keyup(function () {
		//alert('Not Working');
		var emp_code = $(this).val();
		if (emp_code == '')
		{
			emp_code = 0;
		}
		
		var datalength=emp_code.length;
		if(datalength==8)
		{
			FilterEmpInfo(emp_code);		
		}
		else
		{
			$(".emp_details").html('0 Record Found');
		}
	});
	
	$(".employee_filter").change(function () {
		//alert('Not Working');
		var emp_code = $(this).val();
		if (emp_code == '')
		{
			emp_code = 0;
		}
		
		var datalength=emp_code.length;
		if(datalength==8)
		{
			FilterEmpInfo(emp_code);		
		}
		else
		{
			$(".emp_details").html('0 Record Found');
		}
	});
});

function FilterEmpInfo(emp_code)
{
	$.post("<?= url('Jobcard/GetEpmloyeedetails') ?>", {'emp_code': emp_code, '_token': '<?= csrf_token() ?>'}, function (data) {
			var total = data.length;

			if (total != 0)
			{
				$(".emp_details").html('');
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
}
</script>
@if(isset($jobData))
<script>

function empFilter()
{
	setTimeout(function(){ 
		var emp_code = $('.employee_filter').val();
		if (emp_code == '')
		{
			emp_code = 0;
		}
		
		var datalength=emp_code.length;
		if(datalength==8)
		{
			FilterEmpInfo(emp_code);		
		}
		else
		{
			empFilter();
			$(".emp_details").html('0 Record Found');
		}
	}, 1000);
}

$(document).ready(function(){
	empFilter();
	$(".ot_count_flag").change(function(){
		var rowID=$(this).parent('td').parent('tr').attr('id');
		//$("#over_time_"+rowID).html(rowID);
		var day_status=$("#day_status_"+rowID).val();
		var in_time=$("#in_time_"+rowID).val();
		var end_date=$("#end_date_"+rowID).val();
		var out_time=$("#out_time_"+rowID).val();
		
		var emp_code=$("#emp_code_"+rowID).val();
		
		var jobcard_id=$("#id_"+rowID).val();
		
		if(day_status=='LL')
		{
			$("#ll_ref_"+rowID).val('Enter Date');
		}
		
		//alert(day_status);
		
		$.post("<?=url('Jobcard/Row/viewMode')?>",
		{'start_date':rowID,'in_time':in_time,'end_date':end_date,'out_time':out_time,
		'emp_code':emp_code,
		'id':jobcard_id,
		'day_status':day_status,
		'_token':"<?=csrf_token()?>"},function(retJob){
			//alert(retJob);
			var obj=retJob;
			$("#over_time_"+rowID).val(retJob.admin_ot);
			$("#user_out_time_"+rowID).val(retJob.user_new_out_time);
			$("#user_total_ot_"+rowID).val(retJob.user_new_ot_time);
			$("#edit_flag_"+rowID).val(1);
			$("#user_end_date_"+rowID).val(retJob.user_end_date);
		});
		//alert(rowID);
	});	
});
</script>
@endif
@include('include.coreKendo')
<?= MenuPageController::genarateKendoDatePicker(array("start_date", "end_date")) ?>
<script>

/*$('.btn').on('click', function() {
    $(this).html('loading row');
	var buttonID=$(this).attr('id');
	makeFiction(buttonID);
	
    setTimeout(function() {
       $("#"+buttonID).fadeOut();
   }, 3000);
});

function makeFiction(id)
{
	$("#in_time_"+buttonID).attr("readonly", false);
	$("#end_date_"+buttonID).attr("readonly", false);
	$("#out_time_"+buttonID).attr("readonly", false);
}*/




</script>
@endsection
