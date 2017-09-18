<?php
if (isset($data)) {
    $pageinfo = array("Edit Add Employee Settings", "Edit Employee Record", "", "SUL");
} else {
    $pageinfo = array("Employee Settings", "Add Employee Record", "", "SUL");
}
?>

@extends('layout.master') @section('content') @include('include.coreBarcum')

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
                                    @if(isset($data))
                                    <!--                                    <form class="wizard-form" id="example-advanced-form" action="#">
                                                                            <h3> Registration </h3>
                                                                            <fieldset>
                                                                                <div class="form-group row">
                                                                                    <div class="col-sm-4 col-lg-2">
                                                                                        <label for="userName-2" class="block">User name *</label>
                                                                                    </div>
                                                                                    <div class="col-sm-8 col-lg-10">
                                                                                        <input id="userName-2" name="userName" type="text" value="a" class="required form-control">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-sm-4 col-lg-2">
                                                                                        <label for="email-2" class="block">Email *</label>
                                                                                    </div>
                                                                                    <div class="col-sm-8 col-lg-10">
                                                                                        <input id="email-2" name="email" type="email" value="a@g" class="required form-control">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-sm-4 col-lg-2">
                                                                                        <label for="password-2" class="block">Password *</label>
                                                                                    </div>
                                                                                    <div class="col-sm-8 col-lg-10">
                                                                                        <input id="password-2" name="password" type="password" value="a" class="form-control required">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-sm-4 col-lg-2">
                                                                                        <label for="confirm-2" class="block">Confirm Password *</label>
                                                                                    </div>
                                                                                    <div class="col-sm-8 col-lg-10">
                                                                                        <input id="confirm-2" name="confirm" type="password" value="a" value="a" class="form-control required">
                                                                                    </div>
                                                                                </div>
                                                                            </fieldset>
                                                                            <h3> Basic information </h3>
                                                                            <fieldset>
                                                                                <div class="col-lg-6 pull-left">
                                                                                    <div class="form-group">
                                                                                        <label for="l30">First Name</label>
                                                                                        <input type="text" name="fname" class="form-control" value="a" placeholder="Type First Name" id="l30">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-6 pull-right">
                                                                                    <div class="form-group">
                                                                                        <label for="l30">Last Name</label>
                                                                                        <input type="text" name="lname" class="form-control" value="a" placeholder="Type Last Name" id="l30">
                                                                                    </div>
                                                                                </div>
                                    
                                                                                <div class="col-lg-6 pull-left">
                                                                                    <div class="form-group">
                                                                                        <label for="l30">Marital Status</label>
                                                                                        <select class="form-control" name="marital_id">
                                                                                            <option value="d">Select Marital Status</option>
                                    
                                                                                            @foreach($marital_status as $row)
                                                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                                                            @endforeach
                                    
                                    
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-6 pull-right">
                                                                                    <div class="form-group">
                                                                                        <label for="l30">Gender</label>
                                                                                        <select class="form-control" name="gender_id">
                                                                                            <option value="d">Select Gender</option>
                                                                                            @foreach($gender as $row)
                                                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                                                            @endforeach
                                    
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                    
                                                                                <div class="col-lg-6 pull-left">
                                                                                    <div class="form-group">
                                                                                        <label for="l30">Blood Group</label>
                                                                                        <select class="form-control" name="blood_id">
                                                                                            <option value="d">Select Blood Group</option>
                                    
                                                                                            @foreach($bloodGroup as $row)
                                                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                                                            @endforeach
                                    
                                    
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                    
                                                                                <div class="col-lg-6 pull-right">
                                                                                    <div class="form-group">
                                                                                        <label for="l30" class="col-md-12">Date of Birth</label>
                                                                                        <label class="input-group datepicker-only-init">
                                                                                            <input type="text" name="DOB" class="form-control" value="a" placeholder="Type Date of Birth" />
                                                                                            <span class="input-group-addon">
                                                                                                <i class="icmn-calendar"></i>
                                                                                            </span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                    
                                                                                <div class="col-lg-6 pull-left">
                                                                                    <div class="form-group">
                                                                                        <label for="l30" class="col-md-12">Country</label>
                                                                                        <select class="form-control" name="country_id">
                                                                                            <option value="d">Select Country</option>
                                                                                            @foreach($country as $row)
                                                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                                                            @endforeach
                                    
                                    
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-6 pull-right">
                                                                                    <div class="form-group">
                                                                                        <label for="l30" class="col-md-12">City</label>
                                                                                        <select class="form-control" name="city_id">
                                                                                            <option value="d">Select City</option>
                                    
                                                                                            @foreach($city as $row)
                                                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                                                            @endforeach
                                    
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-6 pull-left">
                                                                                    <div class="form-group">
                                                                                        <label for="l30">Address</label>
                                                                                        <input type="text" name="address" class="form-control" value="a" placeholder="Type Day Title" id="l30">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-6 pull-right">
                                                                                    <div class="form-group">
                                                                                        <label for="l30">Phone</label>
                                                                                        <input type="text" name="phone" class="form-control" value="a" placeholder="Type Day Type Id" id="l30">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-6 pull-left">
                                                                                    <div class="form-group">
                                                                                        <label for="l30">Email</label>
                                                                                        <input type="text" name="email" class="form-control" value="a" placeholder="Type Day Type Id" id="l30">
                                                                                    </div>
                                                                                </div>
                                    
                                                                            </fieldset>
                                                                            <h3> Company </h3>
                                                                            <fieldset>
                                                                                <div class="col-lg-6 pull-left">
                                                                                    <div class="form-group">
                                                                                        <label for="l30" class="col-md-12">Company</label>
                                                                                        <select class="form-control" name="company_id">
                                                                                            <option>Select Company</option>
                                    
                                                                                            @foreach($com as $row)
                                                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                                                            @endforeach
                                    
                                    
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-6 pull-right">
                                                                                    <div class="form-group">
                                                                                        <label for="l30" class="col-md-12">Department</label>
                                                                                        <select class="form-control" name="department_id">
                                                                                            <option>Select Department</option>
                                    
                                                                                            @foreach($department as $row)
                                                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                                                            @endforeach
                                    
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-6 pull-left">
                                                                                    <div class="form-group">
                                                                                        <label for="l30" class="col-md-12">Section</label>
                                                                                        <select class="form-control" name="section_id">
                                                                                            <option>Select Section</option>
                                    
                                                                                            @foreach($section as $row)
                                                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                                                            @endforeach
                                    
                                    
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-6 pull-right">
                                                                                    <div class="form-group">
                                                                                        <label for="l30" class="col-md-12">Designation</label>
                                                                                        <select class="form-control" name="designation_id">
                                                                                            <option>Select Designation</option>
                                    
                                                                                            @foreach($designation as $row)
                                                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                                                            @endforeach
                                    
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-6 pull-left">
                                                                                    <div class="form-group">
                                                                                        <label for="l30" class="col-md-12">Supervisor</label>
                                                                                        <select class="form-control" name="supervisor">
                                                                                            <option>Select Supervisor</option>
                                                                                            @foreach($branch as $row)
                                                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                                                            @endforeach
                                    
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                              
                                                                                <div class="col-lg-6 pull-left">
                                                                                    <div class="form-group">
                                                                                        <label for="l30" class="col-md-12">Job Location</label>
                                                                                        <select class="form-control" name="branch_id">
                                                                                            <option>Select Job Location</option>
                                    
                                                                                            @foreach($branch as $row)
                                                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                                                            @endforeach
                                    
                                    
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-6 pull-right">
                                                                                    <div class="form-group">
                                                                                        <label for="l30" class="col-md-12">Effective From</label>
                                                                                        <label class="input-group datepicker-only-init">
                                                                                            <input type="text" value="a" name="effective_date" class="form-control" placeholder="Select Date" />
                                                                                            <span class="input-group-addon">
                                                                                                <i class="icmn-calendar"></i>
                                                                                            </span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </fieldset>
                                                                            <h3> Job experience </h3>
                                                                            <fieldset>
                                                                                <div class="input_fields_containerJob col-lg-12">
                                                                                    <div class="row append2">
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30">Company Name</label>
                                                                                                <input type="text" name="com_name[]" class="form-control" placeholder="Type Company Name" id="l30">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30">Company Address</label>
                                                                                                <input type="text" name="com_address[]" class="form-control" placeholder="Type Address" id="l30">
                                                                                            </div>
                                                                                        </div>
                                    
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30">Designation</label>
                                                                                                <input type="text" name="com_desigantion[]" class="form-control" placeholder="Type Designation" id="l30">
                                                                                            </div>
                                                                                        </div>
                                    
                                    
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30" class="col-xs-12">Start Date</label>
                                                                                                <label class="input-group datepicker-only-init">
                                                                                                    <input type="text" name="com_s_date[]" class="form-control" placeholder="Select End Date" />
                                                                                                    <span class="input-group-addon">
                                                                                                        <i class="icmn-calendar"></i>
                                                                                                    </span>
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30" class="col-xs-12">End Date</label>
                                                                                                <label class="input-group datepicker-only-init">
                                                                                                    <input type="text" name="com_e_date[]" class="form-control" placeholder="Select End Date" />
                                                                                                    <span class="input-group-addon">
                                                                                                        <i class="icmn-calendar"></i>
                                                                                                    </span>
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30">Responsibility</label>
                                                                                                <input type="text" name="com_responsibility[]" class="form-control" placeholder="Type Responsibility" id="l30">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30">Attache Certificate</label>
                                                                                                <input type="file" name="com_upload[]" class="form-control">
                                                                                            </div>
                                                                                        </div>
                                    
                                                                                    </div>
                                    
                                                                                    <a href="#" class="btn btn-success add_more_buttonJob">Add More</a>
                                                                                </div>
                                                                            </fieldset>
                                                                            <h3> Education Info </h3>
                                                                            <fieldset>
                                                                                <div class="input_fields_containerJob col-lg-12">
                                                                                    <div class="row append2">
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30">Company Name</label>
                                                                                                <input type="text" name="com_name[]" class="form-control" placeholder="Type Company Name" id="l30">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30">Company Address</label>
                                                                                                <input type="text" name="com_address[]" class="form-control" placeholder="Type Address" id="l30">
                                                                                            </div>
                                                                                        </div>
                                    
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30">Designation</label>
                                                                                                <input type="text" name="com_desigantion[]" class="form-control" placeholder="Type Designation" id="l30">
                                                                                            </div>
                                                                                        </div>
                                    
                                    
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30" class="col-xs-12">Start Date</label>
                                                                                                <label class="input-group datepicker-only-init">
                                                                                                    <input type="text" name="com_s_date[]" class="form-control" placeholder="Select End Date" />
                                                                                                    <span class="input-group-addon">
                                                                                                        <i class="icmn-calendar"></i>
                                                                                                    </span>
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30" class="col-xs-12">End Date</label>
                                                                                                <label class="input-group datepicker-only-init">
                                                                                                    <input type="text" name="com_e_date[]" class="form-control" placeholder="Select End Date" />
                                                                                                    <span class="input-group-addon">
                                                                                                        <i class="icmn-calendar"></i>
                                                                                                    </span>
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30">Responsibility</label>
                                                                                                <input type="text" name="com_responsibility[]" class="form-control" placeholder="Type Responsibility" id="l30">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30">Attache Certificate</label>
                                                                                                <input type="file" name="com_upload[]" class="form-control">
                                                                                            </div>
                                                                                        </div>
                                    
                                                                                    </div>
                                    
                                                                                    <a href="#" class="btn btn-success add_more_buttonJob">Add More</a>
                                                                                </div>
                                                                            </fieldset>
                                                                            <h3> Education Info </h3>
                                                                            <fieldset>
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
                                                                                                <label class="input-group datepicker-only-init">
                                                                                                    <input type="text" name="start_date[]" class="form-control" placeholder="Select End Date" />
                                                                                                    <span class="input-group-addon">
                                                                                                        <i class="icmn-calendar"></i>
                                                                                                    </span>
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-lg-3 pull-left">
                                                                                            <div class="form-group">
                                                                                                <label for="l30" class="col-xs-12">End Date</label>
                                                                                                <label class="input-group datepicker-only-init">
                                                                                                    <input type="text" name="end_date[]" class="form-control" placeholder="Select End Date" />
                                                                                                    <span class="input-group-addon">
                                                                                                        <i class="icmn-calendar"></i>
                                                                                                    </span>
                                                                                                </label>
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
                                    
                                                                                    <a href="#" class="btn btn-success add_more_buttonEdu">Add More</a>
                                                                                </div>
                                                                            </fieldset>
                                                                            <h3> Others Info </h3>
                                                                            <fieldset>
                                                                                <div class="col-lg-6">
                                                                                    <div class="form-group">
                                                                                        <label for="l30" class="col-md-12">Proposed Confirmation Date</label>
                                                                                        <label class="input-group datepicker-only-init">
                                                                                            <input type="text"  class="form-control" placeholder="Select Date" />
                                                                                            <span class="input-group-addon">
                                                                                                <i class="icmn-calendar"></i>
                                                                                            </span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                    
                                                                                <div class="col-lg-6">
                                                                                    <div class="form-group">
                                                                                        <label for="l30" class="col-md-12">In Replacement of</label>
                                                                                        <select class="form-control" name="inReplacement">
                                                                                            <option value="">Select Employee</option>
                                                                                            @if(isset($coany))
                                                                                            @foreach($comny as $row)
                                                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                                                            @endforeach
                                                                                            @endif
                                    
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-6">
                                                                                    <div class="form-group">
                                                                                        <label for="l30" class="col-md-12">Assign a Role</label>
                                                                                        <select class="form-control" name="assignRole">
                                                                                            <option value="">Select a Role</option>
                                                                                            @if(isset($compny))
                                                                                            @foreach($compay as $row)
                                                                                            <option value="{{$row->id}}">{{$row->name}}</option>
                                                                                            @endforeach
                                                                                            @endif
                                    
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                    
                                                                                <div class="col-lg-6">
                                                                                    <div class="form-group">
                                                                                        <label for="l30" class="col-md-12">PF Effective From</label>
                                                                                        <label class="input-group datepicker-only-init">
                                                                                            <input type="text" name="pf_effective_from" class="form-control" placeholder="Select Date" />
                                                                                            <span class="input-group-addon">
                                                                                                <i class="icmn-calendar"></i>
                                                                                            </span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-check col-lg-6">
                                                                                    <label class="form-check-label">
                                                                                        <input class="form-check-input" name="pf_eligible" type="checkbox" checked="" value="">
                                                                                        Provident Fund Eligible
                                                                                    </label>
                                                                                </div>
                                                                            </fieldset>
                                                                        </form>-->
                                    @else
                                    <form class="wizard-form"  enctype="multipart/form-data"  id="example-advanced-form" name="Employeeinfo" action="{{url('Employee/Employeeinfo/Add/')}}" method="post">
                                        {{csrf_field()}}
                                        <h3> Registration </h3>
                                        <fieldset>
                                            <div class="form-group row">
                                                <div class="col-sm-4 col-lg-2">
                                                    <label for="userName-2" class="block">Employee code *</label>
                                                </div>
                                                <div class="col-sm-8 col-lg-10">
                                                    <input id="userName-2" name="emp_code" type="text"  class="required form-control">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-4 col-lg-2">
                                                    <label for="email-2" class="block">Email *</label>
                                                </div>
                                                <div class="col-sm-8 col-lg-10">
                                                    <input id="email-2" name="email" type="email"  class="required form-control">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-4 col-lg-2">
                                                    <label for="password-2" class="block">Password *</label>
                                                </div>
                                                <div class="col-sm-8 col-lg-10">
                                                    <input id="password-2" name="password" type="password" class="form-control required">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-4 col-lg-2">
                                                    <label for="confirm-2" class="block">Confirm Password *</label>
                                                </div>
                                                <div class="col-sm-8 col-lg-10">
                                                    <input id="confirm-2" name="confirm" type="password" class="form-control required">
                                                </div>
                                            </div>
                                        </fieldset>
                                        <h3> Basic info </h3>
                                        <fieldset>
                                            <div class="col-lg-6 pull-left">
                                                <div class="form-group">
                                                    <label for="l30">First Name</label>
                                                    <input type="text" name="fname" class="form-control"  placeholder="Type First Name" id="l30">
                                                </div>
                                            </div>
                                            <div class="col-lg-6 pull-left">
                                                <div class="form-group">
                                                    <label for="l30">Last Name</label>
                                                    <input type="text" name="lname" class="form-control" placeholder="Type Last Name" id="l30">
                                                </div>
                                            </div>

                                            <div class="col-lg-3 pull-left">
                                                <div class="form-group">
                                                    <label for="l30">Marital Status</label>
                                                    <select class="form-control" name="marital_id">
                                                        <option>Select Marital Status</option>

                                                        @foreach($marital_status as $row)
                                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                                        @endforeach


                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 pull-left">
                                                <div class="form-group">
                                                    <label for="l30">Gender</label>
                                                    <select class="form-control" name="gender_id">
                                                        <option>Select Gender</option>
                                                        @foreach($gender as $row)
                                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-3 pull-left">
                                                <div class="form-group">
                                                    <label for="l30">Blood Group</label>
                                                    <select class="form-control" name="blood_id">
                                                        <option>Select Blood Group</option>

                                                        @foreach($bloodGroup as $row)
                                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                                        @endforeach


                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-3 pull-left">
                                                <div class="form-group">
                                                    <label for="l30" class="col-md-12">Date of Birth</label>
                                                    <label class="input-group datepicker-only-init">
                                                        <input type="text" name="DOB" class="form-control" placeholder="Type Date of Birth" />
                                                        <span class="input-group-addon">
                                                            <i class="icmn-calendar"></i>
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-lg-3 pull-left">
                                                <div class="form-group">
                                                    <label for="l30" class="col-md-12">Country</label>
                                                    <select class="form-control" name="country_id">
                                                        <option>Select Country</option>
                                                        @foreach($country as $row)
                                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                                        @endforeach


                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 pull-left">
                                                <div class="form-group">
                                                    <label for="l30" class="col-md-12">City</label>
                                                    <select class="form-control" name="city_id">
                                                        <option>Select City</option>

                                                        @foreach($city as $row)
                                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 pull-left">
                                                <div class="form-group">
                                                    <label for="l30">Address</label>
                                                    <input type="text" name="address" class="form-control"  placeholder="Type Day Title" id="l30">
                                                </div>
                                            </div>
                                            <div class="col-lg-6 pull-left">
                                                <div class="form-group">
                                                    <label for="l30">Phone</label>
                                                    <input type="text" name="phone" class="form-control" placeholder="Type Day Type Id" id="l30">
                                                </div>
                                            </div>
                                            <div class="col-lg-6 pull-left">
                                                <div class="form-group">
                                                    <label for="l30">Email</label>
                                                    <input type="text" name="email" class="form-control"  placeholder="Type Day Type Id" id="l30">
                                                </div>
                                            </div>

                                        </fieldset>
                                        <h3> Company </h3>
                                        <fieldset>
                                            <div class="col-lg-6 pull-left">
                                                <div class="form-group">
                                                    <label for="l30" class="col-md-12">Company</label>
                                                    <select class="form-control" name="company_id">
                                                        <option>Select Company</option>
                                                        @foreach($com as $row)
                                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>

                                 
                                            <div class="col-lg-6 pull-right">
                                                <div class="form-group">
                                                    <label for="l30" class="col-md-12">Department</label>
                                                    <select class="form-control" name="department_id">
                                                        <option>Select Department</option>

                                                        @foreach($department as $row)
                                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                           
                                            <div class="col-lg-6 pull-left">
                                                <div class="form-group">
                                                    <label for="l30" class="col-md-12">Section</label>
                                                    <select class="form-control" name="section_id">
                                                        <option>Select Section</option>
                                                        @foreach($com as $row)
                                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 pull-right">
                                                <div class="form-group">
                                                    <label for="l30" class="col-md-12">Designation</label>
                                                    <select class="form-control" name="designation_id">
                                                        <option>Select Designation</option>

                                                        @foreach($designation as $row)
                                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 pull-left">
                                                <div class="form-group">
                                                    <label for="l3" class="col-md-12">Staff Grade</label>
                                                    <select class="form-control" name="staffgrade_id">
                                                        <option>Select Staff Grade</option>
                                                        @foreach($staff as $row)
                                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 pull-right">
                                                <div class="form-group">
                                                    <label for="l30" class="col-md-12">Supervisor</label>
                                                    <select class="form-control" name="supervisor">
                                                        <option>Select Supervisor</option>
                                                        @foreach($supervisor as $row)
                                                        <option value="{{$row->id}}">{{$row->first_name}} {{$row->last_name}}</option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-6 pull-left">
                                                <div class="form-group">
                                                    <label for="l30" class="col-md-12">Job Location</label>
                                                    <select class="form-control" name="branch_id">
                                                        <option>Select Job Location</option>

                                                        @foreach($branch as $row)
                                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                                        @endforeach


                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 pull-right">
                                                <div class="form-group">
                                                    <label for="l30" class="col-md-12">Effective From</label>
                                                    <label class="input-group datepicker-only-init">
                                                        <input type="text" name="effective_date" class="form-control" placeholder="Select Date" />
                                                        <span class="input-group-addon">
                                                            <i class="icmn-calendar"></i>
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </fieldset>
                                        <h3> Job experience </h3>
                                        <fieldset>
                                            <div class="input_fields_containerJob col-lg-12">
                                                <div class="row append2">
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Company Name</label>
                                                            <input type="text" name="com_name[]" class="form-control" placeholder="Type Company Name" id="l30">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Company Address</label>
                                                            <input type="text" name="com_address[]" class="form-control" placeholder="Type Address" id="l30">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Designation</label>
                                                            <input type="text" name="com_desigantion[]" class="form-control" placeholder="Type Designation" id="l30">
                                                        </div>
                                                    </div>


                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30" class="col-xs-12">Start Date</label>
                                                            <label class="input-group datepicker-only-init">
                                                                <input type="text" name="com_s_date[]" class="form-control" placeholder="Select End Date" />
                                                                <span class="input-group-addon">
                                                                    <i class="icmn-calendar"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30" class="col-xs-12">End Date</label>
                                                            <label class="input-group datepicker-only-init">
                                                                <input type="text" name="com_e_date[]" class="form-control" placeholder="Select End Date" />
                                                                <span class="input-group-addon">
                                                                    <i class="icmn-calendar"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Responsibility</label>
                                                            <input type="text" name="com_responsibility[]" class="form-control" placeholder="Type Responsibility" id="l30">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30">Attache Certificate</label>
                                                            <input type="file" name="com_upload[]" class="form-control">
                                                        </div>
                                                    </div>

                                                </div>

                                                <a href="#" class="btn btn-success add_more_buttonJob">Add More</a>
                                            </div>
                                        </fieldset>

                                        <h3> Education Info </h3>
                                        <fieldset>
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
                                                            <label class="input-group datepicker-only-init">
                                                                <input type="text" name="start_date[]" class="form-control" placeholder="Select End Date" />
                                                                <span class="input-group-addon">
                                                                    <i class="icmn-calendar"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 pull-left">
                                                        <div class="form-group">
                                                            <label for="l30" class="col-xs-12">End Date</label>
                                                            <label class="input-group datepicker-only-init">
                                                                <input type="text" name="end_date[]" class="form-control" placeholder="Select End Date" />
                                                                <span class="input-group-addon">
                                                                    <i class="icmn-calendar"></i>
                                                                </span>
                                                            </label>
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

                                                <a href="#" class="btn btn-success add_more_buttonEdu">Add More</a>
                                            </div>
                                        </fieldset>
                                        <h3> Others Info </h3>
                                        <fieldset>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="l30" class="col-md-12">Proposed Confirmation Date</label>
                                                    <label class="input-group datepicker-only-init">
                                                        <input type="text" name="pc_date" class="form-control" placeholder="Select Date" />
                                                        <span class="input-group-addon">
                                                            <i class="icmn-calendar"></i>
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="l30" class="col-md-12">In Replacement of</label>
                                                    <select class="form-control" name="inReplacement">
                                                        <option value="">Select Employee</option>

                                                        @foreach($inRplace as $row)
                                                        <option value="{{$row->id}}">{{$row->first_name}} {{$row->last_name}}</option>
                                                        @endforeach


                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="l30" class="col-md-12">Assign a Role</label>
                                                    <select class="form-control" name="assignRole">
                                                        <option value="">Select a Role</option>

                                                        @foreach($role as $row)
                                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                                        @endforeach


                                                    </select>
                                                </div>
                                            </div>


                                            <div class="form-check col-lg-6">
                                                <div class="form-group">
                                                    <label onclick="prefixis()">
                                                        <input type="checkbox" value="1" id="is_pf_eligible" name="pf_eligible"> Provident Fund Eligible</label>
                                                </div>
                                            </div>
                                            <div class="col-lg-6" id="pf-date">
                                                <div class="form-group">
                                                    <label for="l30" class="col-md-12">PF Effective From</label>
                                                    <label class="input-group datepicker-only-init">
                                                        <input type="text" name="pf_effective_from" class="form-control " id="show" placeholder="Select Date" />
                                                        <span class="input-group-addon">
                                                            <i class="icmn-calendar"></i>
                                                        </span>
                                                    </label>
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
<script>
    $(function () {

        $('.datepicker-init').datetimepicker({
            widgetPositioning: {
                horizontal: 'left'
            },
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down",
                previous: 'fa fa-arrow-left',
                next: 'fa fa-arrow-right'
            }
        });

        $('.datepicker-only-init').datetimepicker({
            widgetPositioning: {
                horizontal: 'left'
            },
            icons: {
                //time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down",
                previous: 'fa fa-arrow-left',
                next: 'fa fa-arrow-right'
            },
            //format: 'LL'
            format: 'YYYY-MM-DD'
        });

        $('.timepicker-init').datetimepicker({
            widgetPositioning: {
                horizontal: 'left'
            },
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down",
                previous: 'fa fa-arrow-left',
                next: 'fa fa-arrow-right'
            },
            format: 'LT'
        });

        $('.datepicker-inline-init').datetimepicker({
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down",
                previous: 'fa fa-arrow-left',
                next: 'fa fa-arrow-right'
            },
            inline: true,
            sideBySide: false
        });

        $('.timepicker-inline-init').datetimepicker({
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down",
                previous: 'fa fa-arrow-left',
                next: 'fa fa-arrow-right'
            },
            format: 'LT',
            inline: true,
            sideBySide: false
        });

    })
