<?php
if (isset($data)) {
    $pageinfo = array("Leave User Sorted Data", "Leave User Sorted Data", "", "SUL","Filter list","Filtered Report");
} else {
    $pageinfo = array("Leave User Data List", "Leave User Data List", "", "SUL","Filter list","Filtered Report");
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
                        <strong>{{$pageinfo[4]}}</strong>
                        <!--<small class="text-muted">All cards are draggable and sortable!</small>-->
                    </h5>
                </div>





                <div class="card-block">

                    <div class="row">
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->


                            <div class="row">
                              @if(empty($logged_emp_com))
                              <div class="col-lg-3">
                                  <div class="form-group">
                                      <label for="l30">Company</label>
                                      <select class="form-control" name="company_id">
                                          <option selected="selected" value="">Select Company</option>
                                          @foreach($company as $row)
                                          <option value="{{$row->id}}">{{$row->name}}</option>
                                          @endforeach

                                      </select>
                                  </div>
                              </div>
                              <!--Added For Loading Company Wise Leave Type and Year-->
                              <script type="text/javascript">
                                  $(document).ready(function(){
                                      $("select[name='company_id']").change(function(){
                                          if($(this).val()!='')
                                          {
                                              //alert('sd')
                                              //For Leave Type
                                              $.post("<?=url('Filter/Company/Get/LeavePolicies/Json')?>",{'company_id':$(this).val(),'_token':'<?=csrf_token()?>'},function(data){
                                                  var total=data.length;
                                                  if(total!=0)
                                                  {
                                                      var str='';
                                                      str +='<option selected="selected" value="">Select Leave Type</option>';
                                                      $.each(data,function(index,val){
                                                              //console.log(index,val);
                                                              //console.log(val.year);
                                                          str +='<option value="'+val.id+'">'+val.leave_title+'</option>';
                                                      });
                                                          //console.log("Data Found");
                                                      $("select[name='leave_policy_id']").html(str);
                                                  }
                                                      //console.log(data);
                                              });

                                              //For Year
                                              $.post("<?=url('Filter/Company/Get/Year/Json')?>",{'company_id':$(this).val(),'_token':'<?=csrf_token()?>'},function(data){
                                                  var total=data.length;
                                                  if(total!=0)
                                                  {
                                                      var str='';
                                                      str +='<option selected="selected" value="">Select Year</option>';
                                                      $.each(data,function(index,val){
                                                              //console.log(index,val);
                                                              //console.log(val.year);
                                                          str +='<option value="'+val.year+'">'+val.year+'</option>';
                                                      });
                                                          //console.log("Data Found");
                                                      $("select[name='year']").html(str);
                                                  }
                                                      //console.log(data);
                                              });
                                          }
                                      });
                                    });
                              </script>
                              <!--Ends Loading Company Wise Leave Type and Year-->
                              @else
                              <input type="hidden" name="company_id" id="company_id" value="{{$logged_emp_com}}" class="form-control" placeholder="Type Designation Title" id="l30">
                              <!--Added For Loading Company Wise Leave Type and Year-->
                              <script type="text/javascript">
                                  $(document).ready(function(){
                                        var company_id = $("input[name='company_id']").val();
                                        //alert('sd')
                                        //For Leave Type
                                        $.post("<?=url('Filter/Company/Get/LeavePolicies/Json')?>",{'company_id':company_id,'_token':'<?=csrf_token()?>'},function(data){
                                            var total=data.length;
                                            if(total!=0)
                                            {
                                                var str='';
                                                str +='<option selected="selected" value="">Select Leave Type</option>';
                                                $.each(data,function(index,val){
                                                        //console.log(index,val);
                                                        //console.log(val.year);
                                                    str +='<option value="'+val.id+'">'+val.leave_title+'</option>';
                                                });
                                                    //console.log("Data Found");
                                                $("select[name='leave_policy_id']").html(str);
                                            }
                                                //console.log(data);
                                        });

                                        //For Year
                                        $.post("<?=url('Filter/Company/Get/Year/Json')?>",{'company_id':company_id,'_token':'<?=csrf_token()?>'},function(data){
                                            var total=data.length;
                                            if(total!=0)
                                            {
                                                var str='';
                                                str +='<option selected="selected" value="">Select Year</option>';
                                                $.each(data,function(index,val){
                                                        //console.log(index,val);
                                                        //console.log(val.year);
                                                    str +='<option value="'+val.year+'">'+val.year+'</option>';
                                                });
                                                    //console.log("Data Found");
                                                $("select[name='year']").html(str);
                                            }
                                                //console.log(data);
                                        });
                                    });
                              </script>
                              <!--Ends Loading Company Wise Leave Type and Year-->
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
                                        <label for="l30">Section</label>
                                        <select class="form-control" name="section_id">
                                            <option value="">Select Section</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="l30">Designation</label>
                                        <select class="form-control" name="designation_id">
                                            <option value="">Select Designation</option>
                                        </select>
                                    </div>
                                </div>

                                <!--Added For Leave User Data Filtering-->
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="l30">Employee</label>
                                        <select class="form-control" name="emp_code">
                                            <option value="">Select Employee</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="l30">Leave Type</label>
                                        <select class="form-control" name="leave_policy_id">
                                            <option value="">Select Leave Type</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="l30">Year</label>
                                        <select class="form-control" name="year">
                                            <option value="">Select Year</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" name="filter" id="filter"  class="btn btn-primary">Filter Record</button>
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
    <div class="col-lg-12" id="filtered_grid">
        <div class="cat__core__sortable" id="left-col">
            <section class="card" order-id="card-1">


                <div class="card-header">

                    <div class="pull-right cat__core__sortable__control">

                        <a href="#" id="export_excel" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
                        <a href="#"  id="export_pdf" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>
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
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/LeaveUserData/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a style="display:none;" class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);"><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                    var c = confirm("Do you want to delete?");
                                    if (c === true) {
                                        $.ajax({
                                            type: "POST",
                                            dataType: "json",
                                            url: "Employee/Employeeinfo/Delete",
                                            data: {
                                                id: id,
                                                '_token': '<?= csrf_token() ?>'
                                            },
                                            success: function (result) {
                                                $(".k-i-refresh").click();
                                            }
                                        });
                                    }
                                }
                            </script>


                            <script type="text/javascript">



                                function KendoManualInitialized(link,data)
                                {
                                    var dataSource = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?= url('"+link+"') ?>",
                                                type: "POST",
                                                data:data,
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
                                                    id: {
                                                        type: "number"
                                                    },
                                                    name: {
                                                        type: "string"
                                                    },
                                                    created_at: {
                                                        type: "string"
                                                    }
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
                                            pageSizes: [10, 20, 50, 100, 200, 400]
                                        },
                                        sortable: true,
                                        groupable: true,
                                        columns: [
                                        {
                                            field: "id",
                                            title: "#",
                                            width: "40px",
                                            filterable: false
                                        },
                                        {
                                            field: "emp_code",
                                            title: "Employee Code ",
                                            width: "140px"
                                        },
                                        {
                                          field: "emp_name",
                                          title: "Employee Name ",
                                          width: "100px"
                                        },
                                        {
                                          field: "leave_title",
                                          title: "Leave Title",
                                          width: "80px"
                                        },
                                        {
                                          field: "year",
                                          title: "Year",
                                          width: "30px"
                                        },
                                        {
                                          field: "total_days",
                                          title: "Total Days",
                                          width: "80px",
                                          filterable: false
                                        },
                                        {
                                          field: "availed_days",
                                          title: "Availed Days",
                                          width: "80px",
                                          filterable: false
                                        },
                                        {
                                          field: "remaining_days",
                                          title: "Remaining Days",
                                          width: "80px",
                                          filterable: false
                                        },
                                        {
                                          field: "incash_balance",
                                          title: "Incash Balance",
                                          width: "80px",
                                          filterable: false
                                        },
                                        {
                                          field: "carry_forward_balance",
                                          title: "Carry Forward Balance",
                                          width: "80px",
                                          filterable: false
                                        },
                                        {
                                            field: "created_at",
                                            title: "Created ",
                                            width: "60px",
                                        },
                                        {
                                            title: "Action",
                                            width: "150px",
                                            template: kendo.template($("#action_template").html())
                                        }
                                        ],
                                    });
                                }

                                $(document).ready(function () {

                                    $("button[name='filter']").click(function(){
                                       // alert('success');
                                        var company_id=$("select[name='company_id']").val();
                                        // if(empty(company_id) || company_id=="" || company_id==0 || !isset(company_id)){
                                        //   var company_id=$("input[name='company_id']").val();
                                        // }
                                        var department_id=$("select[name='department_id']").val();
                                        var section_id=$("select[name='section_id']").val();
                                        var designation_id=$("select[name='designation_id']").val();
                                        var employee_code=$("select[name='emp_code']").val();
                                        var leave_policy_id=$("select[name='leave_policy_id']").val();
                                        var year=$("select[name='year']").val();

                                        var param={'company_id':company_id,'department_id':department_id,'section_id':section_id,'designation_id':designation_id,'employee_code':employee_code,'leave_policy_id':leave_policy_id,'year':year,'_token':'<?=csrf_token()?>'};
                                        var link="Filter/LeaveUserData/List";

                                        KendoManualInitialized(link,param);

                                    });

                                    $("#export_excel").click(function(){

                                        var company_id=$("select[name='company_id']").val();

                                        if(company_id=='')
                                        {
                                            company_id=0;
                                        }
                                        var department_id=$("select[name='department_id']").val();
                                        if(department_id=='')
                                        {
                                            department_id=0;
                                        }
                                        var section_id=$("select[name='section_id']").val();
                                        if(section_id=='')
                                        {
                                            section_id=0;
                                        }
                                        var designation_id=$("select[name='designation_id']").val();
                                        if(designation_id=='')
                                        {
                                            designation_id=0;
                                        }
                                        var designation_id=$("select[name='designation_id']").val();
                                        if(designation_id=='')
                                        {
                                            designation_id=0;
                                        }
                                        var employee_code=$("select[name='emp_code']").val();
                                        if(employee_code=='')
                                        {
                                            employee_code=0;
                                        }
                                        var leave_policy_id=$("select[name='leave_policy_id']").val();
                                        if(leave_policy_id=='')
                                        {
                                            leave_policy_id=0;
                                        }
                                        var year=$("select[name='year']").val();
                                        if(year=='')
                                        {
                                            year=0;
                                        }



                                        var param={'company_id':company_id,'department_id':department_id,'section_id':section_id,'designation_id':designation_id,'employee_code':employee_code,'leave_policy_id':leave_policy_id,'year':year,'_token':'<?=csrf_token()?>'};
                                        var link="<?=url('/')?>/Export/LeaveUserData/Excel/"+company_id+"/"+department_id+"/"+section_id+"/"+designation_id+"/"+employee_code+"/"+leave_policy_id+"/"+year;

                                        window.location.href=link;

                                    });

                                    $("#export_pdf").click(function(){

                                        var company_id=$("select[name='company_id']").val();

                                        if(company_id=='')
                                        {
                                            company_id=0;
                                        }
                                        var department_id=$("select[name='department_id']").val();
                                        if(department_id=='')
                                        {
                                            department_id=0;
                                        }
                                        var section_id=$("select[name='section_id']").val();
                                        if(section_id=='')
                                        {
                                            section_id=0;
                                        }
                                        var designation_id=$("select[name='designation_id']").val();
                                        if(designation_id=='')
                                        {
                                            designation_id=0;
                                        }
                                        var employee_code=$("select[name='emp_code']").val();
                                        if(employee_code=='')
                                        {
                                            employee_code=0;
                                        }
                                        var leave_policy_id=$("select[name='leave_policy_id']").val();
                                        if(leave_policy_id=='')
                                        {
                                            leave_policy_id=0;
                                        }
                                        var year=$("select[name='year']").val();
                                        if(year=='')
                                        {
                                            year=0;
                                        }



                                        var param={'company_id':company_id,'department_id':department_id,'section_id':section_id,'designation_id':designation_id,'employee_code':employee_code,'leave_policy_id':leave_policy_id,'year':year,'_token':'<?=csrf_token()?>'};
                                        var link="<?=url('/')?>/Export/LeaveUserData/Pdf/"+company_id+"/"+department_id+"/"+section_id+"/"+designation_id+"/"+employee_code+"/"+leave_policy_id+"/"+year;

                                        window.location.href=link;

                                    });

                                    //For Deafult Data View
                                    var dataSource = new kendo.data.DataSource({
                                        transport: {
                                            read: {
                                                url: "<?= url('/Filter/LeaveUserData/Json') ?>",
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
                                                    id: {
                                                        type: "number"
                                                    },
                                                    name: {
                                                        type: "string"
                                                    },
                                                    created_at: {
                                                        type: "string"
                                                    }
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
                                            pageSizes: [10, 20, 50, 100, 200, 400]
                                        },
                                        sortable: true,
                                        groupable: true,
                                        columns: [
                                        {
                                            field: "id",
                                            title: "#",
                                            width: "40px",
                                            filterable: false
                                        },
                                        {
                                            field: "emp_code",
                                            title: "Code ",
                                            width: "140px"
                                        },
                                        {
                                          field: "emp_name",
                                          title: "Employee Name ",
                                          width: "100px"
                                        },
                                        {
                                          field: "leave_title",
                                          title: "Leave Title",
                                          width: "80px"
                                        },
                                        {
                                          field: "year",
                                          title: "Year",
                                          width: "80px"
                                        },
                                        {
                                          field: "total_days",
                                          title: "Total Days",
                                          width: "80px",
                                          filterable: false
                                        },
                                        {
                                          field: "availed_days",
                                          title: "Availed Days",
                                          width: "80px",
                                          filterable: false
                                        },
                                        {
                                          field: "remaining_days",
                                          title: "Remaining Days",
                                          width: "80px",
                                          filterable: false
                                        },
                                        {
                                          field: "incash_balance",
                                          title: "Incash Balance",
                                          width: "80px",
                                          filterable: false
                                        },
                                        {
                                          field: "carry_forward_flag",
                                          title: "Carry Forward",
                                          width: "80px",
                                          filterable: false
                                        },
                                        {
                                            field: "created_at",
                                            title: "Created ",
                                            width: "100px",
                                        },
                                        {
                                            title: "Action",
                                            width: "120px",
                                            template: kendo.template($("#action_template").html())
                                        }
                                        ],
                                    });
                                    //Ends For Default Data View
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
<!-- <script type="text/javascript">
  $("#filter").click(function () {
    $("#filtered_grid").show(500);
  });
</script> -->
@include('include.coreKendo')
@include('ajax_include.company_wise_department')
@include('ajax_include.department_wise_section')
@include('ajax_include.section_wise_designation')
@include('ajax_include.company_department_section_designation_wise_employee')
@endsection
