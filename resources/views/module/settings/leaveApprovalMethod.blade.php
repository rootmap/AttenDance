<?php
if (isset($app_method)) {
    $pageinfo = array("Edit Leave Approval Method Settings", "Edit Leave Approval Method Record", "", "SUL");
} else {
    $pageinfo = array("Leave Approval Method Settings", "Add Leave Approval Method Record", "", "SUL");
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

                    <div class="pull-right cat__core__sortable__control" style="display:none;">

                        <a href="{{route('settings.leavePolicy.export.excel')}}" class="fa fa-file-excel-o fa-2x"  title="Export Excel" data-original-title="Export Excel">&nbsp;</a>
                        <a href="{{route('settings.leavePolicy.export.pdf')}}" class="fa fa-file-pdf-o fa-2x"  title="Export Pdf" data-original-title="Export Pdf">&nbsp;</a>
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
                            @if(isset($app_method))
                            <form name="gender" action="{{url('/Leave/LeaveApplication/ApprovalMethod/Update/'.$app_method['id'])}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                  <div class="col-lg-3">
                                      <div class="mb-5">
                                        <div class="form-check">
                                          <label>Approval Method</label>
                                          <br/>
                                          <label class="form-check-label">
                                              <input class="form-check-input"
                                              @if($app_method['approval_method']=='Individual')
                                              checked="checked"
                                              @endif
                                              type="radio" name="approval_method" id="approval_method" value="Individual">
                                              Individual Approval
                                          </label>
                                          <label class="form-check-label">
                                              <input class="form-check-input"
                                              @if($app_method['approval_method']=='Group')
                                              checked="checked"
                                              @endif
                                              type="radio" name="approval_method" id="approval_method" value="Group">
                                              Group Approval
                                          </label>
                                        </div>

                                      </div>
                                  </div>

                                  <div class="col-lg-3">
                                    <div class="mb-5">
                                      <div class="form-check">
                                        <label>Rejection Method</label>
                                        <br/>
                                          <div class="form-check">
                                              <label class="form-check-label">
                                                  <input class="form-check-input"
                                                  @if($app_method['ends_at_first_rejection']==1)
                                                  checked="checked"
                                                  @endif
                                                  type="checkbox" name="ends_at_first_rejection" value="1">
                                                  Ends at First Rejection
                                              </label>
                                          </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>


                                <div class="form-actions">
                                    <button type="submit"  class="btn btn-primary">Update</button>
                                    <button type="reset" class="btn btn-default">Cancel</button>
                                </div>
                            </form>
                            @else
                            <form name="leaveApprovalMethod" action="{{url('/Leave/LeaveApplication/ApprovalMethod/Add')}}" method="post">
                                {{csrf_field()}}
                                <div class="row">
                                  <div class="col-lg-3">
                                      <div class="mb-5">
                                        <div class="form-check">
                                          <label>Approval Method</label>
                                          <br/>
                                          <label class="form-check-label">
                                              <input class="form-check-input" type="radio" name="approval_method" id="approval_method" value="individual">
                                              Individual Approval
                                          </label>
                                          <label class="form-check-label">
                                              <input class="form-check-input" type="radio" name="approval_method" id="approval_method" value="group">
                                              Group Approval
                                          </label>
                                        </div>

                                      </div>
                                  </div>

                                  <div class="col-lg-3">
                                    <div class="mb-5">
                                      <div class="form-check">
                                        <label>Rejection Method</label>
                                        <br/>
                                          <div class="form-check">
                                              <label class="form-check-label">
                                                  <input class="form-check-input" type="checkbox" name="ends_at_first_rejection" value="1">
                                                  Ends at First Rejection
                                              </label>
                                          </div>
                                      </div>
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
                        <div class="clearfix"></div>
                        <div class="col-xl-12">
                            <!--Vertical Form Starts Here-->
                            <!-- kendo table code start from here-->
                            <div class="row" style="display:none;">
                                <div id="grid" class="col-md-12"></div>
                            </div>
                            <script id="action_template" type="text/x-kendo-template">
                                <a class="k-button k-button-icontext k-grid-edit" href="{{url('Settings/LeavePolicy/Edit')}}/#=id#"><span class="k-icon k-edit"></span> Edit</a>
                                <a style="display:none;" class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#=id#);" ><span class="k-icon k-delete"></span> Delete</a>
                            </script>

                            <script type="text/javascript">
                                function deleteClick(id) {
                                var baseUrl = "<?= url('Settings/LeavePolicy/Delete') ?>";
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
                                $(document).ready(function () {
                                var dataSource = new kendo.data.DataSource({
                                transport: {
                                read: {
                                url: "<?= url('Settings/LeavePolicy/Json') ?>",
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
                                                                leave_title: {type: "string"},
                                                                leave_short_code: {type: "string"},
                                                                total_days: {type: "number"},
                                                                is_applicable_for_all: {type: "boolean"},
                                                                is_leave_cut_applicable: {type: "boolean"},
                                                                is_carry_forward: {type: "boolean"},
                                                                max_carry_forward_days: {type: "number"},
                                                                is_document_upload: {type: "boolean"},
                                                                document_upload_after_days	: {type: "number"},
                                                                is_holiday_deduct: {type: "boolean"},
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
                                        {field: "leave_title", title: "Title", width: "120px"},
                                        {field: "leave_short_code", title: "Code", filterable: false, width: "60px"},
                                        {field: "total_days", title: "Total Days", filterable: false, width: "90px"},
                                        {field: "is_applicable_for_all", title: "Is For All", filterable: false, width: "60px"},
                                        {field: "is_leave_cut_applicable", title: "Is Cut Applicable", filterable: false, width: "100px"},
                                        {field: "is_carry_forward", title: "Carry Forward", filterable: false, width: "90px"},
                                        {field: "max_carry_forward_days", title: "Max Days", filterable: false, width: "60px"},
                                        {field: "is_document_upload", title: "Is Document Upload", filterable: false},
                                        {field: "document_upload_after_days", title: "Needed After Days", filterable: false},
                                        {field: "is_holiday_deduct", title: "Is Holiday Deduct", filterable: false},
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
            </section>


        </div>
    </div>

</div>
@endsection
@section('extraFooter')
@include('include.coreKendo')
<script type="text/javascript">
    $(document).ready(function(){
        @if (isset($data))
        @if ($data['is_carry_forward'] == 1)
        $(".is_carry_forward").fadeIn('slow');
        @ else
        $(".is_carry_forward").fadeOut('slow');
        @endif

        @if ($data['is_document_upload '] == 1)
        $(".is_document_upload").fadeIn('slow');
        @ else
        $(".is_document_upload").fadeOut('slow');
        @endif
        @ else
        $(".is_carry_forward").fadeOut('fast');
        $(".is_document_upload").fadeOut('fast');
        $("#is_carry_forward").prop('checked', false);
        $("#is_document_upload").prop('checked', false);
        @endif


        $("input[name='is_carry_forward']").click(function(){
            if (document.getElementById('is_carry_forward').checked)
            {
            $(".is_carry_forward").fadeIn('slow');
            }
            else
            {
            $(".is_carry_forward").fadeOut('fast');
            }
            });
                    $("input[name='is_document_upload']").click(function(){
            if (document.getElementById('is_document_upload').checked)
            {
            $(".is_document_upload").fadeIn('slow');
            }
            else
            {
            $(".is_document_upload").fadeOut('fast');
            }
          });
    });

</script>
@endsection