</script>




@endsection 
@section('extraHeader')

<link rel="stylesheet" type="text/css" href="{{url('vendors/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css')}}"> 
@endsection 
@section('extraFooter')
<script src="{{url('vendors/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js')}}"></script>

<!--Please Add this scripts for custom-form-wizard-->
<link rel="stylesheet" type="text/css" href="{{url('vendors/custom-form-wizard/jquery.steps/demo/css/jquery.steps.css')}}">
<script src="{{url('vendors/custom-form-wizard/form-wizard.js')}}"></script>
<script src="{{url('vendors/custom-form-wizard/jquery.steps/build/jquery.steps.js')}}"></script>
<script src="{{url('vendors/jquery-validation/dist/jquery.validate.js')}}"></script>
<!--End Custom Form Wizard-->

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
                        '<label class="input-group datepicker-only-init">' +
                        '<input type="text" name="start_date[]" class="form-control" placeholder="Select Start Date" />' +
                        '<span class="input-group-addon">' +
                        '<i class="icmn-calendar"></i>' +
                        '</span>' +
                        '</label>' +
                        '</div>' +
                        '</div>' +
                        '<div class="col-lg-3 pull-left">' +
                        '<div class="form-group">' +
                        '<label for="l30" class="col-xs-12">End Date</label>' +
                        '<label class="input-group datepicker-only-init">' +
                        '<input type="text" name="end_date[]" class="form-control" placeholder="Select Start Date" />' +
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
                        '    <label class="input-group datepicker-only-init">' +
                        '       <input type="text" name="com_e_date[]" class="form-control" placeholder="Select End Date" />' +
                        '       <span class="input-group-addon">' +
                        '           <i class="icmn-calendar"></i>' +
                        '       </span>' +
                        '     </label>' +
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

        if (document.getElementById("is_pf_eligible").checked == true)
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
@endsection