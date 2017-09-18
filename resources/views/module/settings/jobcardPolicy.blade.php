<?php
if (isset($data)) {
    $pageinfo = array("Edit Jobcard Data Policy Settings", "Edit Jobcard Data Policy Record", "", "SUL");
} else {
    $pageinfo = array("Jobcard Data Policy Settings", "Add Jobcard Data Policy Record", "", "SUL");
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
                            <form enctype="multipart/form-data" name="Company" action="{{url('Settings/AttendanceJobcardPolicy/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}

                                <div class="row">
                                    @if(empty($logged_emp_com))
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="l30">Company Name</label>
                                            <select class="required form-control" name="company_id" required>
                                                @if(isset($company))
                                                @foreach($company as $row)
                                                <option <?php if ($data->company_id == $row->id) { ?> selected="selected" <?php } ?> value="{{$row->id}}">{{$row->name}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    @else
                                    <input type="hidden" name="company_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="card-header">
                                        <span class="cat__core__title">
                                            <strong><input type="checkbox" 
                                                           @if($data->is_admin_data_show_policy==1)
                                                           checked="checked"  
                                                           @endif        
                                                           name="is_admin_data_show_policy" placeholder="Type Policy Title" value="1" id="is_admin_data_show_policy"> 
                                                           Is Admin Data Show Policy Active</strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="col-lg-3 is_admin_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">Addition Deduction </label>
                                            <select name="admin_addition_deduction" id="admin_addition_deduction" class="form-control">
                                                <option value="">Please Select +-</option>
                                                <option 
                                                    @if($data->admin_addition_deduction=="+")
                                                    selected="selected"   
                                                    @endif    
                                                    value="+">Addition (+)</option>
                                                <option 
                                                    @if($data->admin_addition_deduction=="-")
                                                    selected="selected"   
                                                    @endif   
                                                    value="-">Deduction (-)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 is_admin_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">With Intime </label>
                                            <input value="{{$data->admin_with_intime}}"  type="text" name="admin_with_intime" id="admin_with_intime" class="form-control timepicker-init">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 is_admin_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">With Outime</label>
                                            <input value="{{$data->admin_with_outime}}"   type="text" name="admin_with_outime"  class="form-control timepicker-init" id="l30">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 is_admin_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30" style="margin-top:40px;">
                                                <input type="checkbox" 
                                                       @if($data->is_admin_max_ot_fixed==1)
                                                       checked="checked"  
                                                       @endif     
                                                       name="is_admin_max_ot_fixed" placeholder="Type Policy Title"  value="1" id="is_admin_max_ot_fixed"> 
                                                       Is Max OT Fixed 
                                            </label>

                                        </div>
                                    </div>
                                    <div class="col-lg-2 is_admin_data_show_policy is_admin_max_ot_fixed">
                                        <div class="form-group">
                                            <label for="l30">Max OT Hour</label>
                                            <input  value="{{$data->admin_max_ot_hour}}"  type="text" name="admin_max_ot_hour" class="form-control timepicker-init" id="admin_max_ot_hour">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 is_admin_data_show_policy is_admin_max_ot_fixed">
                                        <div class="form-group">
                                            <label for="l30" style="margin-top:40px;">
                                                <input type="checkbox" 
                                                       @if($data->is_admin_ot_adjust_with_outtime==1)
                                                       checked="checked"  
                                                       @endif 
                                                       name="is_admin_ot_adjust_with_outtime"  value="1" placeholder="Type Policy Title" id="is_admin_ot_adjust_with_outtime"> 
                                                       Is Max OT Adjust With Outtime 
                                            </label>
                                        </div>
                                    </div>
                                </div>



                                <div class="row">
                                    <div class="card-header">
                                        <span class="cat__core__title">
                                            <strong>
                                                <input type="checkbox" 
                                                       @if($data->is_user_data_show_policy==1)
                                                       checked="checked"  
                                                       @endif 
                                                       name="is_user_data_show_policy" placeholder="Type Policy Title"  value="1" id="is_user_data_show_policy"> 
                                                       Is User Data Show Policy Active </strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">


                                    <div class="col-lg-3 is_user_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">Addition Deduction </label>
                                            <select name="user_addition_deduction" id="user_addition_deduction" class="form-control">
                                                <option value="">Please Select +-</option>
                                                <option 
                                                    @if($data->user_addition_deduction=="+")
                                                    selected="selected"   
                                                    @endif   
                                                    value="+">Addition (+)</option>
                                                <option 
                                                    @if($data->user_addition_deduction=="-")
                                                    selected="selected"   
                                                    @endif   
                                                    value="-">Deduction (-)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 is_user_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">With Intime </label>
                                            <input type="text"  value="{{$data->user_with_intime}}"  name="user_with_intime" id="user_with_intime" class="form-control timepicker-init">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 is_user_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">With Outime</label>
                                            <input type="text" value="{{$data->user_with_outime}}" name="user_with_outime"  class="form-control timepicker-init" id="l30">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 is_user_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30" style="margin-top:40px;">
                                                <input type="checkbox" 
                                                       @if($data->is_user_max_ot_fixed==1)
                                                       checked="checked"  
                                                       @endif 

                                                       name="is_user_max_ot_fixed" placeholder="Type Policy Title"  value="1" id="is_user_max_ot_fixed"> 
                                                       Is Max OT Fixed 
                                            </label>

                                        </div>
                                    </div>
                                    <div class="col-lg-2 is_user_data_show_policy is_user_max_ot_fixed">
                                        <div class="form-group">
                                            <label for="l30">Max OT Hour</label>
                                            <input 
                                                value="{{$data->user_max_ot_hour}}" 
                                                type="text" name="user_max_ot_hour" class="form-control timepicker-init" id="user_max_ot_hour">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 is_user_data_show_policy is_user_max_ot_fixed">
                                        <div class="form-group">
                                            <label for="l30" style="margin-top:40px;">
                                                <input type="checkbox" 
                                                       @if($data->is_user_ot_adjust_with_outtime==1)
                                                       checked="checked"  
                                                       @endif 
                                                       name="is_user_ot_adjust_with_outtime" placeholder="Type Policy Title"  value="1" id="is_user_ot_adjust_with_outtime"> 
                                                       Is Max OT Adjust With Outtime 
                                            </label>
                                        </div>
                                    </div>

                                </div>



                                <div class="row">
                                    <div class="card-header">
                                        <span class="cat__core__title">
                                            <strong>
                                                <input type="checkbox" 
                                                       @if($data->is_audit_data_show_policy==1)
                                                       checked="checked"  
                                                       @endif 
                                                       name="is_audit_data_show_policy" placeholder="Type Policy Title"  value="1" id="is_audit_data_show_policy"> 
                                                       Is Audit Data Show Policy Active 
                                            </strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">



                                    <div class="col-lg-3 is_audit_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">Addition Deduction </label>
                                            <select name="audit_addition_deduction" id="audit_addition_deduction" class="form-control">
                                                <option value="">Please Select +-</option>
                                                <option 
                                                    @if($data->audit_addition_deduction=="+")
                                                    selected="selected"  
                                                    @endif 
                                                    value="+">Addition (+)</option>
                                                <option 
                                                    @if($data->audit_addition_deduction=="-")
                                                    selected="selected"  
                                                    @endif 
                                                    value="-">Deduction (-)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 is_audit_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">With Intime </label>
                                            <input value="{{$data->audit_with_intime}}" type="text" name="audit_with_intime" id="audit_with_intime" class="form-control timepicker-init">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 is_audit_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">With Outime</label>
                                            <input value="{{$data->audit_with_outime}}" type="text" name="audit_with_outime"  class="form-control timepicker-init" id="l30">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 is_audit_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30" style="margin-top:40px;">
                                                <input type="checkbox" 
                                                       @if($data->is_audit_max_ot_fixed==1)
                                                       checked="checked"  
                                                       @endif 
                                                       name="is_audit_max_ot_fixed" placeholder="Type Policy Title"  value="1" id="is_audit_max_ot_fixed"> 
                                                       Is Max OT Fixed 
                                            </label>

                                        </div>
                                    </div>
                                    <div class="col-lg-2 is_audit_data_show_policy is_audit_max_ot_fixed">
                                        <div class="form-group">
                                            <label for="l30">Max OT Hour</label>
                                            <input type="text"  value="{{$data->audit_max_ot_hour}}"  name="audit_max_ot_hour" class="form-control timepicker-init" id="audit_max_ot_hour">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 is_audit_data_show_policy is_audit_max_ot_fixed">
                                        <div class="form-group">
                                            <label for="l30" style="margin-top:40px;">
                                                <input 
                                                    @if($data->is_audit_ot_adjust_with_outtime==1)
                                                    checked="checked"  
                                                    @endif 
                                                    type="checkbox" name="is_audit_ot_adjust_with_outtime" placeholder="Type Policy Title"  value="1" id="is_audit_ot_adjust_with_outtime"> 
                                                    Is Max OT Adjust With Outtime 
                                            </label>
                                        </div>
                                    </div>

                                </div>




                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
                            <form name="AttendancePolicy" enctype="multipart/form-data" action="{{url('Settings/AttendanceJobcardPolicy/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    @if(empty($logged_emp_com))
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
                                    @else
                                    <input type="hidden" name="company_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="card-header">
                                        <span class="cat__core__title">
                                            <strong>
												<input type="checkbox" name="is_admin_data_show_policy" placeholder="Type Policy Title" value="1" id="is_admin_data_show_policy"> 
                                                Is Admin Data Show Policy Active
											</strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="col-lg-3 is_admin_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">Addition Deduction </label>
                                            <select name="admin_addition_deduction" id="admin_addition_deduction" class="form-control">
                                                <option value="">Please Select +-</option>
                                                <option value="+">Addition (+)</option>
                                                <option value="-">Deduction (-)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 is_admin_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">With Intime </label>
                                            <input type="text" name="admin_with_intime" id="admin_with_intime" class="form-control timepicker-init">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 is_admin_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">With Outime</label>
                                            <input type="text" name="admin_with_outime"  class="form-control timepicker-init" id="l30">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 is_admin_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30" style="margin-top:40px;">
                                                <input type="checkbox" name="is_admin_max_ot_fixed" placeholder="Type Policy Title"  value="1" id="is_admin_max_ot_fixed"> 
                                                Is Max OT Fixed 
                                            </label>

                                        </div>
                                    </div>
                                    <div class="col-lg-2 is_admin_data_show_policy is_admin_max_ot_fixed">
                                        <div class="form-group">
                                            <label for="l30">Max OT Hour</label>
                                            <input type="text" name="admin_max_ot_hour" class="form-control timepicker-init" id="admin_max_ot_hour">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 is_admin_data_show_policy is_admin_max_ot_fixed">
                                        <div class="form-group">
                                            <label for="l30" style="margin-top:40px;">
                                                <input type="checkbox" name="is_admin_ot_adjust_with_outtime"  value="1" placeholder="Type Policy Title" id="is_admin_ot_adjust_with_outtime"> 
                                                Is Max OT Adjust With Outtime 
                                            </label>
                                        </div>
                                    </div>
                                </div>



                                <div class="row">
                                    <div class="card-header">
                                        <span class="cat__core__title">
                                            <strong><input type="checkbox" name="is_user_data_show_policy" placeholder="Type Policy Title"  value="1" id="is_user_data_show_policy"> 
                                                Is User Data Show Policy Active </strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">


                                    <div class="col-lg-3 is_user_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">Addition Deduction </label>
                                            <select name="user_addition_deduction" id="user_addition_deduction" class="form-control">
                                                <option value="">Please Select +-</option>
                                                <option value="+">Addition (+)</option>
                                                <option value="-">Deduction (-)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 is_user_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">With Intime </label>
                                            <input type="text" name="user_with_intime" id="user_with_intime" class="form-control timepicker-init">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 is_user_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">With Outime</label>
                                            <input type="text" name="user_with_outime"  class="form-control timepicker-init" id="l30">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 is_user_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30" style="margin-top:40px;">
                                                <input type="checkbox" name="is_user_max_ot_fixed" placeholder="Type Policy Title"  value="1" id="is_user_max_ot_fixed"> 
                                                Is Max OT Fixed 
                                            </label>

                                        </div>
                                    </div>
                                    <div class="col-lg-2 is_user_data_show_policy is_user_max_ot_fixed">
                                        <div class="form-group">
                                            <label for="l30">Max OT Hour</label>
                                            <input type="text" name="user_max_ot_hour" class="form-control timepicker-init" id="user_max_ot_hour">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 is_user_data_show_policy is_user_max_ot_fixed">
                                        <div class="form-group">
                                            <label for="l30" style="margin-top:40px;">
                                                <input type="checkbox" name="is_user_ot_adjust_with_outtime" placeholder="Type Policy Title"  value="1" id="is_user_ot_adjust_with_outtime"> 
                                                Is Max OT Adjust With Outtime 
                                            </label>
                                        </div>
                                    </div>

                                </div>



                                <div class="row">
                                    <div class="card-header">
                                        <span class="cat__core__title">
                                            <strong>
                                                <input type="checkbox" name="is_audit_data_show_policy" placeholder="Type Policy Title"  value="1" id="is_audit_data_show_policy"> 
                                                Is Audit Data Show Policy Active 
                                            </strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="row">



                                    <div class="col-lg-3 is_audit_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">Addition Deduction </label>
                                            <select name="audit_addition_deduction" id="audit_addition_deduction" class="form-control">
                                                <option value="">Please Select +-</option>
                                                <option value="+">Addition (+)</option>
                                                <option value="-">Deduction (-)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 is_audit_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">With Intime </label>
                                            <input type="text" name="audit_with_intime" id="audit_with_intime" class="form-control timepicker-init">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 is_audit_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30">With Outime</label>
                                            <input type="text" name="audit_with_outime"  class="form-control timepicker-init" id="l30">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 is_audit_data_show_policy">
                                        <div class="form-group">
                                            <label for="l30" style="margin-top:40px;">
                                                <input type="checkbox" name="is_audit_max_ot_fixed" placeholder="Type Policy Title"  value="1" id="is_audit_max_ot_fixed"> 
                                                Is Max OT Fixed 
                                            </label>

                                        </div>
                                    </div>
                                    <div class="col-lg-2 is_audit_data_show_policy is_audit_max_ot_fixed">
                                        <div class="form-group">
                                            <label for="l30">Max OT Hour</label>
                                            <input type="text" name="audit_max_ot_hour" class="form-control timepicker-init" id="audit_max_ot_hour">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 is_audit_data_show_policy is_audit_max_ot_fixed">
                                        <div class="form-group">
                                            <label for="l30" style="margin-top:40px;">
                                                <input type="checkbox" name="is_user_ot_audit_with_outtime" placeholder="Type Policy Title"  value="1" id="is_audit_ot_adjust_with_outtime"> 
                                                Is Max OT Adjust With Outtime 
                                            </label>
                                        </div>
                                    </div>

                                </div>

                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Create</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @endif

                            <!--Vertical Form Ends Here-->
                        </div>


                        <div class="col-xl-12">

                            <div class="row">
                                <div id="grid" class="col-md-12"></div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/AttendanceJobcardPolicy/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "AttendanceJobcardPolicy/Delete",
                                            data: {id: id, '_token': '<?= csrf_token() ?>'},
                                            success: function (result) {
                                                $(".k-i-refresh").click();
                                            }
                                        });
                                    }
                                }

                            </script>
                            <script type="text/javascript">
                                $(document).ready(function () {
                                    var dataSource = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?= url('Settings/AttendanceJobcardPolicy/Json') ?>",
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
                                                    id: {type: "number"},
                                                    name: {type: "string"},
                                                    is_admin_data_show_policy: {type: "boolean"},
                                                    is_user_data_show_policy: {type: "boolean"},
                                                    is_audit_data_show_policy: {type: "boolean"},
                                                    created_at: {type: "string"}
                                                }
                                            }
                                        },
                                        pageSize: 10,
                                        serverPaging: false,
                                        serverFiltering: false,
                                        serverSorting: false
                                    });
                                    $("#grid").kendoGrid({
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
                                            {field: "id", title: "#", width: "40px", filterable: false},
                                            {field: "is_admin_data_show_policy", title: "Admin Data Policy Active", width: "80px"},
                                            {field: "is_user_data_show_policy", title: "User Data Policy Active", width: "80px"},
                                            {field: "is_audit_data_show_policy ", title: "Audit Data Policy Active", width: "80px"},
                                            {field: "created_at", title: "Created ", width: "100px", },
                                            {
                                                title: "Action", width: "120px",
                                                template: kendo.template($("#action_template").html())
                                            }
                                        ],
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
<script>
    function isFunc(fid)
    {

        if (document.getElementById(fid).checked)
        {
            //alert(fid);
            $('.' + fid).fadeIn('slow');
            if (fid == "is_admin_data_show_policy")
            {
                if (document.getElementById('is_admin_max_ot_fixed').checked)
                {
                    $('.is_admin_max_ot_fixed').fadeIn('slow');
                }
                else
                {
                    $('.is_admin_max_ot_fixed').fadeOut('slow');
                }
            }

            if (fid == "is_user_data_show_policy")
            {
                if (document.getElementById('is_user_max_ot_fixed').checked)
                {
                    $('.is_user_max_ot_fixed').fadeIn('slow');
                }
                else
                {
                    $('.is_user_max_ot_fixed').fadeOut('slow');
                }
            }

            if (fid == "is_audit_data_show_policy")
            {
                if (document.getElementById('is_audit_max_ot_fixed').checked)
                {
                    $('.is_audit_max_ot_fixed').fadeIn('slow');
                }
                else
                {
                    $('.is_audit_max_ot_fixed').fadeOut('slow');
                }
            }
        }
        else
        {
            $('.' + fid).fadeOut('fast');
        }
    }

    isFunc('is_admin_data_show_policy');
    isFunc('is_user_data_show_policy');
    isFunc('is_audit_data_show_policy');

    $(document).ready(function () {
        $('#is_admin_data_show_policy').click(function () {
            isFunc('is_admin_data_show_policy');
        });

        $('#is_admin_max_ot_fixed').click(function () {
            isFunc('is_admin_data_show_policy');
        });

        $('#is_user_data_show_policy').click(function () {
            isFunc('is_user_data_show_policy');
        });

        $('#is_user_max_ot_fixed').click(function () {
            isFunc('is_user_data_show_policy');
        });

        $('#is_audit_data_show_policy').click(function () {
            isFunc('is_audit_data_show_policy');
        });

        $('#is_audit_max_ot_fixed').click(function () {
            isFunc('is_audit_data_show_policy');
        });
    });
</script>
@include('include.coreKendo')
<script src="{{url('vendors/jquery-validation/dist/jquery.validate.js')}}"></script>
<script>
    $(function () {
        $('.timepicker-init').datetimepicker({
            widgetPositioning: {
                vertical: 'bottom'
            },
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down",
                previous: 'fa fa-arrow-left',
                next: 'fa fa-arrow-right'
            },
            format: 'HH:mm:ss'
        });
    });
</script>
<link rel="stylesheet" type="text/css" href="{{url('vendors/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css')}}">
<link rel="stylesheet" type="text/css" href="{{url('vendors/fullcalendar/dist/fullcalendar.min.css')}}">

<script src="{{url('vendors/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js')}}"></script>
<script src="{{url('vendors/fullcalendar/dist/fullcalendar.min.js')}}"></script> -->


@endsection
