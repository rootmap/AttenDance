<?php
if (isset($data)) {
    $pageinfo = array("Edit Individual Step Leave Approval Settings", "Edit Leave Workflow Record", "", "SUL");
} else {
    $pageinfo = array("Leave Workflow Settings", "Add Leave Workflow Record", "", "SUL");
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
                            <form name="LeaveWorkflowSetting" action="{{url('Settings/LeaveWorkflowSetting/Update/'.$data[0]->id)}}" method="post">
                                {{csrf_field()}}
                                <div class="row">

                                    <div class="col-lg-5">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-6">Employee</label>
                                            <input type="text" value="{{$data[0]->emp_name}}" readonly="readonly" class="form-control" name="emp_name" />
                                        </div>
                                    </div>

                                    <div class="col-lg-5">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-6">Change Approval Supervisor</label>
                                            <select class="form-control" name="sup_emp_code">
                                                <option>Select Section</option>
                                                @if(isset($emp_all))
                                                    @foreach($emp_all as $row)
                                                        <option
                                                        @if($row->emp_code==$data[0]->sup_emp_code)
                                                            selected="selected"
                                                        @endif

                                                         value="{{$row->emp_code}}">{{$row->emp_code}} - {{$row->first_name}} {{$row->last_name}}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="l30" class="col-md-12">Approval Step</label>
                                            <input type="text" value="STEP - {{$data[0]->step}}" readonly="readonly" class="form-control" name="emp_step" />
                                        </div>
                                    </div>


                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
                            <form name="LeaveWorkflow" action="{{url('Settings/LeaveWorkflowSetting/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                    
									<div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Company</label>
                                            <select class="form-control" name="company_id">
                                                <option selected="selected" value="">Select Company</option>
                                                @foreach($com as $row)
                                                <option value="{{$row->id}}">{{$row->name}}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    


                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Department</label>
                                            <select class="form-control" name="department_id">
                                                <option>Select Department</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Section</label>
                                            <select class="form-control" name="section_id">
                                                <option>Select Section</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">Designation</label>
                                            <select class="form-control" name="designation_id">
                                                <option>Select Section</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="l30">No. of Steps</label>
                                            <select class="form-control" name="step_id" id="mySelect">
                                                <option value="">Select Step</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-5">
                                            <div class="form-group">
                                                <label>Employee</label>
                                            <select name="emp_in_approval[]"  class="form-control" size="7" multiple="multiple">

                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    
                                </div>
                                <!--starts options cloned on the basis of selected steps-->
                                <div class="row" id="supervisor"></div>
                                <!--ends options cloned on the basis of selected steps-->


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Create</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
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
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/LeaveWorkflowSetting/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick('#=emp_code#');" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                        <script type="text/javascript">
                            function deleteClick(id) {
                                var c = confirm("Do you want to delete?");
                                if (c === true) {
                                    var BaseUrl="<?=url('Settings/LeaveWorkflowSetting/Delete')?>";
                                    $.ajax({
                                        type: "POST",
                                        dataType: "json",
                                        url: BaseUrl,
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
                                            url: "<?= url('Settings/LeaveWorkflowSetting/Json') ?>",
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
                                                created_at: {type: "string"}
                                            }
                                        }
                                    },
                                    pageSize: 10,
                                    serverPaging: true,
                                    serverFiltering: true,
                                    serverSorting: true
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

                                        {field: "company", title: "Company Name ", width: "80px"},
                                        {field: "emp_name", title: "Emp Name ", width: "80px"},
                                        {field: "sup_emp_name", title: "Sup.Emp Name ", width: "80px"},
                                        {field: "step", title: "Step ", width: "80px"},
                                        {field: "created_at", title: "Created ", width: "100px", },
                                        {
                                            title: "Action", width: "80px",
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

@include('include.coreKendo')

<script>
    $(document).ready(function(){

        $("select[name='company_id']").change(function(){
            if($(this).val()!='')
            {
                var baseUrl="<?=url('Filter/Designation/Get/Employee/Json')?>";
                var company_id=$("select[name='company_id']").val();

                var param={'company_id':company_id,'_token':'<?=csrf_token()?>'};

                $.post(baseUrl,param,function(data){
                    var strd='';
                    strd +='<option  selected="selected" value="">Select Employee</option>';
                    $.each(data,function(index,val){
                        strd +='<option value="'+val.emp_code+'">'+val.emp_code+'-'+val.name+'</option>';
                    });
                    $("select[name='emp_in_approval[]']").html(strd);
                });
            }
        });

        $("select[name='department_id']").change(function(){
            if($(this).val()!='')
            {
                var baseUrl="<?=url('Filter/Designation/Get/Employee/Json')?>";
                var company_id=$("select[name='company_id']").val();
                var department_id=$("select[name='department_id']").val();
                var param={'company_id':company_id,'department_id':department_id,'_token':'<?=csrf_token()?>'};

                $.post(baseUrl,param,function(data){
                    var strd='';
                    strd +='<option  selected="selected" value="">Select Employee</option>';
                    $.each(data,function(index,val){
                        strd +='<option value="'+val.emp_code+'">'+val.emp_code+'-'+val.name+'</option>';
                    });
                    $("select[name='emp_in_approval[]']").html(strd);
                });
            }
        });

        $("select[name='section_id']").change(function(){
            if($(this).val()!='')
            {
                var baseUrl="<?=url('Filter/Designation/Get/Employee/Json')?>";
                var company_id=$("select[name='company_id']").val();
                var department_id=$("select[name='department_id']").val();
                var section_id=$("select[name='section_id']").val();
                var param={'company_id':company_id,'department_id':department_id,'section_id':section_id,'_token':'<?=csrf_token()?>'};

                $.post(baseUrl,param,function(data){
                    var strd='';
                    strd +='<option  selected="selected" value="">Select Employee</option>';
                    $.each(data,function(index,val){
                        strd +='<option value="'+val.emp_code+'">'+val.emp_code+'-'+val.name+'</option>';
                    });
                    $("select[name='emp_in_approval[]']").html(strd);
                });
            }
        });

        $("select[name='designation_id']").change(function(){
            if($(this).val()!='')
            {
                var baseUrl="<?=url('Filter/Designation/Get/Employee/Json')?>";
                var company_id=$("select[name='company_id']").val();
                var department_id=$("select[name='department_id']").val();
                var section_id=$("select[name='section_id']").val();
                var designation_id=$("select[name='designation_id']").val();
                var param={'company_id':company_id,'department_id':department_id,'section_id':section_id,'designation_id':designation_id,'_token':'<?=csrf_token()?>'};

                $.post(baseUrl,param,function(data){
                    var strd='';
                    strd +='<option  selected="selected" value="">Select Employee</option>';
                    $.each(data,function(index,val){
                        strd +='<option value="'+val.emp_code+'">'+val.emp_code+'-'+val.name+'</option>';
                    });
                    $("select[name='emp_in_approval[]']").html(strd);
                });
            }
        });


        $("select[name='step_id']").change(function(){
            if($(this).val()!='')
            {



                var html = '';
                for (i = 1; i <= $(this).val(); i++) {
                    var field_name='step_' + i;
                    html += '<div class="col-lg-3">';
                    html += '<div class="form-group">';
                    html += '<label for="l30">Step - ' + i + ':</label>';
                    html += '<select class="form-control" type="text" id="' + field_name + '" name="' + field_name + '">';
                    html += '<option value="">Select Step - ' + i + ' Employee</option>';
                    html += '</select>';
                    html += '</div>';
                    html += '</div>';
                  }

                $('#supervisor').html(html);
                for (i = 1; i <= $(this).val(); i++) {
                  var fid='step_' + i;
                  EmpLoader(fid,i);
                }

                //alert($(this).val());
            }
        });
    });


    function EmpLoader(fid,i)
    {
      if ($("input[name='company_id']").length==0)
      {
          var company_id=$("select[name='company_id']").val();
      }
      else {
        var company_id=$("input[name='company_id']").val();
      }

      if(company_id=='')
      {
          company_id=0;
      }
      var fid_html='';
      $.post("<?=url('Filter/All/Get/Employee/Json')?>",
          {'company_id':company_id,'_token':'<?=csrf_token()?>'},
          function(data){
          var total=data.length;
          if(total!=0)
          {
              //var str='';
                fid_html += '<option value="">Select Step - ' + i + ' Supervisor</option>';
              $.each(data,function(index,val){
                  fid_html +='<option value="'+val.emp_code+'">'+val.emp_code+"-"+val.name+'</option>';
              });
              //$("select["name='emp_code']"").html(str);
          }
          else
          {
              fid_html +='<option selected="selected" value="">0 Record Found</option>';
          }

          $("select[name='"+fid+"']").html(fid_html);
      });
    }

</script>

@include('ajax_include.company_wise_department')
@include('ajax_include.department_wise_section')
@include('ajax_include.section_wise_designation')
@endsection
