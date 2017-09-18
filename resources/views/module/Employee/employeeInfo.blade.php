<?php
if (isset($basic)) {
    $pageinfo = array("Edit Add Employee Settings", "Edit Employee Record", "", "SUL");
} else {
    $pageinfo = array("Employee Settings", "Add Employee Record", "", "SUL");
}
?>

@extends('layout.master')

@section('content')
@include('include.coreBarcum')
<script>
    $(function () {


        var form = $("#example-advanced-form").show();

        form.steps({
            headerTag: "h3",
            bodyTag: "fieldset",
            transitionEffect: "slideLeft",
            onStepChanging: function (event, currentIndex, newIndex)
            {
                // Allways allow previous action even if the current form is not valid!
                if (currentIndex > newIndex)
                {
                    return true;
                }
                // Forbid next action on "Warning" step if the user is to young
                if (newIndex === 3 && Number($("#age-2").val()) < 18)
                {
                    return false;
                }
                // Needed in some cases if the user went back (clean up)
                if (currentIndex < newIndex)
                {
                    // To remove error styles
                    form.find(".body:eq(" + newIndex + ") label.error").remove();
                    form.find(".body:eq(" + newIndex + ") .error").removeClass("error");
                }
                form.validate().settings.ignore = ":disabled,:hidden";
                return form.valid();
            },
            onStepChanged: function (event, currentIndex, priorIndex)
            {
                // Used to skip the "Warning" step if the user is old enough.
                if (currentIndex === 2 && Number($("#age-2").val()) >= 18)
                {
                    form.steps("next");
                }
                // Used to skip the "Warning" step if the user is old enough and wants to the previous step.
                if (currentIndex === 2 && priorIndex === 3)
                {
                    form.steps("previous");
                }
            },
            onFinishing: function (event, currentIndex)
            {
                form.validate().settings.ignore = ":disabled";
                return form.valid();
            },
            onFinished: function (event, currentIndex)
            {
                ///alert("Submitted!");
                document.getElementById("example-advanced-form").submit();
            }
        }).validate({
            errorPlacement: function errorPlacement(error, element) {
                element.before(error);
            },
            rules: {
                confirm: {
                    equalTo: "#password-2"
                }
            }
        });


    });
