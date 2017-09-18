<?php
if(isset($data))
{
    $pageinfo=array("Edit Leave User Info","Edit Leave User Record","","SUL");
}
else
{
    $pageinfo=array("Leave User Info","Add Leave User Record","","SUL");
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

                    <div class="pull-right cat__core__sortable__control">

                        <a href="{{route('settings.leaveUserData.export.excel')}}" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
                        <a href="{{route('settings.leaveUserData.export.pdf')}}" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>
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
                            @if(isset($data))
                            <form name="ModulePages" action="{{url('Settings/LeaveUserData/Update/'.$data['id'])}}" method="post">
                                {{csrf_field()}}
                                <div class="row">

                                <div class="col-lg-3">
                                  <div class="form-group">
                                      <label for="130">Total Days</label>
                                      <input id="total_days" name="total_days" type="text" class="form-control" value="{{$data['total_days']}}">
                                  </div>
                                </div>
                                <div class="col-lg-3">
                                  <div class="form-group">
                                      <label for="130">Availed Days</label>
                                      <input id="availed_days" name="availed_days" type="text" class="form-control" value="{{$data['availed_days']}}">
                                  </div>
                                </div>
                                <div class="col-lg-3">
                                  <div class="form-group">
                                      <label for="130">Remaining Days</label>
                                      <input id="remaining_days" name="remaining_days" type="text" class="form-control" value="{{$data['remaining_days']}}" disabled="disabled">
                                  </div>
                                </div>

                                <div class="col-lg-3">
                                  <div class="form-group">
                                      <label for="130">Incash Balance</label>
                                      <input name="incash_balance" type="text" class="form-control" value="{{$data['incash_balance']}}">
                                  </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                      <!-- carry_forward_balance -->
                                      <label for="130">Carry Forward Balance</label>
                                      <input id="carry_forward_balance" name="carry_forward_balance" type="text" class="form-control" value="{{$data['carry_forward_balance']}}">
                                    </div>
                                </div>



                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            <script type="text/javascript">
                            $("#total_days").keyup(function () {
                              var ttl_days = $(this).val();

                              $("#remaining_days").val(ttl_days);
                            });

                            $("#availed_days").keyup(function () {
                              var avld_days = $(this).val();
                              var ex_ttl_days = $("#total_days").val();
                              var new_rem_days = (ex_ttl_days-0) - (avld_days-0);
                              $("#remaining_days").val(new_rem_days);
                            });
                            </script>
                            @else
              							<form name="ModulePages" action="{{url('Settings/LeaveUserData/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                  <input type="hidden" id="leave_user_data_id" name="leave_user_data_id" value="" class="form-control" placeholder="Type Designation Title" id="l30">
                                  @if(empty($logged_emp_com) || !isset($logged_emp_com) || $logged_emp_com =="Undefined")
                                  <div class="col-md-3">
                                      <div class="form-group">
                                          <label>Company</label>
                                          <select name="company_id" class="form-control">
                                              <option value="">Select Company</option>
                                              @if(isset($company))
                                              @foreach($company as $row)
                                              <option value="{{$row->id}}">{{$row->name}}</option>
                                              @endforeach
                                              @endif
                                          </select>
                                      </div>
                                  </div>
                                  <script type="text/javascript">
                                      $(document).ready(function(){
                                          $("select[name='company_id']").change(function(){
                                              if($(this).val()!='')
                                              {
                                                  $.post("<?=url('Settings/LeaveUserData/Get/Employees/Json')?>",{'company_id':$(this).val(),'_token':'<?=csrf_token()?>'},function(data){
                                                      var total=data.length;
                                                      if(total!=0)
                                                      {
                                                          var str='';
                                                          str +='<option selected="selected" value="">Select Employee</option>';
                                                          $.each(data,function(index,val){
                                                                  //console.log(index,val);
                                                                  //console.log(val.year);
                                                              str +='<option value="'+val.emp_code+'">'+val.emp_name+'</option>';
                                                          });
                                                              //console.log("Data Found");
                                                          $("select[name='emp_code']").html(str);
                                                      }
                                                      else
                                                      {
                                                          var str='';
                                                          str +='<option selected="selected" value="">0 Record Found</option>';
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
                                  <input type="hidden" name="company_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                                  <script type="text/javascript">
                                      $(document).ready(function(){
                                          var company_id = $("input[name='company_id']").val();

                                          $.post("<?=url('Settings/LeaveUserData/Get/Employees/Json')?>",{'company_id':company_id,'_token':'<?=csrf_token()?>'},function(data){
                                              var total=data.length;
                                              if(total!=0)
                                              {
                                                  var str='';
                                                  str +='<option selected="selected" value="">Select Employee</option>';
                                                  $.each(data,function(index,val){
                                                          //console.log(index,val);
                                                          //console.log(val.year);
                                                      str +='<option value="'+val.emp_code+'">'+val.emp_name+'</option>';
                                                  });
                                                      //console.log("Data Found");
                                                  $("select[name='emp_code']").html(str);
                                              }
                                              else
                                              {
                                                  var str='';
                                                  str +='<option selected="selected" value="">0 Record Found</option>';
                                                  $("select[name='emp_code']").html(str);
                                                  //console.log(data);
                                              }
                                                  //console.log(data);
                                          });
                                        });
                                  </script>
                                  @endif
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Employee</label>
                                            <select class="form-control" name="emp_code" id="emp_code">
                                                <option value="">Select Employee</option>
                                                @if(isset($employee))
                                                    @foreach($employee as $row)
                                                    <option value="{{$row->emp_code}}">{{$row->first_name}} {{$row->last_name}}</option>
                                                    @endforeach
                                                @endif

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Leave Type</label>
                                            <select class="form-control" name="leave_policy_id"  id="leave_policy_id">
                                                <option value="">Select Leave Type</option>
                                                @if(isset($leave_policies))
                                                    @foreach($leave_policies as $row)
                                                    <option value="{{$row->id}}">{{$row->leave_title}}</option>
                                                    @endforeach
                                                @endif

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Year</label>
                                            <select class="form-control" name="year" id="year" onchange="javascript:GetEmpLeaveBalance();">
                                                <option value="">Select Year</option>
                                                @if(isset($year))
                                                    @foreach($year as $row)
                                                    <option value="{{$row->year}}">{{$row->year}}</option>
                                                    @endforeach
                                                @endif

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                      <div class="form-group">
                                          <label for="130">Total Days</label>
                                          <input id="total_days" name="total_days" type="text" class="form-control" placeholder="Type Total Days">
                                      </div>
                                    </div>
                                    <div class="col-lg-3">
                                      <div class="form-group">
                                          <label for="130">Availed Days</label>
                                          <input id="availed_days" name="availed_days" type="text" class="form-control" placeholder="Type Availed Days">
                                      </div>
                                    </div>
                                    <div class="col-lg-3">
                                      <div class="form-group">
                                          <label for="130">Remaining Days</label>
                                          <input id="remaining_days" name="remaining_days" type="text" class="form-control" value="0" placeholder="Type Remaining Days" disabled="disabled">
                                      </div>
                                    </div>

                                    <div class="col-lg-3">
                                      <div class="form-group">
                                          <label for="130">Incash Balance</label>
                                          <input id="incash_balance" name="incash_balance" type="text" class="form-control" placeholder="Type Incash Balance">
                                      </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                          <!-- carry_forward_balance -->
                                          <label for="130">Carry Forward Balance</label>
                                          <input id="carry_forward_balance" name="carry_forward_balance" type="text" class="form-control" placeholder="Type Carry Forward Balance">
                                        </div>
                                    </div>


                                </div>


                                <div class="form-actions">
                                    <button type="submit" id="submit-btn1" class="btn btn-primary">Create</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>

                            <script type="text/javascript">
                            //For Leave User Data with Leave Type and Employee and year
                            $("select[name='company_id']").change(function () {
                              $("select[name='emp_code']").val("");
                              $("select[name='leave_policy_id']").val("");
                              $("select[name='year']").val("");
                              $("#total_days").val("");
                              $('#availed_days').val("");
                              $("#remaining_days").val("");
                              $("#incash_balance").val("");
                              $('#carry_forward_balance').val("");
                              $("#submit-btn1").html('Create');
                            });
                            $("select[name='emp_code']").change(function () {
                              $("select[name='leave_policy_id']").val("");
                              $("select[name='year']").val("");
                              $("#total_days").val("");
                              $('#availed_days').val("");
                              $("#remaining_days").val("");
                              $("#incash_balance").val("");
                              $('#carry_forward_balance').val("");
                              $("#submit-btn1").html('Create');
                            });
                            $("select[name='leave_policy_id']").change(function () {
                              $("select[name='year']").val("");
                              $("#total_days").val("");
                              $('#availed_days').val("");
                              $("#remaining_days").val("");
                              $("#incash_balance").val("");
                              $('#carry_forward_balance').val("");
                              $("#submit-btn1").html('Create');
                            });

                            $("#total_days").keyup(function () {
                              var ttl_days = $(this).val();

                              $("#remaining_days").val(ttl_days);
                            });

                            $("#availed_days").keyup(function () {
                              var avld_days = $(this).val();
                              var ex_ttl_days = $("#total_days").val();
                              var new_rem_days = (ex_ttl_days-0) - (avld_days-0);
                              $("#remaining_days").val(new_rem_days);
                            });

                            function GetEmpLeaveBalance() {
                                var company_id=$("select[name='company_id']").val();
                                var employee_id=$("select[name='emp_code']").val();
                                var leave_policy_id=$("select[name='leave_policy_id']").val();
                                var year = $("select[name='year']").val();

                                if (company_id=="") {
                                  swal({
                                       title: "Field(s) Empty!",
                                       text: "Please Select Company",
                                       type: "warning"
                                       },
                                       function(){
                                           $('#company_id').focus();
                                       });
                                } else if (employee_id==""){
                                  swal({
                                      title: "Field(s) Empty!",
                                      text: "Please Select Employee",
                                      type: "warning"
                                  },
                                  function(){
                                      $('#emp_code').focus();
                                  });
                                } else if (leave_policy_id==""){
                                  swal({
                                      title: "Field(s) Empty!",
                                      text: "Please Input Leave Type",
                                      type: "warning"
                                  },
                                  function(){
                                      $('#leave_policy_id').focus();
                                  });
                                } else if (year==""){
                                  //alert('Please Select Leave Start Date');
                                  swal({
                                      title: "Field(s) Empty!",
                                      text: "Please Select year",
                                      type: "warning"
                                  },
                                  function(){
                                      $('#year').focus();
                                  });
                                } else {
                                  $.post("<?=url('/Leave/LeaveApplication/Get/LeaveUserData')?>",{'company_id':company_id,'employee_code':employee_id,'leave_policy_id':leave_policy_id,'year':year,'_token':'<?=csrf_token()?>'},function(data){
                                      var total=data.length;
                                      if(total!=0)
                                      {
                                        //alert('not zero');
                                          var leave_user_data_id='';
                                          var total_days='';
                                          var availed_days='';
                                          var remaining_days='';
                                          var incash_balance='';
                                          var carry_forward_flag='';

                                          leave_user_data_id = data.leave_user_data_id;
                                          total_days = data.total_days;
                                          availed_days = data.availed_days;
                                          remaining_days = data.remaining_days;
                                          incash_balance = data.incash_balance;
                                          carry_forward_balance = data.carry_forward_flag;

                                          $("#leave_user_data_id").val(leave_user_data_id);
                                          $("#total_days").val(total_days);
                                          $('#availed_days').val(availed_days);
                                          $("#remaining_days").val(remaining_days);
                                          $("#incash_balance").val(incash_balance);
                                          $("#carry_forward_balance").val(carry_forward_balance);
                                          $("#submit-btn1").html('Update');



                                      }
                                  });
                                } //end else
                              }
                            //Ends
                            </script>
                            @endif
                            <!--Vertical Form Ends Here-->
                        </div>
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row">
                                <div id="grid" class="col-md-12"></div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/LeaveUserData/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a style="display:none;" class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>



                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "LeaveUserData/Delete",
                                            data: {id: id,'_token':'<?=csrf_token()?>'},
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
                                                url: "<?=url('Settings/LeaveUserData/Json')?>",
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
                                                    emp_name: {type: "string"},
                                                    leave_title:{type: "string"},
                                                    year:{type: "string"},
                                                    total_days:{type: "string"},
                                                    availed_days:{type: "string"},
                                                    remaining_days:{type: "string"},
                                                    incash_balance:{type: "string"},
                                                    carry_forward_balance:{type: "float"},
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
                                        dataBound:gridDataBound,
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
                                        {field: "emp_name", title: "Employee Name ", width: "100px"},
                                        {field: "leave_title", title: "Leave Title", width: "80px"},
                                        {field: "year", title: "Year", width: "80px"},
                                        {field: "total_days", title: "Total Days", width: "80px", filterable: false},
                                        {field: "availed_days", title: "Availed Days", width: "80px", filterable: false},
                                        {field: "remaining_days", title: "Remaining Days", width: "80px", filterable: false},
                                        {field: "incash_balance", title: "Incash Balance", width: "80px", filterable: false},
                                        {field: "carry_forward_balance", title: "Carry Forward Balance", width: "100px", filterable: false},
                                        {field: "created_at", title: "Created ", width: "100px",},
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

<!-- @section('extraHeader')
<link rel="stylesheet" type="text/css" href="{{url('vendors/bootstrap-select/dist/css/bootstrap-select.min.css')}}">
<link rel="stylesheet" type="text/css" href="{{url('vendors/select2/dist/css/select2.min.css')}}">
@endsection -->
@section('extraFooter')
<!-- <script src="{{url('vendors/bootstrap-select/dist/js/bootstrap-select.min.js')}}"></script>
<script src="{{url('vendors/select2/dist/js/select2.full.min.js')}}"></script> -->
@include('include.coreKendo')
@endsection
