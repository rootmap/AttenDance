<?php
if (isset($data)) {
    $pageinfo = array("Edit Leave Email Template Settings", "Leave Email Template Settings", "", "SUL","Leave Email Template Settings","Leave Email Template Settings");
} else {
    $pageinfo = array("Create Leave Email Template Settings", "Leave Email Template Settings", "", "SUL","Leave Email Template Settings","Leave Email Template Settings");
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
                                <form name="LeaveEmailTemplateUpdate" id="LeaveEmailTemplateUpdate" action="{{url('/Leave/EmailTemplate/Settings/Update/'.$data['id'])}}" method="post" enctype="multipart/form-data">

                                    {{csrf_field()}}
                                    <div class="row">
                                      <div class="col-lg-12">
                                        <div class="form-group">
                                           <label for="l30">Email Message Template For: <span style="color:red;">*</span> </label>
                                           <select class="form-control"   id="template_type_id" name="template_type_id">
                                               <option value="">Select Email Template Type</option>
                                               @if(isset($template_type))
                                               @foreach($template_type as $crow)
                                               <option
                                               @if($data['template_type_id']==$crow->id)
                                                   selected="selected"
                                               @endif
                                               value="{{$crow->id}}">{{$crow->template_type}}</option>
                                               @endforeach
                                               @endif
                                           </select>
                                        </div>
                                      </div>

                                      <div class="col-lg-12">
                                        <div class="form-group">
                                           <label for="l30">Message: <span style="color:red;">*</span> </label> <br/>
                                           <br />
                                           <table class="table table-bordered">
                                              <thead>
                                                <tr>
                                                  <th colspan="6">Please Use These Short Codes For Email Template Design</th>
                                                </tr>
                                                <tr>
                                                  <th>Message Content</th>
                                                  <th>Short Code</th>
                                                  <th>Message Content</th>
                                                  <th>Short Code</th>
                                                  <th>Message Content</th>
                                                  <th>Short Code</th>
                                                </tr>
                                              </thead>
                                              <tbody>
                                                <tr>
                                                  <td>Applicant's Name</td>
                                                  <td class="text-primary font-weight-bold">APPNAME</td>
                                                  <td>Applicant's Designation</td>
                                                  <td class="text-primary font-weight-bold">APPDES</td>
                                                  <td>Applicant's Department</td>
                                                  <td class="text-primary font-weight-bold">APPDEP</td>
                                                </tr>
                                                <tr>
                                                  <td>Company Name</td>
                                                  <td class="text-primary font-weight-bold">COMPANY</td>
                                                  <td>Leave Type</td>
                                                  <td class="text-primary font-weight-bold">LEAVETYPE</td>
                                                  <td>Leave Start Date</td>
                                                  <td class="text-primary font-weight-bold">LEAVESTART</td>
                                                </tr>
                                                <tr>
                                                  <td>Leave End Date</td>
                                                  <td class="text-primary font-weight-bold">LEAVEEND</td>
                                                  <td>Total Days</td>
                                                  <td class="text-primary font-weight-bold">TOTALDAYS</td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          <br />
                                           <textarea type="text" value="" name="message_template" class="k-textbox" id="message_template"  style="width: 100%; height: 450px;">{{$data->msg_template}}</textarea>
                                        </div>
                                      </div>
                                    </div>


                                    <div class="form-actions">
                                        <button type="submit"  class="btn btn-primary">Update</button>
                                        <button type="reset" class="btn btn-default">Cancel</button>
                                    </div>
                                </form>
                                @else
                                <form name="LeaveEmailTemplate" id="LeaveEmailTemplate" action="{{url('/Leave/EmailTemplate/Settings/Add')}}" method="post">
                                    {{csrf_field()}}
                                    <div class="row">
                                      <div class="col-lg-12">
                                        <div class="form-group">
                                           <label for="l30">Email Message Template For: <span style="color:red;">*</span> </label>
                                           <select class="form-control"   id="template_type_id" name="template_type_id">
                                               <option value="">Select Email Template Type</option>
                                               @if(isset($template_type))
                                               @foreach($template_type as $crow)
                                               <option value="{{$crow->id}}">{{$crow->template_type}}</option>
                                               @endforeach
                                               @endif
                                           </select>
                                        </div>
                                      </div>

                                      <div class="col-lg-12" id="email_temp_div" style="display:none;">
                                        <div class="form-group">
                                           <label for="l30">Message: <span style="color:red;">*</span> </label> <br/>
                                           <br />
                                           <table class="table table-bordered">
                                              <thead>
                                                <tr>
                                                  <th colspan="6">Please Use These Short Codes For Email Template Design</th>
                                                </tr>
                                                <tr>
                                                  <th>Message Content</th>
                                                  <th>Short Code</th>
                                                  <th>Message Content</th>
                                                  <th>Short Code</th>
                                                  <th>Message Content</th>
                                                  <th>Short Code</th>
                                                </tr>
                                              </thead>
                                              <tbody>
                                                <tr>
                                                  <th>Applicant's Name</th>
                                                  <td class="text-primary font-weight-bold">APPNAME</td>
                                                  <td>Applicant's Designation</td>
                                                  <td class="text-primary font-weight-bold">APPDES</td>
                                                  <td>Applicant's Department</td>
                                                  <td class="text-primary font-weight-bold">APPDEP</td>
                                                </tr>
                                                <tr>
                                                  <th>HR Name</th>
                                                  <td class="text-primary font-weight-bold">HRNAME</td>
                                                  <td>HR Designation</td>
                                                  <td class="text-primary font-weight-bold">HRDES</td>
                                                  <td>Company namespace</td>
                                                  <td class="text-primary font-weight-bold">COMPANY</td>
                                                </tr>
                                                <tr>
                                                  <th>Leave Type</th>
                                                  <td class="text-primary font-weight-bold">LEAVETYPE</td>
                                                  <td>Leave Start Date</td>
                                                  <td class="text-primary font-weight-bold">LEAVESTART</td>
                                                  <td>Leave End Date</td>
                                                  <td class="text-primary font-weight-bold">LEAVEEND</td>
                                                </tr>
                                                <tr>
                                                  <th>Total Days</th>
                                                  <td class="text-primary font-weight-bold">TOTALDAYS</td>
                                                  <td>Supervisor's Name</td>
                                                  <td class="text-primary font-weight-bold">SUPNAME</td>
                                                  <!-- <td>Otto</td>
                                                  <td class="text-primary font-weight-bold">LEAVEEND</td> -->
                                                </tr>
                                              </tbody>
                                            </table>
                                          <br />
                                          <textarea type="text" value="" id="message_template" name="message_template" class="k-textbox" id="message_template"  style="width: 98%; height: 450px;"></textarea>
                                        </div>

                                        <div class="form-actions">
                                          <button type="submit" class="btn btn-primary">Create</button>
                                          <button type="reset" class="btn btn-default">Cancel</button>
                                      </div>
                                      </div>
                                    </div>

                                </form>
                                @endif
                                <!--Vertical Form Ends Here-->
                                <div class="col-xl-12">
                                <!--Vertical Form Starts Here-->
                                <!-- kendo table code start from here-->
                                <div class="row">
                                    <div id="grid" class="col-md-12"></div>
                                </div>
                                <script id="action_template" type="text/x-kendo-template">
                                    <a class="k-button k-button-icontext k-grid-edit" href="{{url('/Leave/EmailTemplate/Settings/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                    <a style="display:none;" class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                                </script>

                                <script type="text/javascript">
                                    function deleteClick(id) {
                                    var baseUrl = "<?= url('/Leave/EmailTemplate/Settings/Delete') ?>";
                                            var c = confirm("Do you want to delete?");
                                            if (c === true) {
                                    $.ajax({
                                    type: "POST",
                                            dataType: "json",
                                            url: baseUrl,
                                            data: {id: id, '_token':'<?= csrf_token() ?>'},
                                            success: function (result) {
                                            $(".k-i-refresh").click();
                                            }
                                    });
                                    }
                                    }

                                </script>

                                <script type="text/javascript">
                                    $(document).ready(function(){
                                        $("select[name='template_type_id']").change(function(){
                                          var template_type_id = $(this).val();
                                            if($(this).val()!='')
                                            {
                                                //"<?//=url('/Leave/EmailTemplate/Settings/Edit/')?>"+template_type_id
                                                window.location.href = "<?=url('/Leave/EmailTemplate/Settings/Edit')?>/"+template_type_id;
                                            }
                                        });
                                      });
                                </script>
                                <script type="text/javascript">
                                    $(document).ready(function () {
                                    var dataSource = new kendo.data.DataSource({
                                    transport: {
                                    read: {
                                    url: "<?= url('/Leave/EmailTemplate/Settings/TemplateList/Json') ?>",
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
                                                                    template_type: {type: "string"},
                                                                    msg_template: {type: "string"},
                                                                    updated_at: {type: "string"}
                                                            }
                                                    }
                                            },
                                            pageSize: 10,
                                            serverPaging: true,
                                            serverFiltering: true,
                                            serverSorting: true
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
                                            {field: "template_type", title: "Template Title",},
                                            {field: "msg_template", title: "Message Template", filterable: false,},
                                            {field: "updated_at", title: "Date", filterable: false},
                                            {
                                            title: "Action", width: "160px",
                                                    template: kendo.template($("#action_template").html())
                                            }
                                            ],
                                    });
                                    });</script>
                                <!-- kendo table code end fro here-->
                                <!--Vertical Form Ends Here-->
                            </div>
                            </div>
                        </div>
                    </div>
                </section>

        </div>
    </div>
</div>



@endsection
@section('extraHeader')
<style type="text/css">
code{
  color: #d32f2f !important;
}
</style>
@endsection
@section('extraFooter')
<script>
    $(document).ready(function () {

        $("#message_template").kendoEditor({
            tools: [
                "bold", "italic", "underline", "strikethrough", "justifyLeft", "justifyCenter", "justifyRight", "justifyFull",
                "insertUnorderedList", "insertOrderedList", "indent", "outdent", "createLink", "unlink", "insertImage",
                "insertFile", "subscript", "superscript", "createTable", "addRowAbove", "addRowBelow", "addColumnLeft",
                "addColumnRight", "deleteRow", "deleteColumn", "viewHtml", "formatting", "cleanFormatting",
                "fontName", "fontSize", "foreColor", "backColor"
            ]
        });
    });
</script>
@include('include.coreKendo')
<?=MenuPageController::genarateKendoDatePicker(array("starts","ends"))?>
@include('ajax_include.company_wise_department')
@include('ajax_include.department_wise_section')
@include('ajax_include.section_wise_designation')
@include('ajax_include.company_department_section_designation_wise_employee')
@endsection