</script>
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
                            <!-- Form wizard with validation card start [Use Only This Block For Custom Form Wizard] -->
                            <div id="wizard">

                                <section>
                                    @if(isset($basic))
                                    <form class="wizard-form"  enctype="multipart/form-data"  id="example-advanced-form" name="Employeeinfo" action="{{url('Employee/Employeeinfo/Update/'.$basic['emp_code'])}}" method="post">
                                        {{csrf_field()}}
									<h3> Basic info </h3>
                                        <fieldset>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30">First Name<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <input type="text" name="fname" class="required form-control" value="{{$basic->first_name}}" placeholder="Type First Name" id="l30" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30">Last Name</label>
                                                        <input type="text" name="lname" class="form-control" value="{{$basic->last_name}}" placeholder="Type Last Name" id="l30" >
                                                    </div>
                                                </div>

                                                <div class="col-lg-4" >
                                                    <div class="form-group">
                                                        <label for="l30">Marital Status</label>
                                                        <select class="form-control" name="marital_id">
                                                            @if(isset($marital_status))
                                                            @foreach($marital_status as $row)
                                                            <option <?php if ($basic['marital_status'] == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Gender</label>
                                                        <select class="form-control" name="gender_id">
                                                            @if(isset($gender))
                                                            @foreach($gender as $row)
                                                            <option <?php if ($basic['gender'] == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Blood Group</label>
                                                        <select class="form-control" name="blood_id">
                                                            @if(isset($b_group))
                                                            @foreach($b_group as $row)
                                                            <option <?php if ($basic['blood_group'] == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30" class="col-md-12">Date of Birth</label>
                                                        <!--<label class="input-group datepicker-only-init">-->
                                                        <input type="text" name="DOB" class="form-control" value="{{$basic->dob}}" placeholder="Type Date of Birth"/>
    <!--                                                        <span class="input-group-addon">
                                                            <i class="icmn-calendar"></i>
                                                        </span>
                                                    </label>-->
                                                    </div>
                                                </div>

                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30" class="col-md-12">Country</label>
                                                        <select class=" form-control" name="country_id">
                                                            @if(isset($country))
                                                            @foreach($country as $row)
                                                            <option <?php if ($basic['country'] == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30" class="col-md-12">City</label>
                                                        <select class="form-control" name="city_id">
                                                            @if(isset($city))
                                                            @foreach($city as $row)
                                                            <option <?php if ($basic['city'] == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30">Address<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <input type="text" name="address" class="form-control" value="{{$basic->address}}" placeholder="Type Address" id="l30" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30">Phone</label>
                                                        <input type="text" name="phone" class="form-control" value="{{$basic->phone}}" placeholder="Type Phone" id="l30" required>
                                                    </div>
                                                </div>
												
												@if(file_exists('upload/employee_image/'.$basic->image)) 
												  <div class="col-lg-2">
                                                    <div class="card" style="">
                                                        <img style="height: 160px;" class="card-img-top img-responsive" src="{{url('upload/employee_image')}}/{{$basic->image}}" alt="No Image Found">
                                                    </div>
                                                </div>
												<div class="col-lg-4">
                                                    <label for="l30">Update Employee Photo</label>

                                                    <input name="emp_image" type="file" class="dropify" data-height="100"/>

                                                </div>
												@else
												  <div class="col-lg-4">
                                                    <label for="l30">Upload Employee Photo</label>

                                                    <input name="emp_image" type="file" class="dropify" data-height="100"/>

                                                </div>
												@endif
												
                                                
                                                

                                                <!--                                            <div class="col-lg-4">
                                                                                                <label for="l30">Employee Photo</label>
                                                
                                                                                                <input name="emp_image" type="file" class="dropify" data-height="100" />
                                                
                                                                                            </div>-->
                                                <div class="col-lg-6">

                                                    <div class="form-group">
                                                        <label for="l30">Joining Date</label>
                                                        <input  type="text" name="join_date" value="{{$basic->join_date}}" class="form-control" placeholder="Type Join Date"/>
                                                    </div>
                                                </div>
                                            </div>
											
                                        </fieldset>
                                        
										<h3> Company </h3>
                                        <fieldset>
                                            <div class="row">
											<div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="l30">Company<span class="after" style="color:#EF5F5F"> *</span></label>
                                                    <select class="required form-control" name="company_id" required>
                                                        @if(isset($company))
                                                        @foreach($company as $row)
                                                        <option <?php if ($compa['company_id'] == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                        @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30">Department<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <select class="required form-control select-search" name="department_id" required>
                                                            @if(count($department)!=0)
																@foreach($department as $row)
																<option 
															@if(($dept?$dept->department_id:0) == $row->id)  
																	selected="selected" 
															@endif
															value="{{$row->id}}">{{$row->name}}</option>
																@endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30">Section<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <select class="required form-control select-search" name="section_id" required>
                                                            @if(isset($section))
                                                            @foreach($section as $row)
                                                            <option <?php 
															if (($sec?$sec['section_id']:0) == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30">Designation<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <select class="required form-control select-search" name="designation_id" required>
                                                            @if(isset($designation))
                                                            @foreach($designation as $row)
                                                            <option <?php if (($desi?$desi['designation_id']:0) == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">

                                                    <div class="form-group">
                                                        <label for="l3">Staff Grade<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <select class="required form-control select-search" name="staffgrade_id" required>
                                                            @if(isset($staff))
                                                            @foreach($staff as $row)
                                                            <option <?php if (($staffGrade?$staffGrade->staff_grade_id:0) == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6">

                                                    <div class="form-group">

                                                        <label for="l30">Supervisor<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <select class="form-control select-search" name="supervisor">
															<option value="0">None </option>
															<?php 
															$supExID=($supervisor ? $supervisor->employee_info_sup_id : 0);
															?>
                                                            @if(!empty($Inreplac))
                                                            @foreach($Inreplac as $row)
															<?php if ($supExID==$row->emp_code && !empty($supExID)) { ?> 
															<option selected="selected" value="{{$row->emp_code}}">{{$row->first_name}} {{$row->emp_code}}</option>
															<?php }else{
																?>
																<option value="{{$row->emp_code}}">{{$row->first_name}} {{$row->emp_code}}</option>
																<?php
															} ?>
                                                            
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
												<div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30">Employment Type</label>
                                                        <select class="form-control" name="employee_employment_type_id">
                                                            <option value="">Select Type</option>
															@if(isset($emtype))
																@foreach($emtype as $em)
																	<option value="{{$em->id}}">{{$em->name}}</option>
																@endforeach
															@endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">

                                                        <label for="l30">Job Location</label>
                                                        <select class="form-control" name="branch_id">
                                                            @foreach($branch as $row)
                                                            <option <?php if(isset($job_location[0]->branch_id)){ if ($job_location[0]->branch_id == $row->id) { ?> selected="selected" <?php }} ?> value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach
                                                           
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Effective From</label>
                                                        <input 
														
														@if(isset($others_info[0]->company_effective_start_date))
															value="{{$others_info[0]->company_effective_start_date}}" 
														@endif
														type="text" name="effective_date" class="form-control" placeholder="Select Date" />
                                                    </div>
                                                </div>

                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Proposed Confirmation Date </label>
                                                        <input 
														@if(isset($others_info[0]->proposed_confirmation_date))
														value="{{$others_info[0]->proposed_confirmation_date}}" 
														@endif
														type="text" name="pc_date" class="form-control" placeholder="Type Date"/>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4">
                                                    <!--{{$Inreplac}}-->    
                                                    <div class="form-group">
                                                        <label for="l30">In Replacement of</label>
                                                        <select class="form-control select-search" name="inReplacement">
															<option <?php if (($others_info ? $others_info[0]->replacement_of_emp_code : '0') == 0) { ?> selected="selected" <?php } ?> value="0">New</option>
                                                            @if(isset($Inreplac))
                                                            @foreach($Inreplac as $row)
                                                            <option <?php if (($others_info ? $others_info[0]->replacement_of_emp_code : '0') == $row->emp_code) { ?> selected="selected" <?php } ?> value="{{$row->emp_code}}">{{$row->first_name}} {{$row->last_name}} {{$row->emp_code}}</option>
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-check col-lg-4">
                                                    <div class="form-group"><br>
                                                        <label>
                                                            <input
																@if(isset($others_info[0]->is_pf_eligible))
                                                                @if(($others_info[0]->is_pf_eligible?$others_info[0]->is_pf_eligible:0)==1)
                                                                checked="checked"
                                                                @endif
																@endif
                                                                type="checkbox" value="1" name="pf_eligible"> Provident Fund Eligible</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4" id="pf-date">
                                                    <div class="form-group">
                                                        <label for="l30">PF Effective From</label>
                                                        <!--<label class="input-group datepicker-only-init">-->
                                                        <input type="text" name="pf_effective_from" 
														@if(isset($others_info[0]->pf_effective_from))
														value="{{$others_info[0]->pf_effective_from}}" 
														@endif
														class="form-control " id="show" placeholder="Select Date" />
    <!--                                                            <span class="input-group-addon">
                                                            <i class="icmn-calendar"></i>
                                                        </span>
                                                    </label>-->
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
										<h3> Additional Info </h3>
                                        <fieldset>
											<h3>Job experience</h3>
                                            <div class="input_fields_containerJob col-lg-12">
                                                @if(!empty($job_exp))
                                                @foreach($job_exp as $row)
                                                <div class="row append2">
                                                    <div class="col-lg-3 pull-left">
                                                        <div class=" form-group">
                                                            <label for="l30">Company Name</label>
                                                            <input type="text" name="com_name[]" value="{{$row->company_name}}" class="form-control" placeholder="Type Company Name" id="l30"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class=" form-group">
                                                            <label for="l30">Company Address</label>
                                                            <input type="text" name="com_address[]" value="{{$row->company_address}}" class="form-control" placeholder="Type Address" id="l30"/>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 pull-left">
                                                        <div class=" form-group">
                                                            <label for="l30">Designation</label>
                                                            <input type="text" name="com_desigantion[]" value="{{$row->desigantion}}" class="form-control" placeholder="Type Designation" id="l30"/>
                                                        
                                                        </div>
                                                    </div>


                                                    <div class="col-lg-3 pull-left">
                                                        <div class=" form-group">
                                                            <label for="l30" class="col-xs-12">Start Date</label>
                                                            <!--                                                            <label class="input-group datepicker-only-init">-->
                                                            <input type="text" name="com_s_date[]" value="{{$row->start_date}}" class="form-control" placeholder="Select End Date"/>
                                                        
    <!--                                                                <span class="input-group-addon">
                                                                    <i class="icmn-calendar"></i>
                                                                </span>
                                                            </label>-->
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30" class="col-xs-12">End Date</label>
                                                            <!--<label class="input-group datepicker-only-init">-->
                                                            <input type="text" name="com_e_date[]" value="{{$row->end_date}}" class="form-control" placeholder="Select End Date"/>
                                                        
    <!--                                                                <span class="input-group-addon">
                                                                    <i class="icmn-calendar"></i>
                                                                </span>
                                                            </label>-->
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="required form-group">
                                                            <label for="l30">Responsibility</label>
                                                            <input type="text" name="com_responsibility[]" value="{{$row->responsibility}}" class="form-control" placeholder="Type Responsibility" id="l30">
                                                        
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="required form-group">
                                                            <label for="l30">Attache Certificate</label>
                                                            <input type="file" name="com_upload[]" class="form-control">
                                                        </div>
                                                    </div>

                                                </div>
                                                @endforeach
                                                @else
                                                <div class="row append2">
                                                    <div class="col-lg-3 pull-left">
                                                        <div class=" form-group">
                                                            <label for="l30">Company Name</label>
                                                            <input type="text" name="com_name[]" value="" class="form-control" placeholder="Type Company Name" id="l30"/>
                                                            
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class=" form-group">
                                                            <label for="l30">Company Address</label>
                                                            <input type="text" name="com_address[]" value="" class="form-control" placeholder="Type Address" id="l30"/>
                                                            
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 pull-left">
                                                        <div class=" form-group">
                                                            <label for="l30">Designation</label>
                                                            <input type="text" name="com_desigantion[]" value="" class="form-control" placeholder="Type Designation" id="l30"/>
                                                            
                                                        </div>
                                                    </div>


                                                    <div class="col-lg-3 pull-left">
                                                        <div class=" form-group">
                                                            <label for="l30" class="col-xs-12">Start Date</label>
                                                            <!--                                                            <label class="input-group datepicker-only-init">-->
                                                            <input type="text" name="com_s_date[]" value="" class="form-control" placeholder="Select End Date"/>
                                                            
    <!--                                                                <span class="input-group-addon">
                                                                    <i class="icmn-calendar"></i>
                                                                </span>
                                                            </label>-->
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30" class="col-xs-12">End Date</label>
                                                            <!--<label class="input-group datepicker-only-init">-->
                                                            <input type="text" name="com_e_date[]" value="" class="form-control" placeholder="Select End Date"/>
                                                            
    <!--                                                                <span class="input-group-addon">
                                                                    <i class="icmn-calendar"></i>
                                                                </span>
                                                            </label>-->
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="required form-group">
                                                            <label for="l30">Responsibility</label>
                                                            <input type="text" name="com_responsibility[]" value="" class="form-control" placeholder="Type Responsibility" id="l30">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="required form-group">
                                                            <label for="l30">Attache Certificate</label>
                                                            <input type="file" name="com_upload[]" class="form-control">
                                                        </div>
                                                    </div>

                                                </div>
                                                @endif
												<a href="#" class="btn btn-success add_more_buttonJob">Add More</a>
                                            </div>
											
											<h3> Education Info </h3>
                                            <div class="input_fields_containerEdu col-lg-12">
                                                @if(!empty($emp_edu))
                                                @foreach($emp_edu as $row)
                                                <div class="row append">
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Certification Name</label>
                                                            <input type="text" name="certification[]" value="{{$row->certification_name}}" class="form-control" placeholder="Type Certification Name" id="l30">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Institute Name</label>
                                                            <input type="text" name="institute[]" value="{{$row->institute}}" class="form-control" placeholder="Type Institute Name" id="l30">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Institute Address</label>
                                                            <input type="text" name="institute_add[]" value="{{$row->institute_add}}" class="form-control" placeholder="Type Institute Address" id="l30">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30" class="col-xs-12">Start Date</label>
                                                            <!--<label class="input-group datepicker-only-init">-->
                                                            <input type="text" name="edu_s_date[]" value="{{$row->start_date}}" class="form-control" placeholder="Select End Date"/>
    <!--                                                                <span class="input-group-addon">
                                                                    <i class="icmn-calendar"></i>
                                                                </span>
                                                            </label>-->
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30" class="col-xs-12">End Date</label>
                                                            <!--<label class="input-group datepicker-only-init">-->
                                                            <input type="text" name="edu_e_date[]" value="{{$row->end_date}}" class="form-control" placeholder="Select End Date"/>
    <!--                                                                <span class="input-group-addon">
                                                                    <i class="icmn-calendar"></i>
                                                                </span>
                                                            </label>-->
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Result</label>
                                                            <input type="text" name="result[]" value="{{$row->result}}" class="form-control" placeholder="Type Result" id="l30">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Attache Certificate</label>
                                                            <input type="file" name="edu_upload[]" class="form-control">
                                                        </div>
                                                    </div>

                                                </div>
                                                @endforeach
                                                @else
                                                <div class="row append">
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Certification Name</label>
                                                            <input type="text" name="certification[]" value="" class="form-control" placeholder="Type Certification Name" id="l30">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Institute Name</label>
                                                            <input type="text" name="institute[]" value="" class="form-control" placeholder="Type Institute Name" id="l30">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Institute Address</label>
                                                            <input type="text" name="institute_add[]" value="" class="form-control" placeholder="Type Institute Address" id="l30">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30" class="col-xs-12">Start Date</label>
                                                            <!--<label class="input-group datepicker-only-init">-->
                                                            <input type="text" name="edu_s_date[]" value="" class="form-control" placeholder="Select End Date"/>
    <!--                                                                <span class="input-group-addon">
                                                                    <i class="icmn-calendar"></i>
                                                                </span>
                                                            </label>-->
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30" class="col-xs-12">End Date</label>
                                                            <!--<label class="input-group datepicker-only-init">-->
                                                            <input type="text" name="edu_e_date[]" value="" class="form-control" placeholder="Select End Date"/>
    <!--                                                                <span class="input-group-addon">
                                                                    <i class="icmn-calendar"></i>
                                                                </span>
                                                            </label>-->
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Result</label>
                                                            <input type="text" name="result[]" value="" class="form-control" placeholder="Type Result" id="l30">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Attache Certificate</label>
                                                            <input type="file" name="edu_upload[]" class="form-control">
                                                        </div>
                                                    </div>

                                                </div>
                                                @endif
                                                <a href="#" class="btn btn-success add_more_buttonEdu">Add More</a>
                                            </div>
                                        </fieldset>
										
										<h3> Salary Info </h3>
                                        <fieldset>
										
											<div class="row">
												
													@foreach($componentPlus as $index=>$cop)
													<?php $fidval = array_column($exEmpPayrollEmpo, 'salary_'.strtolower(str_replace(' ','_',$cop->header_title)));
															//print_r($fidval[0]); ?>
													<div class="col-sm-12 col-lg-4">
														<div class="form-group">
															<label for="userName-2" class="block">{{$cop->header_title}}</label>
															<input id="userName-2" name="salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>" onkeyup="calculateSumGross()" type="text" value="@if(empty($fidval[0])) 0 @else {{$fidval[0]}} @endif"  class="required form-control" required >
														</div>
														
														
													</div>
													@endforeach
													<div class="col-sm-12 col-lg-12">
														<hr />
													</div>
													@foreach($componentMinus as $cop)
													<?php $fidval = array_column($exEmpPayrollEmpo, 'salary_'.strtolower(str_replace(' ','_',$cop->header_title)));
															//print_r($fidval[0]); ?>
													<div class="col-sm-12 col-lg-4">
														<div class="form-group">
															<label for="userName-2" class="block">{{$cop->header_title}}</label>
															<input id="userName-2" name="salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>" onkeyup="calculateSumGross()" type="text"  value="@if(empty($fidval[0])) 0 @else {{$fidval[0]}} @endif"   class="required form-control" required >
														</div>
													</div>
													@endforeach
													<div class="col-sm-12 col-lg-12">
														<hr />
													</div>
													
													
													<?php $fidvalGross = array_column($exEmpPayrollEmpo, 'salary_'.strtolower(str_replace(' ','_',$componentGross->header_title)));
															//print_r($fidval[0]); ?>
													<div class="col-sm-12 col-lg-4">
														<div class="form-group">
															<label for="userName-2" class="block">{{$componentGross->header_title}}</label>
															<input id="userName-2" name="salary_<?=strtolower(str_replace(' ','_',$componentGross->header_title))?>" type="text" value="@if(empty($fidvalGross[0])) 0 @else {{$fidvalGross[0]}} @endif"  class="required form-control" required >
														</div>
													</div>
													
													<div class="col-sm-12 col-lg-4">
														<div class="form-group">
															<label for="userName-2" class="block">Total Deduction</label>
															<input id="userName-2" name="salary_deduction" type="text" value="0"  class="required form-control" required >
														</div>
													</div>
													
													<div class="col-sm-12 col-lg-4">
														<div class="form-group">
															<label for="userName-2" class="block">Bank Transferable</label>
															<input id="userName-2" name="salary_bank_transferable" type="text" value="0"  class="required form-control" required >
														</div>
													</div>
													
													<div class="col-sm-12 col-lg-12">
														<hr />
													</div>
													
													@foreach($componentNonFunctional as $cop)
													<?php $fidvalNonFunc = array_column($exEmpPayrollEmpo, 'salary_'.strtolower(str_replace(' ','_',$cop->header_title)));
															//print_r($fidval[0]); ?>
													<div class="col-sm-12 col-lg-4">
														<div class="form-group">
															<label for="userName-2" class="block">{{$cop->header_title}}</label>
															<input id="userName-2" name="salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>" onkeyup="calculateSumGross()" type="text" value="@if(empty($fidvalNonFunc[0])) 0 @else {{$fidvalNonFunc[0]}} @endif" class="required form-control" required >
														</div>
													</div>
													@endforeach
													
                                            </div>
                                            

                                        </fieldset>
										
										<script>
										var total_gross=0;
										var salary_deduction=0;
										var salary_bank_transferable=0;
										function calculateSumGross()
										{
											<?php
											foreach($componentPlus as $cop):
											?>
												var salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>=$('input[name="salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>"]').val();
												console.log(salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>);
											<?php
											endforeach;
											?>
											
											<?php
											foreach($componentMinus as $cop):
											?>
												var salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>=$('input[name="salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>"]').val();
												console.log(salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>);
											<?php
											endforeach;
											?>
											
											total_gross=<?php $plus=0;
											if(!empty($componentMinus))
											{
												foreach($componentPlus as $cop){ 
													if($plus==0)
													{
														echo "(salary_".strtolower(str_replace(' ','_',$cop->header_title))."-0)";
													}
													else
													{
														echo "+(salary_".strtolower(str_replace(' ','_',$cop->header_title))."-0)";
													}
													$plus++;
												}
											}
											else
											{
												echo 0;
											}
											?>;			

											salary_deduction=<?php
											$minparam=0;
											if(!empty($componentMinus))
											{
												foreach($componentMinus as $cop){ 
													if($minparam==0)
													{
														echo "(salary_".strtolower(str_replace(' ','_',$cop->header_title))."-0)";
													}
													else
													{
														echo "+(salary_".strtolower(str_replace(' ','_',$cop->header_title))."-0)";
													}
													$minparam++;
												}
											}
											else
											{
												echo 0;
											}
											?>;
											
											salary_bank_transferable=(total_gross-salary_deduction);
											
											$('input[name="<?="salary_".strtolower(str_replace(' ','_',$componentGross->header_title))?>"]').val(total_gross);
											$('input[name="salary_deduction"]').val(salary_deduction);
											$('input[name="salary_bank_transferable"]').val(salary_bank_transferable);

											//salary_deduction
											//salary_bank_transferable
											console.log(total_gross);
											
										}	
										
										calculateSumGross();
											
										</script>
										
										<h3> Role &amp; Permission </h3>

                                        <fieldset>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="userName-2" class="block">Employee code<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <input id="userName-2" name="emp_code" type="text" <?php if(isset($super)){ ?> value="{{$super}}" <?php } ?>  class="required form-control" disabled>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="email-2" class="block">Email<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <input id="email-2" name="email" type="email" <?php if(isset($email)){ ?>  value="{{$email}}" <?php } ?>   class="required form-control" >
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="password-2" class="block">Password<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <input id="password-2" name="password" type="password" <?php if(isset($password)){ ?>  value="{{$password}}" <?php } ?>  class="form-control required">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="confirm-2" class="block">Confirm Password <span class="after" style="color:#EF5F5F"> *</span></label>

                                                        <input id="confirm-2" name="confirm" type="password" <?php if(isset($password)){ ?>  value="{{$password}}" <?php } ?>  class="form-control required">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30" class="col-md-12">Proposed Confirmation Date<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <!--<label class="input-group datepicker-only-init">-->
                                                        <input type="text" name="pc_date" 
														@if(isset($others_info[0]->proposed_confirmation_date))
														value="{{$others_info[0]->proposed_confirmation_date?$others_info[0]->proposed_confirmation_date:'0000-00-00'}}" 
														@endif
														class="form-control required" placeholder="Select Date" />
    <!--                                                            <span class="input-group-addon">
                                                                <i class="icmn-calendar"></i>
                                                            </span>
                                                        </label>-->
                                                    </div>
                                                </div>
												<div class="col-lg-6">

                                                    <div class="form-group">
                                                        <label for="l30">Assign a Role<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <select class="required form-control" name="assignRole" required>
                                                            <option value="">Select a Role</option>
                                                            @foreach($role as $row)
                                                            <option <?php if(isset($Accessrole[0]->system_access_role_id)){ if ($Accessrole[0]->system_access_role_id == $row->id) { ?> selected="selected" <?php }} ?> value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
												<div class="col-md-12"><code>Please select following check box only if an employee is employed in one company, but having weekend plan of another company.</code></div>
												<div class="form-check col-lg-6">
													<div class="form-group">
														<label>
															<!--<input type="checkbox" value="1" id="Weekend_policy_as_diff_company" name="weekend_policy_as_diff_company"> Apply Weekend Policy as Different Company</label>-->
															<input
																@if(isset($wp_effective_from))
																@if($wp_effective_from !=0)
																checked="checked"
																@endif
																@endif
																type="checkbox" value="1" name="weekend_policy_as_diff_company" id="weekend_policy_as_diff_company"> Apply Weekend Policy as Different Company</label>
													</div>
												</div>
												<div class="col-lg-3 weekend_policy_as_diff_company" style="display:none;">
													<div class="form-group">
														<label for="l30">Select Company</label>
														<select class="form-control" name="wp_company_id" required>
                                                            <option value="0" selected="selected" >Select Company</option>
                                                            @foreach($company as $row)
                                                            <option 
															@if($row->id==$wp_company_id)
															 selected="selected"  
															@endif
															value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach
                                                        </select>
													</div>
												</div>
												
												<div class="col-lg-3 weekend_policy_as_diff_company" style="display:none;">
													<div class="form-group">
														<label for="l30">Effective From</label>
														<input 
														@if(isset($wp_effective_from))
														value="{{$wp_effective_from}}" 
														@endif
														type="text" name="wp_effective_from" class="form-control "  placeholder="Select Date" />
													</div>
												</div>
												
												
												
                                            </div>
                                        </fieldset>
                                        


                                    </form>
                                    @else

                                    {{-- <div class="col-lg-6">
                                                <div class="mb-5">
                                                    <table class="table">
                                                        <thead class="thead-inverse">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>First Name</th>
                                                            <th>Last Name</th>
                                                            <th>Username</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <tr>
                                                            <th scope="row">1</th>
                                                            <td>Mark</td>
                                                            <td>Otto</td>
                                                            <td>@mdo</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">2</th>
                                                            <td>Jacob</td>
                                                            <td>Thornton</td>
                                                            <td>@fat</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">3</th>
                                                            <td>Larry</td>
                                                            <td>the Bird</td>
                                                            <td>@twitter</td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div> --}}


                                    <form class="wizard-form"  enctype="multipart/form-data"  id="example-advanced-form" name="Employeeinfo" action="{{url('Employee/Employeeinfo/Add/')}}" method="post">
                                        {{csrf_field()}}
                                        <?php
                                        $isCompanyStaffGrade = MenuPageController::loggedUser('is_company_staffgrade');
                                        ?>
										
                                        <h3> Basic info </h3>
                                        <fieldset>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30">First Name
                                                            <span class="after" style="color:#EF5F5F"> *</span>
                                                        </label>
                                                        <input value="{{ old('fname') }}" type="text" name="fname" class="form-control"  placeholder="Type First Name" id="l30" required/>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30">Last Name</label>
                                                        <input value="{{ old('lname') }}" type="text" name="lname" class="form-control" placeholder="Type Last Name" id="l30"/>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Marital Status</label>
                                                        <select class="form-control" name="marital_id">
                                                            <option value="">Select Marital Status</option>

                                                            @foreach($marital_status as $row)
                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach


                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Gender<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <select class="form-control required" name="gender_id">
                                                            <option value="">Select Gender</option>
                                                            @foreach($gender as $row)
                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach

                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Blood Group</label>
                                                        <select class="form-control" name="blood_id">
                                                            <option value="">Select Blood Group</option>
                                                            @foreach($bloodGroup as $row)
                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Date of Birth </label>
                                                        <input value="{{ old('DOB') }}" type="text" name="DOB" class="form-control" placeholder="Type Date of Birth"/>
                                                    </div>
                                                </div>



                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Country</label>
                                                        <select class="form-control" name="country_id">
                                                            <option value="">Select Country</option>
                                                            @foreach($country as $row)
                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach


                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">City</label>
                                                        <select class="form-control" name="city_id">
                                                            <option value="">Select City</option>
                                                        </select>
                                                    </div>
                                                </div>
												
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30">Address<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <input value="{{ old('address') }}" type="text" name="address" class="form-control required"  placeholder="Type Address" id="l30" >
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30">Phone</label>
                                                        <input value="{{ old('phone') }}" type="text" name="phone" class="form-control" placeholder="Type Phone Number" id="l30" >
                                                    </div>
                                                </div>





                                                <div class="col-lg-5">
                                                    <label for="l30">Employee Photo</label>

                                                    <input name="emp_image" type="file" class="dropify" data-height="100" />

                                                </div>
                                                <div class="col-lg-5">
                                                    <div class="form-group">
                                                        <label for="l30">Join Date</label>
                                                        <input value="{{ old('join_date') }}" type="text" name="join_date" class="form-control" placeholder="Type Join Date"/>
                                                    </div>
                                                </div>
                                            </div>

                                        </fieldset>
                                        <h3> Company </h3>
                                        <fieldset>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30" class="col-md-12">
                                                            Company<span class="after" style="color:#EF5F5F"> *</span>
                                                        </label>
                                                        <select class="form-control" name="company_id" required>
                                                            <option value="0" selected="selected" >Select Company</option>
                                                            @foreach($com as $row)
                                                            <option 
															value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach

                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-lg-6 fff">
                                                    <div class="form-group">
                                                        <label for="l30" class="col-md-12">Department<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <select class="form-control required select-search" name="department_id" required>
                                                            <option>Select Department</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30" class="col-md-12">Section<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <select class="form-control required select-search" name="section_id" >
                                                            <option>Select Section</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30" class="col-md-12">Designation<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <select class="form-control required select-search" name="designation_id" >
                                                            <option>Select Designation</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                @if($isCompanyStaffGrade==1)
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l3" class="col-md-12">Staff Grade</label>
                                                        <select class="form-control" name="staffgrade_id" >
                                                            <option>Select Staff Grade</option>
                                                            @foreach($staff as $row)
                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach

                                                        </select>
                                                    </div>
                                                </div>
                                                @endif
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30" class="col-md-12">Supervisor<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <select class="form-control required select-search" name="supervisor">
                                                            <option selected="selected">Select Supervisor</option>
															<option value="RPAC0000">None</option>
                                                            @foreach($supervisor as $row)
                                                            <option value="{{$row->emp_code}}">{{$row->emp_code}} - {{$row->first_name}} {{$row->last_name}}</option>
                                                            @endforeach

                                                        </select>
                                                    </div>
                                                </div>
												
												<div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30">Employment Type</label>
                                                        <select class="form-control" name="employee_employment_type_id">
                                                            <option value="">Select Type</option>
															@if(isset($emtype))
																@foreach($emtype as $em)
																	<option value="{{$em->id}}">{{$em->name}}</option>
																@endforeach
															@endif
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="l30" class="col-md-12">Job Location</label>
                                                        <select class="form-control" name="branch_id" >
                                                            <option>Select Job Location</option>

                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Effective From</label>
                                                        <input value="{{ old('effective_date') }}" type="text" name="effective_date" class="form-control" placeholder="Select Date" />
                                                    </div>
                                                </div>

                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Proposed Confirmation Date </label>
                                                        <input value="{{ old('pc_date') }}" type="text" name="pc_date" class="form-control" placeholder="Type Date"/>
                                                    </div>
                                                </div>



                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30" >In Replacement of</label>
                                                        <select class="form-control select-search" name="inReplacement">
                                                            <option value="0" selected="selected">Select Employee</option>
															<option value="RPAC0000">New Employee</option>
                                                            @foreach($inRplace as $row)
                                                            <option value="{{$row->emp_code}}">{{$row->emp_code}}-{{$row->first_name}} {{$row->last_name}}</option>
                                                            @endforeach

                                                        </select>
                                                    </div>
                                                </div>

                                                @if($isCompanyStaffGrade==0)
                                                <div class="form-check col-lg-4"><br>
                                                    <div class="form-group">
                                                        <label>
                                                            <input type="checkbox" value="1" id="is_ot_eligible" name="ot_eligible"> OT Eligible</label>
                                                    </div>
                                                </div>
                                                @endif
                                                <div class="form-check col-lg-4"><br>
                                                    <div class="form-group">
                                                        <label onclick="prefixis()">
                                                            <input type="checkbox" value="1" id="is_pf_eligibleI" name="pf_eligible"> Provident Fund Eligible</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4" id="pf-date">
                                                    <div class="form-group">
                                                        <label for="l30">PF Effective From</label>
                                                        <input value="{{ old('pf_effective_from') }}" type="text" name="pf_effective_from" class="form-control " id="show" placeholder="Select Date" />
                                                    </div>
                                                </div>

                                            </div>
                                        </fieldset>
                                        <h3> Additional Info </h3>
                                        <fieldset>
											<h3>Job experience</h3>
											<div class="input_fields_containerJob col-lg-12">
                                                <div class="row append2">
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Company Name</label>
                                                            <input type="text" name="com_name[]" class="form-control" placeholder="Type Company Name" id="l30" >
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Company Address</label>
                                                            <input type="text" name="com_address[]" class="form-control" placeholder="Type Address" id="l30" >
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Designation</label>
                                                            <input type="text" name="com_desigantion[]" class="form-control" placeholder="Type Designation" id="l30" >
                                                        </div>
                                                    </div>


                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30" class="col-xs-12">Start Date</label>

                                                            <input type="text" name="com_s_date[]" class="form-control" placeholder="Select End Date" />
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30" class="col-xs-12">End Date</label>

                                                            <input type="text" name="com_e_date[]" class="form-control" placeholder="Select End Date" />

                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Responsibility</label>
                                                            <input type="text" name="com_responsibility[]" class="form-control" placeholder="Type Responsibility" id="l30" >
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Attache Certificate</label>
                                                            <input type="file" name="com_upload[]" class="form-control">
                                                        </div>
                                                    </div>

                                                </div>


                                            </div>
                                            <a href="#" class="btn btn-success add_more_buttonJob">Add More</a>
											<h3> Education Info </h3>
                                            <div class="input_fields_containerEdu col-lg-12">
                                                <div class="row append">
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Certification Name</label>
                                                            <input type="text" name="certification[]" class="form-control" placeholder="Type Certification Name" id="l30">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Institute Name</label>
                                                            <input type="text" name="institute[]" class="form-control" placeholder="Type Institute Name" id="l30">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Institute Address</label>
                                                            <input type="text" name="institute_add[]" class="form-control" placeholder="Type Institute Address" id="l30">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30" class="col-xs-12">Start Date</label>
                                                            <!--<label class="/*input-group datepicker-only-init*/">-->
                                                            <input type="text" name="edu_s_date[]" class="form-control" placeholder="Select End Date"/>
<!--                                                                                <span class="input-group-addon">
                                                                <i class="icmn-calendar"></i>
                                                            </span>-->
                                                            <!--</label>-->
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30" class="col-xs-12">End Date</label>
                                                            <!--<label class="/*input-group datepicker-only-init*/">-->
                                                            <input type="text" name="edu_e_date[]" class="form-control" placeholder="Select End Date"/>
<!--                                                                                <span class="input-group-addon">
                                                                <i class="icmn-calendar"></i>
                                                            </span>-->
                                                            <!--</label>-->
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Result</label>
                                                            <input type="text" name="result[]" class="form-control" placeholder="Type Result" id="l30">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Attache Certificate</label>
                                                            <input type="file" name="edu_upload[]" class="form-control">
                                                        </div>
                                                    </div>

                                                </div>


                                            </div>
                                            <a href="#" class="btn btn-success add_more_buttonEdu">Add More</a>
                                        </fieldset>
                                        
										
                                        <h3> Salary Info </h3>
                                        <fieldset>
												<div class="row">
												
													@foreach($componentPlus as $cop)
													<div class="col-sm-12 col-lg-4">
														<div class="form-group">
															<label for="userName-2" class="block">{{$cop->header_title}}</label>
															<input id="userName-2" name="salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>" onkeyup="calculateSumGross()" type="text" value="0"  class="required form-control" required >
														</div>
													</div>
													@endforeach
													<div class="col-sm-12 col-lg-12">
														<hr />
													</div>
													@foreach($componentMinus as $cop)
													<div class="col-sm-12 col-lg-4">
														<div class="form-group">
															<label for="userName-2" class="block">{{$cop->header_title}}</label>
															<input id="userName-2" name="salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>" onkeyup="calculateSumGross()" type="text" value="0"  class="required form-control" required >
														</div>
													</div>
													@endforeach
													<div class="col-sm-12 col-lg-12">
														<hr />
													</div>
													
													<div class="col-sm-12 col-lg-4">
														<div class="form-group">
															<label for="userName-2" class="block">{{$componentGross->header_title}}</label>
															<input id="userName-2" name="salary_<?=strtolower(str_replace(' ','_',$componentGross->header_title))?>" type="text" value="0"  class="required form-control" required >
														</div>
													</div>
													
													<div class="col-sm-12 col-lg-4">
														<div class="form-group">
															<label for="userName-2" class="block">Total Deduction</label>
															<input id="userName-2" name="salary_deduction" type="text" value="0"  class="required form-control" required >
														</div>
													</div>
													
													<div class="col-sm-12 col-lg-4">
														<div class="form-group">
															<label for="userName-2" class="block">Bank Transferable</label>
															<input id="userName-2" name="salary_bank_transferable" type="text" value="0"  class="required form-control" required >
														</div>
													</div>
													
													<div class="col-sm-12 col-lg-12">
														<hr />
													</div>
													
													@foreach($componentNonFunctional as $cop)
													<div class="col-sm-12 col-lg-4">
														<div class="form-group">
															<label for="userName-2" class="block">{{$cop->header_title}}</label>
															<input id="userName-2" name="salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>" onkeyup="calculateSumGross()" type="text"  class="required form-control" required >
														</div>
													</div>
													@endforeach
													
                                            </div>
                                        </fieldset>
										
										<script>
										var total_gross=0;
										var salary_deduction=0;
										var salary_bank_transferable=0;
										function calculateSumGross()
										{
											<?php
											foreach($componentPlus as $cop):
											?>
												var salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>=$('input[name="salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>"]').val();
												console.log(salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>);
											<?php
											endforeach;
											?>
											
											<?php
											foreach($componentMinus as $cop):
											?>
												var salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>=$('input[name="salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>"]').val();
												console.log(salary_<?=strtolower(str_replace(' ','_',$cop->header_title))?>);
											<?php
											endforeach;
											?>
											
											total_gross=<?php $plus=0; foreach($componentPlus as $cop){ 
												if($plus==0)
												{
													echo "(salary_".strtolower(str_replace(' ','_',$cop->header_title))."-0)";
												}
												else
												{
													echo "+(salary_".strtolower(str_replace(' ','_',$cop->header_title))."-0)";
												}
												$plus++;
											} ?>;			

											salary_deduction=<?php
											$minparam=0;
											if(count($componentMinus)>0)
											{
												foreach($componentMinus as $cop){ 
													if($minparam==0)
													{
														echo "(salary_".strtolower(str_replace(' ','_',$cop->header_title))."-0)";
													}
													else
													{
														echo "+(salary_".strtolower(str_replace(' ','_',$cop->header_title))."-0)";
													}
													$minparam++;
												}
											}
											else
											{
												echo 0;
											}
											?>;
											
											salary_bank_transferable=(total_gross-salary_deduction);
											
											$('input[name="<?="salary_".strtolower(str_replace(' ','_',$componentGross->header_title))?>"]').val(total_gross);
											$('input[name="salary_deduction"]').val(salary_deduction);
											$('input[name="salary_bank_transferable"]').val(salary_bank_transferable);

											//salary_deduction
											//salary_bank_transferable
											console.log(total_gross);
											
										}	
											
										</script>
                                        
										<h3> Role &amp; Permission </h3>
                                        <fieldset>
                                            <div class="row">
                                                <div class="col-sm-8 col-lg-6">
                                                    <div class="form-group">
                                                        <label for="userName-2" class="block">Employee code<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <input id="userName-2" name="emp_code" type="text" value="{{$emp_Code}}"  class="required form-control" required >
                                                    </div>
                                                </div>

                                                <div class="col-sm-8 col-lg-6">
                                                    <div class="form-group">
                                                        <label for="email-2" class="block">Email<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <input id="email-2" name="email" type="email"  class="required form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-8 col-lg-6">
                                                    <div class="form-group">
                                                        <label for="password-2" class="block">Password<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <input id="password-2" name="password" type="password" class="form-control required"  required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-8 col-lg-6">
                                                    <div class="form-group">
                                                        <label for="confirm-2" class="block">Confirm Password<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <input id="confirm-2" name="confirm" type="password" class="form-control required" required>
                                                    </div>
                                                </div>



                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label for="l30">Assign a Role<span class="after" style="color:#EF5F5F"> *</span></label>
                                                        <select class="required form-control" name="assignRole" required>
                                                            <option value="">Select a Role</option>
                                                            @foreach($role as $row)
                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach

                                                        </select>
                                                    </div>
                                                </div>
												
												<div class="col-md-12"><code>Please select following check box only if an employee is employed in one company, but having weekend plan of another company.</code></div>
												<div class="form-check col-lg-6">
													<div class="form-group">
														<label>
															<input type="checkbox" value="1" id="weekend_policy_as_diff_company" name="weekend_policy_as_diff_company"> Apply Weekend Policy as Different Company</label>
													</div>
												</div>
												
												<div class="col-lg-3 weekend_policy_as_diff_company" style="display:none;">
													<div class="form-group">
														<label for="l30">Select Company</label>
														<select class="form-control" name="wp_company_id" required>
                                                            <option value="0" selected="selected" >Select Company</option>
                                                            @foreach($com as $row)
                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                            @endforeach
                                                        </select>
													</div>
												</div>
												
												<div class="col-lg-3 weekend_policy_as_diff_company" style="display:none;">
													<div class="form-group">
														<label for="l30">Effective From</label>
														<input value="{{ old('wp_effective_from') }}" type="text" name="wp_effective_from" class="form-control "  placeholder="Select Date" />
													</div>
												</div>




                                            </div>
                                        </fieldset>
                                    </form>
                                </section>
                            </div>
                            <!-- Form wizard with validation card end [Use Only This Block For Custom Form Wizard] -->
                            <div class="clearfix"></div>
                            @endif



                        </div>

                    </div>
                </div>
            </section>


        </div>
    </div>

</div>




@endsection

@section('extraFooter')



@include('ajax_include.company_wise_branch')
@include('ajax_include.department_wise_section')
@include('ajax_include.section_wise_designation')
@include('ajax_include.country_wise_city')

@if(isset($basic->company_id))
	
@else
	@include('ajax_include.company_wise_department')
@endif
<!--Please Add this scripts for custom-form-wizard-->
<link rel="stylesheet" type="text/css" href="{{url('vendors/custom-form-wizard/jquery.steps/demo/css/jquery.steps.css')}}">

<script src="{{url('vendors/jquery-validation/dist/jquery.validate.js')}}"></script>

<link rel="stylesheet" type="text/css" href="{{url('vendors/select2/dist/css/select2.min.css')}}">
<script src="{{url('vendors/select2/dist/js/select2.full.min.js')}}"></script> 
<script type="text/javascript">
$(document).ready(function() {
  $(".select-search").select2();
  
  $("#weekend_policy_as_diff_company").click(function(){
	  //alert('hehehe');
	  if(document.getElementById('weekend_policy_as_diff_company').checked==true)
	  {
		  $('.weekend_policy_as_diff_company').fadeIn();
	  }
	  else if(document.getElementById('weekend_policy_as_diff_company').checked==false)
	  {
		  $('.weekend_policy_as_diff_company').fadeOut();
	  }
  });
  
  
  
});


</script>

<!--End Custom Form Wizard-->




<?= MenuPageController::genarateKendoDatePicker(array("DOB", "effective_date","wp_effective_from", "pc_date", "pf_effective_from", "join_date")) ?>


<script>
                                                            $(document).ready(function () {

                                                                var max_fields_limit = 10; //set limit for maximum input fields
                                                                var x = 1; //initialize counter for text box
                                                                $('.add_more_buttonEdu').click(function (e) { //click event on add more fields button having class add_more_button
                                                                    e.preventDefault();
                                                                    if (x < max_fields_limit) { //check conditions
                                                                        x++; //counter increment
                                                                        $('.input_fields_containerEdu').append('<div class="row append">' +
                                                                                '<div class="col-lg-3 pull-left">' +
                                                                                '<div class="form-group">' +
                                                                                '<label for="l30">Certification Name</label>' +
                                                                                '<input type="text" name="certification[]" class="form-control" placeholder="Type Certification Name" id="l30">' +
                                                                                ' </div>' +
                                                                                '</div>' +
                                                                                '<div class="col-lg-3 pull-left">' +
                                                                                '<div class="form-group">' +
                                                                                '<label for="l30">Institute Name</label>' +
                                                                                '<input type="text" name="institute[]" class="form-control" placeholder="Type Institute Name" id="l30">' +
                                                                                '</div>' +
                                                                                '</div>' +
                                                                                '<div class="col-lg-3 pull-left">' +
                                                                                '<div class="form-group">' +
                                                                                '<label for="l30">Institute Address</label>' +
                                                                                '<input type="text" name="institute_add[]" class="form-control" placeholder="Type Institute Address" id="l30">' +
                                                                                '</div>' +
                                                                                '</div>' +
                                                                                '<div class="col-lg-3 pull-left">' +
                                                                                '<div class="form-group">' +
                                                                                '<label for="l30" class="col-xs-12">Start Date</label>' +
                                                                                //                                                                                            '<label class="input-group datepicker-only-init">' +
                                                                                '<input type="text" name="edu_s_date[]" class="form-control" placeholder="Select Start Date" />' +
                                                                                //                                                                                            '<span class="input-group-addon">' +
                                                                                //                                                                                            '<i class="icmn-calendar"></i>' +
                                                                                //                                                                                            '</span>' +
                                                                                //                                                                                            '</label>' +
                                                                                '</div>' +
                                                                                '</div>' +
                                                                                '<div class="col-lg-3 pull-left">' +
                                                                                '<div class="form-group">' +
                                                                                '<label for="l30" class="col-xs-12">End Date</label>' +
                                                                                '<label class="input-group datepicker-only-init">' +
                                                                                '<input type="text" name="edu_e_date[]" class="form-control" placeholder="Select Start Date" />' +
                                                                                '<span class="input-group-addon">' +
                                                                                '<i class="icmn-calendar"></i>' +
                                                                                '</span>' +
                                                                                '</label>' +
                                                                                '</div>' +
                                                                                '</div>' +
                                                                                '<div class="col-lg-3 pull-Left">' +
                                                                                ' <div class="form-group">' +
                                                                                '<label for="l30">Result</label>' +
                                                                                '<input type="text" name="result[]" class="form-control" placeholder="Type Result" id="l30">' +
                                                                                '</div>' +
                                                                                '</div>' +
                                                                                '<div class="col-lg-3 pull-Left">' +
                                                                                ' <div class="form-group">' +
                                                                                '<label for="l30">Attache Certificate</label>' +
                                                                                '<input type="file" name="edu_upload[]" class="form-control">' +
                                                                                '</div>' +
                                                                                '</div>' +
                                                                                '<a href="#" class="remove_fieldEdu btn btn-danger" style="margin-top:3% !important;max-height:40px"><i class="fa fa-close"></i></a>' +
                                                                                '</div>'); //add input field
                                                                    }
                                                                });
                                                                $('.input_fields_containerEdu').on("click", ".remove_fieldEdu", function (e) { //user click on remove text links
                                                                    e.preventDefault();
                                                                    $(this).parent('div').remove();
                                                                    x--;
                                                                });

                                                                $('.add_more_buttonJob').click(function (e) { //click event on add more fields button having class add_more_button
                                                                    e.preventDefault();
                                                                    if (x < max_fields_limit) { //check conditions
                                                                        x++; //counter increment
                                                                        $('.input_fields_containerJob').append('<div class="row append2">' +
                                                                                ' <div class="col-lg-3 pull-left">' +
                                                                                '   <div class="form-group">' +
                                                                                '   <label for="l30">Company Name</label>' +
                                                                                '      <input type="text" name="com_name[]" class="form-control" placeholder="Type Company Name" id="l30">' +
                                                                                '    </div>' +
                                                                                '  </div>' +
                                                                                '  <div class="col-lg-3 pull-left">' +
                                                                                '  <div class="form-group">' +
                                                                                '    <label for="l30">Company Address</label>' +
                                                                                '    <input type="text" name="com_address[]" class="form-control" placeholder="Type Address" id="l30">' +
                                                                                ' </div>' +
                                                                                '</div>' +
                                                                                '<div class="col-lg-3 pull-left">' +
                                                                                '  <div class="form-group">' +
                                                                                '     <label for="l30">Designation</label>' +
                                                                                '     <input type="text" name="com_desigantion[]" class="form-control" placeholder="Type Designation" id="l30">' +
                                                                                '   </div>' +
                                                                                ' </div>' +
                                                                                '<div class="col-lg-3 pull-left">' +
                                                                                ' <div class="form-group">' +
                                                                                '  <label for="l30" class="col-xs-12">Start Date</label>' +
                                                                                '  <label class="input-group datepicker-only-init">' +
                                                                                '    <input type="text" name="com_s_date[]" class="form-control" placeholder="Select End Date" />' +
                                                                                '  <span class="input-group-addon">' +
                                                                                '    <i class="icmn-calendar"></i>' +
                                                                                '     </span>' +
                                                                                '   </label>' +
                                                                                '  </div>' +
                                                                                '</div>' +
                                                                                '<div class="col-lg-3 pull-left">' +
                                                                                '<div class="form-group">' +
                                                                                ' <label for="l30" class="col-xs-12">End Date</label>' +
                                                                                //                                                                                            '    <label class="input-group datepicker-only-init">' +
                                                                                '       <input type="text" name="com_e_date[]" class="form-control" placeholder="Select End Date" />' +
                                                                                //                                                                                            '       <span class="input-group-addon">' +
                                                                                //                                                                                            '           <i class="icmn-calendar"></i>' +
                                                                                //                                                                                            '       </span>' +
                                                                                //                                                                                            '     </label>' +
                                                                                ' </div>' +
                                                                                ' </div>' +
                                                                                ' <div class="col-lg-3 pull-left">' +
                                                                                '  <div class="form-group">' +
                                                                                '    <label for="l30">Responsibility</label>' +
                                                                                '   <input type="text" name="com_responsibility[]" class="form-control" placeholder="Type Responsibility" id="l30">' +
                                                                                ' </div>' +
                                                                                ' </div>' +
                                                                                '<div class="col-lg-3 pull-left">' +
                                                                                '<div class="form-group">' +
                                                                                '<label for="l30">Attache Certificate</label>' +
                                                                                '<input type="file" name="com_upload[]" class="form-control">' +
                                                                                '</div>' +
                                                                                '</div>' +
                                                                                '<a href="#" class="remove_fieldJob btn btn-danger" style="margin-top:3% !important;max-height:40px"><i class="fa fa-close"></i></a>' +
                                                                                '</div>'); //add input field
                                                                    }
                                                                });
                                                                $('.input_fields_containerJob').on("click", ".remove_fieldJob", function (e) { //user click on remove text links
                                                                    e.preventDefault();
                                                                    $(this).parent('div').remove();
                                                                    x--;
                                                                });
                                                            });
</script>


<script type="text/javascript">

    //$(".is_company_prefix").fadeOut('fast');
    function prefixis()
    {
        //alert("working");

        if (document.getElementById("is_pf_eligibleI").checked == true)
        {
            var c = confirm("Please set provident Fund effective start date.");
            if (c)
            {
                document.getElementById("pf-date").style.display = "block";
                $(".pf_date").focus();
            }
            else
            {
                $("#is_pf_eligible").prop('checked', false);
                document.getElementById("pf-date").style.display = "none";
            }
        }
        else
        {

            document.getElementById("pf-date").style.display = "none";
        }

    }

    prefixis();


</script>
<script>

    $(document).ready(function () {

        $('input[name="edu_s_date[]"]').datetimepicker({
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
            console.log($(this).val());
        });

    });
    $(document).ready(function () {

        $('input[name="edu_e_date[]"]').datetimepicker({
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
            console.log($(this).val());
        });

    });
    $(document).ready(function () {

        $('input[name="com_s_date[]"]').datetimepicker({
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
            console.log($(this).val());
        });

    });
    $(document).ready(function () {

        $('input[name="com_e_date[]"]').datetimepicker({
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
            console.log($(this).val());
        });

    });

</script>
<link rel="stylesheet" type="text/css" href="{{url('vendors/dropify/dist/css/dropify.min.css')}}">
<script src="{{url('vendors/dropify/dist/js/dropify.min.js')}}"></script>
<script>
    $(function () {

        $('.dropify').dropify();

    });
</script>
@endsection
